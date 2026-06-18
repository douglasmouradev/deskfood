<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';
$p = \App\Database::pdo();

echo "=== Pedidos em saiu_entrega ===\n";
foreach ($p->query(
    'SELECT o.id, o.order_number, o.tracking_token, d.id AS delivery_id, d.motoboy_id,
            d.last_latitude, d.last_longitude, d.last_location_at
     FROM orders o
     LEFT JOIN deliveries d ON d.order_id = o.id
     WHERE o.status = "saiu_entrega"
     ORDER BY o.id DESC LIMIT 5'
) as $r) {
    echo "order #{$r['id']} {$r['order_number']} track={$r['tracking_token']}\n";
    echo "  delivery #{$r['delivery_id']} motoboy #{$r['motoboy_id']}\n";
    echo "  last: {$r['last_latitude']}, {$r['last_longitude']} @ {$r['last_location_at']}\n";
}

echo "\n=== Ultimas localizacoes ===\n";
foreach ($p->query(
    'SELECT * FROM delivery_locations ORDER BY id DESC LIMIT 5'
) as $r) {
    echo "  delivery #{$r['delivery_id']} {$r['latitude']},{$r['longitude']} @ {$r['recorded_at']}\n";
}
