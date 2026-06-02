<?php

declare(strict_types=1);

/**
 * Configuração do provedor de SMS para envio de OTP e notificações.
 */

use App\Helpers\Env;

return [
    'provider' => Env::get('SMS_PROVIDER', 'log'),
    'api_key' => Env::get('SMS_API_KEY', ''),
    'api_secret' => Env::get('SMS_API_SECRET', ''),
    'from_number' => Env::get('SMS_FROM_NUMBER', ''),
    'sender' => Env::get('SMS_SENDER', 'DeskFood'),
];
