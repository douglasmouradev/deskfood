<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\Env;
use App\Helpers\Logger;
use PDO;
use Ramsey\Uuid\Uuid;
use Throwable;

/**
 * Integração PIX configurável com modo `mock` para desenvolvimento local.
 *
 * Webhook: idempotente, opcionalmente protegido por `PIX_WEBHOOK_SECRET` (header
 * `X-Deskfood-Webhook-Secret` ou `Authorization: Bearer <segredo>`).
 */
final class PixService
{
    /**
     * Cria cobrança PIX pendente vinculada ao pagamento do pedido.
     *
     * @return array{copy_paste:string,qr_payload:string,expires_at:string,external_id:string}
     */
    public static function createForPayment(int $paymentId, float $amount): array
    {
        $provider = Env::get('PIX_PROVIDER', 'mock');
        if ($provider !== 'mock') {
            Logger::log('warning', 'PIX provider não implementado; usando mock', ['provider' => $provider]);
        }

        $txid = 'MOCK-' . Uuid::uuid4()->toString();
        $copy = self::buildMockEmv($txid, $amount);
        $expires = (new \DateTimeImmutable('+30 minutes'))->format('Y-m-d H:i:s');

        return [
            'copy_paste' => $copy,
            'qr_payload' => $copy,
            'expires_at' => $expires,
            'external_id' => $txid,
        ];
    }

    /**
     * Processa webhook de confirmação PIX com idempotência e auditoria.
     *
     * @param array<string, mixed> $payload Corpo decodificado do webhook
     * @return array{ok:bool,http:int,duplicate?:bool,message?:string}
     */
    public static function handleWebhook(array $payload): array
    {
        if (!self::assertWebhookAuthorized()) {
            AuditLogService::record('system', null, 'webhook.pix.unauthorized', null, null, [
                'txid' => $payload['txid'] ?? $payload['external_id'] ?? null,
            ]);

            return ['ok' => false, 'http' => 401, 'message' => 'Webhook não autorizado.'];
        }

        $txid = (string) ($payload['txid'] ?? $payload['external_id'] ?? '');
        if ($txid === '') {
            return ['ok' => false, 'http' => 400, 'message' => 'Payload sem txid.'];
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT pt.id AS pt_id, pt.status AS pt_status, pt.payment_id, p.id AS payment_id, p.status AS pay_status, p.order_id
                 FROM pix_transactions pt
                 INNER JOIN payments p ON p.id = pt.payment_id
                 WHERE pt.external_id = :e
                 LIMIT 1
                 FOR UPDATE'
            );
            $stmt->execute(['e' => $txid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                $pdo->rollBack();
                AuditLogService::record('system', null, 'webhook.pix.not_found', 'pix_transaction', null, ['txid' => $txid]);
                Logger::log('warning', 'Webhook PIX sem transação', ['txid' => $txid]);

                return ['ok' => false, 'http' => 404, 'message' => 'Transação não encontrada.'];
            }

            if (($row['pt_status'] ?? '') === 'pago' || ($row['pay_status'] ?? '') === 'pago') {
                $pdo->commit();
                AuditLogService::record('system', null, 'webhook.pix.duplicate', 'order', (int) $row['order_id'], ['txid' => $txid]);

                return ['ok' => true, 'http' => 200, 'duplicate' => true, 'message' => 'Já processado.'];
            }

            $u1 = $pdo->prepare('UPDATE pix_transactions SET status = :s, webhook_payload = :w, updated_at = NOW() WHERE id = :id');
            $u1->execute([
                's' => 'pago',
                'w' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'id' => $row['pt_id'],
            ]);

            $u2 = $pdo->prepare('UPDATE payments SET status = :s, updated_at = NOW() WHERE id = :id');
            $u2->execute(['s' => 'pago', 'id' => $row['payment_id']]);

            $u3 = $pdo->prepare(
                'UPDATE orders SET payment_status = :ps, status = CASE WHEN status = :p THEN :c ELSE status END, updated_at = NOW() WHERE id = :oid'
            );
            $u3->execute([
                'ps' => 'pago',
                'p' => 'pendente',
                'c' => 'confirmado',
                'oid' => $row['order_id'],
            ]);

            $log = $pdo->prepare(
                'INSERT INTO order_status_logs (order_id, status, note, actor_type, created_at) VALUES (:oid,:st,:n,:a,NOW())'
            );
            $log->execute([
                'oid' => $row['order_id'],
                'st' => 'confirmado',
                'n' => 'Pagamento PIX confirmado via webhook',
                'a' => 'system',
            ]);

            $pdo->commit();

            AuditLogService::record('system', null, 'webhook.pix.confirmed', 'order', (int) $row['order_id'], ['txid' => $txid]);

            try {
                CashRegisterService::recordSaleIfOpen((int) $row['order_id']);
            } catch (Throwable $e) {
                Logger::log('error', 'Falha ao registrar venda no caixa', ['e' => $e->getMessage()]);
            }

            return ['ok' => true, 'http' => 200, 'message' => 'Pagamento confirmado.'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::log('error', 'Erro ao processar webhook PIX', ['e' => $e->getMessage()]);
            AuditLogService::record('system', null, 'webhook.pix.error', null, null, ['txid' => $txid, 'error' => $e->getMessage()]);

            return ['ok' => false, 'http' => 500, 'message' => 'Erro interno.'];
        }
    }

    /**
     * Valida segredo compartilhado quando `PIX_WEBHOOK_SECRET` está definido.
     */
    private static function assertWebhookAuthorized(): bool
    {
        $secret = trim((string) Env::get('PIX_WEBHOOK_SECRET', ''));
        if ($secret === '') {
            return true;
        }

        $header = (string) ($_SERVER['HTTP_X_DESKFOOD_WEBHOOK_SECRET'] ?? '');
        if ($header !== '' && hash_equals($secret, $header)) {
            return true;
        }

        $auth = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
        if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return hash_equals($secret, trim($m[1]));
        }

        return false;
    }

    /**
     * Gera payload EMV fictício apenas para demonstração visual do QR.
     */
    private static function buildMockEmv(string $txid, float $amount): string
    {
        $merchant = 'DESKFOOD*DEMO';
        $amountStr = number_format($amount, 2, '.', '');

        return sprintf('00020126580014br.gov.bcb.pix0136%s520400005303986540%s5802BR5913%s62070503***6304', $txid, $amountStr, $merchant);
    }
}
