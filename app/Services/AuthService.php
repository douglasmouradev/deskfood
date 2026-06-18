<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Logger;
use App\Helpers\Phone;
use App\Services\RateLimitService;
use PDO;
use Throwable;

/**
 * Autenticação passwordless de clientes via OTP enviado por SMS.
 *
 * Controla geração de códigos, rate limiting, verificação e criação de sessão.
 */
final class AuthService
{
    private const OTP_TTL_MINUTES = 5;
    private const MAX_SENDS = 3;
    private const SEND_WINDOW_MINUTES = 15;

    /**
     * Solicita envio de OTP para o telefone informado após validações básicas.
     *
     * @param array{terms:bool,privacy:bool,sms:bool} $consents Consentimentos LGPD obrigatórios
     * @return array{ok:bool,message:string}
     */
    public static function requestOtp(string $name, string $phoneRaw, array $consents): array
    {
        if (empty($consents['terms']) || empty($consents['privacy']) || empty($consents['sms'])) {
            return ['ok' => false, 'message' => 'Você precisa aceitar os termos, privacidade e SMS.'];
        }

        $e164 = Phone::normalizeBr($phoneRaw);
        if ($e164 === null) {
            return ['ok' => false, 'message' => 'Telefone inválido.'];
        }

        $name = trim($name);
        if (strlen($name) < 2) {
            return ['ok' => false, 'message' => 'Informe seu nome completo.'];
        }

        $pdo = Database::pdo();
        if (RateLimitService::isLimited('otp_ip', 'send', 15, 900)) {
            return ['ok' => false, 'message' => 'Muitas tentativas deste dispositivo. Aguarde alguns minutos.'];
        }
        if (!self::canSendAgain($pdo, $e164)) {
            return ['ok' => false, 'message' => 'Muitas tentativas. Aguarde 15 minutos para novo código.'];
        }
        RateLimitService::hit('otp_ip', 'send');

        $code = (string) random_int(100000, 999999);
        $hash = password_hash($code, PASSWORD_BCRYPT);
        $expires = (new \DateTimeImmutable('+' . self::OTP_TTL_MINUTES . ' minutes'))->format('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        try {
            $pdo->beginTransaction();
            $ins = $pdo->prepare(
                'INSERT INTO otp_codes (phone_e164, code_hash, expires_at, used_at, attempts, ip_address, created_at, updated_at)
                 VALUES (:p,:h,:e,NULL,0,:ip,NOW(),NOW())'
            );
            $ins->execute(['p' => $e164, 'h' => $hash, 'e' => $expires, 'ip' => $ip]);

            $log = $pdo->prepare('INSERT INTO otp_send_logs (phone_e164, created_at) VALUES (:p, NOW())');
            $log->execute(['p' => $e164]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            Logger::log('error', 'Falha ao registrar OTP', ['e' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'Não foi possível enviar o código agora.'];
        }

        $msg = sprintf('Seu código Desk Food: %s (expira em %d minutos)', $code, self::OTP_TTL_MINUTES);
        SmsService::send($e164, $msg);

        $_SESSION['otp_pending_phone'] = $e164;
        $_SESSION['otp_pending_name'] = $name;

        return ['ok' => true, 'message' => 'Código enviado por SMS.'];
    }

    /**
     * Confirma OTP, cria/atualiza usuário, grava consentimentos e autentica sessão.
     *
     * @param array<string, mixed> $versions Versões dos documentos aceitos (terms/privacy)
     * @return array{ok:bool,message:string}
     */
    public static function verifyOtp(string $phoneRaw, string $code, array $versions): array
    {
        $e164 = Phone::normalizeBr($phoneRaw);
        if ($e164 === null) {
            return ['ok' => false, 'message' => 'Telefone inválido.'];
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            return ['ok' => false, 'message' => 'Código inválido.'];
        }

        if (RateLimitService::isLimited('otp_verify_ip', 'verify', 20, 900)) {
            return ['ok' => false, 'message' => 'Muitas tentativas deste dispositivo. Aguarde alguns minutos.'];
        }
        if (RateLimitService::isLimited('otp_verify_phone', $e164, 10, 900)) {
            return ['ok' => false, 'message' => 'Muitas tentativas para este telefone. Aguarde alguns minutos.'];
        }
        RateLimitService::hit('otp_verify_ip', 'verify');
        RateLimitService::hit('otp_verify_phone', $e164);

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM otp_codes WHERE phone_e164 = :p AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['p' => $e164]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false || !password_verify($code, (string) $row['code_hash'])) {
            if ($row !== false) {
                $attempts = (int) $row['attempts'] + 1;
                $u = $pdo->prepare('UPDATE otp_codes SET attempts = :a, updated_at = NOW() WHERE id = :id');
                $u->execute(['a' => $attempts, 'id' => $row['id']]);
                if ($attempts >= 5) {
                    return ['ok' => false, 'message' => 'Muitas tentativas. Solicite um novo código.'];
                }
            }

            return ['ok' => false, 'message' => 'Código incorreto ou expirado.'];
        }

        if ((int) ($row['attempts'] ?? 0) >= 5) {
            return ['ok' => false, 'message' => 'Código bloqueado. Solicite um novo código.'];
        }

        $name = (string) ($_SESSION['otp_pending_name'] ?? 'Cliente');
        if (isset($_SESSION['otp_pending_phone']) && $_SESSION['otp_pending_phone'] !== $e164) {
            return ['ok' => false, 'message' => 'Telefone não confere com a solicitação.'];
        }

        try {
            $pdo->beginTransaction();

            $mark = $pdo->prepare('UPDATE otp_codes SET used_at = NOW(), updated_at = NOW() WHERE id = :id');
            $mark->execute(['id' => $row['id']]);

            $userId = self::upsertUser($pdo, $name, $phoneRaw, $e164);
            self::saveConsents($pdo, (int) $userId, $versions);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            Logger::log('error', 'Falha ao verificar OTP', ['e' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'Erro ao finalizar cadastro.'];
        }

        session_regenerate_id(true);
        Csrf::regenerate();
        $_SESSION['user_id'] = (int) $userId;
        unset($_SESSION['otp_pending_phone'], $_SESSION['otp_pending_name']);

        return ['ok' => true, 'message' => 'Autenticado com sucesso.'];
    }

    /**
     * Encerra sessão do cliente de forma segura.
     */
    public static function logoutCustomer(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
    }

    /**
     * Verifica limite de envios por janela de tempo para o telefone.
     */
    private static function canSendAgain(PDO $pdo, string $e164): bool
    {
        $window = (int) self::SEND_WINDOW_MINUTES;
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS c FROM otp_send_logs
             WHERE phone_e164 = :p AND created_at >= DATE_SUB(NOW(), INTERVAL {$window} MINUTE)"
        );
        $stmt->execute(['p' => $e164]);
        $c = (int) ($stmt->fetchColumn() ?: 0);

        return $c < self::MAX_SENDS;
    }

    /**
     * Cria ou atualiza usuário com base no telefone normalizado.
     */
    private static function upsertUser(PDO $pdo, string $name, string $phoneRaw, string $e164): int
    {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE phone_e164 = :p LIMIT 1');
        $stmt->execute(['p' => $e164]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing !== false) {
            $u = $pdo->prepare('UPDATE users SET name = :n, phone = :ph, updated_at = NOW() WHERE id = :id');
            $u->execute(['n' => $name, 'ph' => $phoneRaw, 'id' => $existing['id']]);

            return (int) $existing['id'];
        }

        $ins = $pdo->prepare(
            'INSERT INTO users (name, phone, phone_e164, created_at, updated_at) VALUES (:n,:ph,:e164,NOW(),NOW())'
        );
        $ins->execute(['n' => $name, 'ph' => $phoneRaw, 'e164' => $e164]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Persiste consentimentos LGPD vinculados ao usuário recém-autenticado.
     *
     * @param array<string, mixed> $versions
     */
    private static function saveConsents(PDO $pdo, int $userId, array $versions): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $termsV = (string) ($versions['terms'] ?? '1.0');
        $privacyV = (string) ($versions['privacy'] ?? '1.0');
        $smsV = (string) ($versions['sms'] ?? '1.0');

        $rows = [
            ['terms', $termsV],
            ['privacy', $privacyV],
            ['sms', $smsV],
        ];

        $ins = $pdo->prepare(
            'INSERT INTO lgpd_consents (user_id, doc_type, version, ip_address, accepted_at, created_at, updated_at)
             VALUES (:uid,:dt,:v,:ip,NOW(),NOW(),NOW())'
        );

        foreach ($rows as [$type, $ver]) {
            $ins->execute(['uid' => $userId, 'dt' => $type, 'v' => $ver, 'ip' => $ip]);
        }
    }
}
