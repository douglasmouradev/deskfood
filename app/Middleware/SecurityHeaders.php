<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * Envia cabeçalhos HTTP de segurança recomendados para aplicações web.
 *
 * CSP restritivo com exceções para CDNs do Tailwind, Alpine, Google Fonts
 * e scripts/estilos locais do Desk Food.
 */
final class SecurityHeaders
{
    /**
     * Envia o conjunto de headers de segurança para a resposta atual.
     */
    public static function send(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        $tailwindCdn = \App\Helpers\Env::get('TAILWIND_CDN', '0') === '1'
            || !is_file(BASE_PATH . '/public/assets/css/tailwind.css');

        $scriptSrc = "'self' 'unsafe-inline' https://cdn.jsdelivr.net";
        $styleSrc = "'self' 'unsafe-inline' https://fonts.googleapis.com";
        if ($tailwindCdn) {
            $scriptSrc .= ' https://cdn.tailwindcss.com';
            $styleSrc .= ' https://cdn.tailwindcss.com';
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src {$styleSrc}",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https:",
            "connect-src 'self' https://viacep.com.br",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        header('Content-Security-Policy: ' . $csp);

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }
}
