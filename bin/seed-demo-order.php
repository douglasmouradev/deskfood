#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Cria um pedido de demonstração para testar o quadro do operador e o mapa GPS.
 * Uso: php bin/seed-demo-order.php [--status=em_preparo]
 */

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';
require $base . '/bootstrap.php';

$status = 'em_preparo';
foreach ($argv as $arg) {
    if (preg_match('/^--status=(.+)$/', $arg, $m)) {
        $status = (string) $m[1];
    }
}

$allowed = ['pendente', 'confirmado', 'em_preparo', 'saiu_entrega'];
if (!in_array($status, $allowed, true)) {
    fwrite(STDERR, "Status inválido. Use: " . implode(', ', $allowed) . "\n");
    exit(1);
}

$pdo = \App\Database::pdo();
$unitId = 1;

$unit = $pdo->prepare('SELECT id, name, delivery_fee FROM units WHERE id = :id AND is_active = 1 LIMIT 1');
$unit->execute(['id' => $unitId]);
$u = $unit->fetch(PDO::FETCH_ASSOC);
if ($u === false) {
    fwrite(STDERR, "Unidade demo (id=1) não encontrada. Rode: php install.php --force\n");
    exit(1);
}

$product = $pdo->prepare(
    'SELECT id, name, price FROM products WHERE unit_id = :u AND status = "active" ORDER BY id ASC LIMIT 1'
);
$product->execute(['u' => $unitId]);
$prod = $product->fetch(PDO::FETCH_ASSOC);
if ($prod === false) {
    fwrite(STDERR, "Nenhum produto ativo na unidade. Rode: php install.php --force\n");
    exit(1);
}

$orderNumber = \App\Services\OrderService::generateOrderNumber($pdo, $unitId);
$tracking = bin2hex(random_bytes(16));
$subtotal = (float) $prod['price'];
$deliveryFee = (float) ($u['delivery_fee'] ?? 0);
$total = $subtotal + $deliveryFee;

$pdo->beginTransaction();
try {
    $pdo->prepare(
        'INSERT INTO orders (
            unit_id, user_id, order_number, tracking_token, status,
            payment_method, payment_status, on_delivery_type,
            customer_name, customer_phone,
            delivery_street, delivery_number, delivery_complement,
            delivery_neighborhood, delivery_city, delivery_state, delivery_zip,
            notes, delivery_type, subtotal, delivery_fee, total,
            created_at, updated_at
        ) VALUES (
            :uid, NULL, :onum, :track, :st,
            "on_delivery", "pendente_entrega", "cash",
            :cname, :cphone,
            :dst, :dnum, NULL,
            :dnei, :dcity, :dstate, :dzip,
            :notes, "delivery", :sub, :dfee, :tot,
            NOW(), NOW()
        )'
    )->execute([
        'uid' => $unitId,
        'onum' => $orderNumber,
        'track' => $tracking,
        'st' => $status,
        'cname' => 'Cliente Demo',
        'cphone' => '11999998888',
        'dst' => 'Rua Augusta',
        'dnum' => '500',
        'dnei' => 'Consolação',
        'dcity' => 'São Paulo',
        'dstate' => 'SP',
        'dzip' => '01304000',
        'notes' => 'Pedido gerado por bin/seed-demo-order.php',
        'sub' => round($subtotal, 2),
        'dfee' => round($deliveryFee, 2),
        'tot' => round($total, 2),
    ]);

    $orderId = (int) $pdo->lastInsertId();

    $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, line_total, created_at, updated_at)
         VALUES (:oid, :pid, :pname, 1, :up, :lt, NOW(), NOW())'
    )->execute([
        'oid' => $orderId,
        'pid' => (int) $prod['id'],
        'pname' => (string) $prod['name'],
        'up' => $subtotal,
        'lt' => $subtotal,
    ]);

    $pdo->prepare(
        'INSERT INTO payments (order_id, type, status, amount, meta, created_at, updated_at)
         VALUES (:oid, "on_delivery", "pendente", :amt, "{}", NOW(), NOW())'
    )->execute(['oid' => $orderId, 'amt' => round($total, 2)]);

    $pdo->prepare(
        'INSERT INTO order_status_logs (order_id, status, note, actor_type, created_at)
         VALUES (:oid, :st, :n, "system", NOW())'
    )->execute([
        'oid' => $orderId,
        'st' => $status,
        'n' => 'Pedido demo para testes',
    ]);

    if ($status === 'saiu_entrega') {
        $mb = $pdo->query('SELECT id FROM motoboys WHERE unit_id = 1 AND is_active = 1 AND deleted_at IS NULL ORDER BY id ASC LIMIT 1');
        $motoboyId = $mb ? (int) ($mb->fetchColumn() ?: 0) : 0;
        if ($motoboyId > 0) {
            $pdo->prepare(
                'INSERT INTO deliveries (order_id, motoboy_id, status, started_at, created_at, updated_at)
                 VALUES (:oid, :mid, "out_for_delivery", NOW(), NOW(), NOW())'
            )->execute(['oid' => $orderId, 'mid' => $motoboyId]);
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Erro: ' . $e->getMessage() . "\n");
    exit(1);
}

$cfg = require $base . '/config/app.php';
$baseUrl = rtrim((string) ($cfg['url'] ?? 'http://127.0.0.1:8080'), '/');

echo "Pedido demo criado com sucesso!\n\n";
echo "Número:     {$orderNumber}\n";
echo "Status:     {$status}\n";
echo "Total:      R$ " . number_format($total, 2, ',', '.') . "\n\n";
echo "Operador:   {$baseUrl}/operador\n";
echo "Rastreio:   {$baseUrl}/acompanhar/{$tracking}\n";
echo "Cardápio:   {$baseUrl}/u/centro\n\n";

if ($status === 'em_preparo') {
    echo "Próximo passo: no operador, abra o pedido e clique em \"Atribuir motoboy\".\n";
    echo "Depois renove/copie o link do motoboy em {$baseUrl}/operador/motoboys\n";
} elseif ($status === 'saiu_entrega') {
    echo "Pedido já em rota. Abra o link do motoboy e a página de rastreio acima.\n";
} else {
    echo "Avance o status no quadro do operador até \"Em preparo\" e atribua o motoboy.\n";
}
