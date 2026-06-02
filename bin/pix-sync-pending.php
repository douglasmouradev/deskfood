<?php

declare(strict_types=1);

/**
 * Sincroniza PIX pendentes com o gateway (cron a cada 2–5 min em produção).
 *
 * Uso: php bin/pix-sync-pending.php
 */

use App\Database;
use App\Helpers\Env;
use App\Services\PixService;

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
Env::load($root . '/.env');
require $root . '/config/database.php';

if (Env::get('PIX_PROVIDER', 'mock') === 'mock') {
    echo "PIX_PROVIDER=mock — nada a sincronizar.\n";
    exit(0);
}

$pdo = Database::pdo();

$st = $pdo->query(
    'SELECT o.id FROM orders o
     INNER JOIN payments p ON p.order_id = o.id AND p.type = "pix"
     INNER JOIN pix_transactions pt ON pt.payment_id = p.id
     WHERE o.payment_status = "pendente" AND pt.status = "criado" AND pt.expires_at > NOW()
     ORDER BY o.id ASC
     LIMIT 100'
);

$synced = 0;
while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    if (PixService::trySyncOrderPixStatus((int) $row['id'])) {
        ++$synced;
        echo 'Pedido #' . $row['id'] . " confirmado.\n";
    }
}

echo "Concluído. {$synced} pedido(s) atualizado(s).\n";
