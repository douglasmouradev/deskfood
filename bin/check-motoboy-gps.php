<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';
$p = \App\Database::pdo();
echo "=== Motoboys ===\n";
foreach ($p->query('SELECT id, name FROM motoboys WHERE deleted_at IS NULL') as $r) {
    echo "  #{$r['id']} {$r['name']}\n";
}
echo "=== Entregas em rota ===\n";
$st = $p->query(
    'SELECT d.id, d.motoboy_id, d.status, o.order_number, o.status AS order_status
     FROM deliveries d JOIN orders o ON o.id = d.order_id
     WHERE d.status = "out_for_delivery" ORDER BY d.id DESC LIMIT 5'
);
foreach ($st as $r) {
    echo "  delivery #{$r['id']} motoboy #{$r['motoboy_id']} order {$r['order_number']} ({$r['order_status']})\n";
}
if ($st->rowCount() === 0) {
    echo "  (nenhuma)\n";
}
