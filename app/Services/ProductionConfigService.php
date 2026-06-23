<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;

/**
 * Auditoria de configuração para go-live e alertas no painel.
 */
final class ProductionConfigService
{
    /**
     * @return list<array{level: string, message: string}>
     */
    public static function issues(): array
    {
        $issues = [];
        $env = Env::get('APP_ENV', 'production');
        $isProd = $env === 'production';

        $secret = (string) Env::get('APP_SECRET', '');
        if ($secret === '' || strlen($secret) < 32 || str_contains(strtolower($secret), 'altere')) {
            $issues[] = self::issue('error', 'APP_SECRET fraco ou padrão — gere com: openssl rand -hex 32');
        }

        if ($isProd) {
            if (Env::get('ALLOW_INSTALL', '0') === '1') {
                $issues[] = self::issue('error', 'ALLOW_INSTALL=1 em produção — defina 0 após instalar');
            }

            $url = rtrim((string) Env::get('APP_URL', ''), '/');
            if ($url !== '' && !str_starts_with($url, 'https://')) {
                $issues[] = self::issue('error', 'APP_URL deve usar HTTPS em produção (GPS do motoboy exige)');
            }

            if (trim((string) Env::get('PIX_WEBHOOK_SECRET', '')) === '') {
                $issues[] = self::issue('error', 'PIX_WEBHOOK_SECRET obrigatório em produção');
            }

            if (trim((string) Env::get('HEALTH_TOKEN', '')) === '') {
                $issues[] = self::issue('error', 'HEALTH_TOKEN obrigatório em produção');
            }
        }

        if (trim((string) Env::get('GOOGLE_MAPS_API_KEY', '')) === '') {
            $issues[] = self::issue('warning', 'GOOGLE_MAPS_API_KEY ausente — mapa de rastreio não funcionará');
        }

        if ($isProd && Env::get('SMS_PROVIDER', 'log') === 'log') {
            $issues[] = self::issue('warning', 'SMS_PROVIDER=log — clientes não receberão OTP por SMS real');
        }

        if ($isProd && Env::get('MAIL_DRIVER', 'log') === 'log') {
            $issues[] = self::issue('warning', 'MAIL_DRIVER=log — e-mails de pedido não serão enviados');
        }

        if ($isProd && Env::get('JOBS_ASYNC', '0') !== '1') {
            $issues[] = self::issue('warning', 'JOBS_ASYNC=0 — SMS/e-mail podem atrasar pedidos (recomendado: 1 + cron worker)');
        }

        if (trim((string) Env::get('ERROR_WEBHOOK_URL', '')) === '') {
            $issues[] = self::issue('warning', 'ERROR_WEBHOOK_URL ausente — erros críticos sem alerta externo');
        }

        return $issues;
    }

    public static function isReady(): bool
    {
        foreach (self::issues() as $issue) {
            if ($issue['level'] === 'error') {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<array{level: string, message: string}>
     */
    public static function panelIssues(): array
    {
        if (Env::get('APP_ENV', 'production') !== 'production') {
            return [];
        }

        return self::issues();
    }

    /**
     * @return array{level: string, message: string}
     */
    private static function issue(string $level, string $message): array
    {
        return ['level' => $level, 'message' => $message];
    }
}
