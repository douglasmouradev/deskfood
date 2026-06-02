<?php

declare(strict_types=1);

/**
 * Sincroniza pagamentos online pendentes (PIX e cartão) com os gateways.
 */

use App\Database;
use App\Helpers\Env;
use App\Services\CardPaymentService;
use App\Services\PixService;

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
Env::load($root . '/.env');
require $root . '/config/database.php';

$pdo = Database::pdo();
$synced = 0;

$pix = $pdo->query(
    'SELECT o.id FROM orders o
     INNER JOIN payments p ON p.order_id = o.id AND p.type = "pix"
     INNER JOIN pix_transactions pt ON pt.payment_id = p.id
     WHERE o.payment_status = "pendente" AND pt.status = "criado" AND pt.expires_at > NOW()
     LIMIT 50'
);
while ($row = $pix->fetch(PDO::FETCH_ASSOC)) {
    if (PixService::trySyncOrderPixStatus((int) $row['id'])) {
        ++$synced;
        echo 'PIX pedido #' . $row['id'] . " OK\n";
    }
}

$cards = $pdo->query(
    'SELECT o.id, o.unit_id, p.id AS payment_id FROM orders o
     INNER JOIN payments p ON p.order_id = o.id AND p.type = "card"
     INNER JOIN card_transactions ct ON ct.payment_id = p.id
     WHERE o.payment_status = "pendente" AND ct.status IN ("criado","pendente")
     LIMIT 50'
);
while ($row = $cards->fetch(PDO::FETCH_ASSOC)) {
    if (CardPaymentService::syncByPaymentId((int) $row['payment_id'], (int) $row['unit_id'])) {
        ++$synced;
        echo 'Cartão pedido #' . $row['id'] . " OK\n";
    }
}

echo "Concluído: {$synced} pagamento(s) confirmado(s).\n";
