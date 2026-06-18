<?php

declare(strict_types=1);

/**
 * Configurações gerais da aplicação Desk Food.
 *
 * Expõe valores tipados consumidos por controllers, views e services
 * sem acessar diretamente superglobals ou arquivos .env fora do helper.
 */

use App\Helpers\Env;

return [
    'name' => Env::get('APP_NAME', 'Desk Food'),
    'env' => Env::get('APP_ENV', 'production'),
    'url' => rtrim(Env::get('APP_URL', 'http://localhost'), '/'),
    'secret' => Env::get('APP_SECRET', ''),
    'upload_max' => (int) Env::get('UPLOAD_MAX_SIZE', '2097152'),
    'upload_path' => dirname(__DIR__) . '/' . trim(Env::get('UPLOAD_PATH', 'public/uploads'), '/'),
    'terms_version' => Env::get('TERMS_VERSION', '1.0'),
    'privacy_version' => Env::get('PRIVACY_VERSION', '1.0'),
    'dpo_name' => Env::get('DPO_NAME', ''),
    'dpo_email' => Env::get('DPO_EMAIL', ''),
    'notify_order_sms' => Env::get('NOTIFY_ORDER_SMS', '0') === '1',

    /** Texto padrão para <meta name="description"> em páginas públicas sem descrição própria */
    'default_meta_description' => Env::get(
        'APP_META_DESCRIPTION',
        'Desk Food — pedido online direto com o restaurante. Cardápio, PIX e rastreio em um só lugar.'
    ),

    /** Dados de contato comercial (rodapé, landing, ajuda) */
    'commercial_company' => Env::get('APP_COMMERCIAL_COMPANY', 'TDesk Solutions'),
    'commercial_email' => Env::get('APP_COMMERCIAL_EMAIL', 'comercial@tdesksolutions.com.br'),
    'commercial_phone_label' => Env::get('APP_COMMERCIAL_PHONE_LABEL', '71 99708-7082'),
    'commercial_phone_tel' => Env::get('APP_COMMERCIAL_PHONE_TEL', '+5571997087082'),

    /** Intervalo em ms para o operador verificar mudanças no quadro de pedidos (0 = desliga auto-atualização) */
    'operator_board_poll_ms' => max(0, (int) Env::get('OPERATOR_BOARD_POLL_MS', '20000')),

    'analytics_ga_id' => Env::get('ANALYTICS_GA_ID', ''),

    /** Chave da Google Maps JavaScript API (mapa de rastreio ao vivo) */
    'google_maps_api_key' => Env::get('GOOGLE_MAPS_API_KEY', ''),

    'mail_driver' => Env::get('MAIL_DRIVER', 'log'),
];
