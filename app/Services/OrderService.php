<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\Env;
use App\Helpers\Logger;
use App\Services\AuditLogService;
use App\Services\JobQueueService;
use PDO;
use Throwable;

/**
 * Criação e transição de estados de pedidos com regras de negócio centrais.
 */
final class OrderService
{
    /**
     * Gera número interno de pedido único por unidade.
     *
     * @throws \RuntimeException Quando não for possível gerar após várias tentativas
     */
    public static function generateOrderNumber(PDO $pdo, int $unitId): string
    {
        for ($i = 0; $i < 8; $i++) {
            $n = date('ymd') . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $st = $pdo->prepare('SELECT id FROM orders WHERE unit_id = :u AND order_number = :n LIMIT 1');
            $st->execute(['u' => $unitId, 'n' => $n]);
            if ($st->fetch() === false) {
                return $n;
            }
        }

        throw new \RuntimeException('Não foi possível gerar número de pedido.');
    }

    /**
     * Cria pedido completo a partir do carrinho em sessão e dados de entrega/pagamento.
     *
     * @param array<string, mixed> $cart Estrutura `unit_id` + `items`
     * @param array<string, mixed> $delivery Campos de endereço e observações
     * @param array<string, mixed> $payment payment_method, on_delivery_type, change_for
     * @param array<string, string> $customer name, phone (exibição)
     * @param array{delivery_type?:string,coupon_code?:string} $options pickup|delivery, cupom opcional
     * @return array{order_id:int,tracking_token:string}
     */
    public static function createFromCart(int $userId, array $cart, array $delivery, array $payment, array $customer, array $options = []): array
    {
        $pdo = Database::pdo();
        $unitId = (int) ($cart['unit_id'] ?? 0);
        if ($unitId <= 0 || empty($cart['items']) || !is_array($cart['items'])) {
            throw new \InvalidArgumentException('Carrinho inválido.');
        }

        $unit = $pdo->prepare(
            'SELECT id, name, delivery_fee, minimum_order, business_hours, phone,
                    address_street, address_number, address_complement, neighborhood, city, state, zip, delivery_radius_km
             FROM units WHERE id = :id AND is_active = 1 AND deleted_at IS NULL LIMIT 1'
        );
        $unit->execute(['id' => $unitId]);
        $u = $unit->fetch(PDO::FETCH_ASSOC);
        if ($u === false) {
            throw new \RuntimeException('Unidade indisponível.');
        }

        if (!BusinessHoursService::isOpen($u)) {
            throw new \RuntimeException('A unidade está fechada no momento. Tente novamente no horário de funcionamento.');
        }

        $deliveryType = (string) ($options['delivery_type'] ?? 'delivery');
        if (!in_array($deliveryType, ['delivery', 'pickup'], true)) {
            throw new \InvalidArgumentException('Tipo de entrega inválido.');
        }

        if ($deliveryType === 'pickup') {
            $delivery = [
                'street' => (string) $u['address_street'],
                'number' => (string) $u['address_number'],
                'complement' => $u['address_complement'] ?? null,
                'neighborhood' => (string) $u['neighborhood'],
                'city' => (string) $u['city'],
                'state' => (string) $u['state'],
                'zip' => (string) $u['zip'],
                'notes' => trim((string) ($delivery['notes'] ?? '')) !== '' ? 'Retirada: ' . trim((string) $delivery['notes']) : 'Retirada no balcão',
            ];
        } else {
            DeliveryService::assertDeliverable($u, $delivery);
        }

        $deliveryFee = $deliveryType === 'pickup' ? 0.0 : (float) $u['delivery_fee'];
        $minimumOrder = max(0, (float) ($u['minimum_order'] ?? 0));
        $lines = self::buildLines($pdo, $unitId, $cart['items']);
        $subtotal = 0.0;
        foreach ($lines as $ln) {
            $subtotal += $ln['line_total'];
        }

        if ($minimumOrder > 0 && $subtotal < $minimumOrder) {
            throw new \RuntimeException(
                'Pedido mínimo: R$ ' . number_format($minimumOrder, 2, ',', '.') . '. Seu subtotal: R$ ' . number_format($subtotal, 2, ',', '.') . '.'
            );
        }

        $discount = 0.0;
        $couponId = null;
        $couponCode = trim((string) ($options['coupon_code'] ?? ''));
        if ($couponCode !== '') {
            $coupon = CouponService::resolve($couponCode, $unitId, $subtotal);
            $discount = $coupon['discount'];
            $couponId = $coupon['id'];
        }

        $total = max(0, $subtotal - $discount + $deliveryFee);
        $payConfig = UnitPaymentConfig::forUnit($unitId);
        $method = (string) ($payment['payment_method'] ?? '');
        if (!in_array($method, ['pix', 'card', 'on_delivery'], true)) {
            throw new \InvalidArgumentException('Forma de pagamento inválida.');
        }

        if ($method === 'pix' && !UnitPaymentConfig::supportsPix($payConfig)) {
            throw new \RuntimeException('PIX não disponível nesta unidade. Configure pagamentos no painel admin.');
        }
        if ($method === 'card' && !UnitPaymentConfig::supportsCard($payConfig)) {
            throw new \RuntimeException('Cartão online não disponível nesta unidade. Configure Mercado Pago no painel admin.');
        }

        $paymentStatus = match ($method) {
            'pix', 'card' => 'pendente',
            default => 'pendente_entrega',
        };
        $onType = null;
        $changeFor = null;
        if ($method === 'on_delivery') {
            $onType = (string) ($payment['on_delivery_type'] ?? 'cash');
            if (!in_array($onType, ['cash', 'card'], true)) {
                throw new \InvalidArgumentException('Tipo de pagamento na entrega inválido.');
            }
            if ($onType === 'cash') {
                $changeFor = isset($payment['change_for']) ? (float) $payment['change_for'] : null;
            }
        }

        $orderNumber = self::generateOrderNumber($pdo, $unitId);
        $tracking = bin2hex(random_bytes(16));

        $pdo->beginTransaction();
        try {
            $ins = $pdo->prepare(
                'INSERT INTO orders (
                    unit_id, user_id, order_number, tracking_token, status,
                    payment_method, payment_status, on_delivery_type, change_for,
                    customer_name, customer_phone,
                    delivery_street, delivery_number, delivery_complement,
                    delivery_neighborhood, delivery_city, delivery_state, delivery_zip,
                    notes, delivery_type, coupon_id, discount_amount,
                    subtotal, delivery_fee, total,
                    created_at, updated_at
                ) VALUES (
                    :uid,:user_id,:onum,:track,:st,
                    :pm,:ps,:odt,:cf,
                    :cname,:cphone,
                    :dst,:dnum,:dcomp,:dnei,:dcity,:dstate,:dzip,
                    :notes,:dtype,:cid,:disc,
                    :sub,:dfee,:tot,
                    NOW(),NOW()
                )'
            );

            $ins->execute([
                'uid' => $unitId,
                'user_id' => $userId,
                'onum' => $orderNumber,
                'track' => $tracking,
                'st' => 'pendente',
                'pm' => $method,
                'ps' => $paymentStatus,
                'odt' => $onType,
                'cf' => $changeFor,
                'cname' => $customer['name'],
                'cphone' => $customer['phone'],
                'dst' => $delivery['street'],
                'dnum' => $delivery['number'],
                'dcomp' => $delivery['complement'] ?? null,
                'dnei' => $delivery['neighborhood'],
                'dcity' => $delivery['city'],
                'dstate' => $delivery['state'],
                'dzip' => $delivery['zip'],
                'notes' => $delivery['notes'] ?? null,
                'dtype' => $deliveryType,
                'cid' => $couponId,
                'disc' => round($discount, 2),
                'sub' => round($subtotal, 2),
                'dfee' => round($deliveryFee, 2),
                'tot' => round($total, 2),
            ]);

            if ($couponId !== null) {
                CouponService::incrementUsage($pdo, $couponId);
            }

            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, line_total, created_at, updated_at)
                 VALUES (:oid,:pid,:pname,:qty,:up,:lt,NOW(),NOW())'
            );
            $addonStmt = $pdo->prepare(
                'INSERT INTO order_item_addons (order_item_id, product_addon_id, addon_name, addon_price, created_at, updated_at)
                 VALUES (:oiid,:paid,:an,:ap,NOW(),NOW())'
            );

            foreach ($lines as $line) {
                $itemStmt->execute([
                    'oid' => $orderId,
                    'pid' => $line['product_id'],
                    'pname' => $line['product_name'],
                    'qty' => $line['qty'],
                    'up' => $line['unit_price'],
                    'lt' => $line['line_total'],
                ]);
                $oiId = (int) $pdo->lastInsertId();
                foreach ($line['addons'] as $ad) {
                    $addonStmt->execute([
                        'oiid' => $oiId,
                        'paid' => $ad['id'],
                        'an' => $ad['name'],
                        'ap' => $ad['price'],
                    ]);
                }
            }

            $payIns = $pdo->prepare(
                'INSERT INTO payments (order_id, type, status, amount, meta, created_at, updated_at)
                 VALUES (:oid,:type,:st,:amt,:meta,NOW(),NOW())'
            );
            $payIns->execute([
                'oid' => $orderId,
                'type' => $method,
                'st' => 'pendente',
                'amt' => round($total, 2),
                'meta' => '{}',
            ]);
            $paymentId = (int) $pdo->lastInsertId();

            $pdo->prepare('UPDATE payments SET meta = :meta WHERE id = :id')->execute([
                'id' => $paymentId,
                'meta' => json_encode([
                    'source' => 'checkout',
                    'payment_provider' => $payConfig['provider'],
                    'unit_id' => $unitId,
                    'external_reference' => UnitPaymentConfig::paymentReference($paymentId),
                ], JSON_UNESCAPED_UNICODE),
            ]);

            $checkoutUrl = null;

            if ($method === 'pix') {
                $pix = PixService::createForPayment($paymentId, (float) round($total, 2), $unitId);
                $pdo->prepare(
                    'INSERT INTO pix_transactions (payment_id, external_id, qr_code_payload, copy_paste, expires_at, status, created_at, updated_at)
                     VALUES (:pid,:ext,:qr,:cp,:exp,:st,NOW(),NOW())'
                )->execute([
                    'pid' => $paymentId,
                    'ext' => $pix['external_id'],
                    'qr' => $pix['qr_payload'],
                    'cp' => $pix['copy_paste'],
                    'exp' => $pix['expires_at'],
                    'st' => 'criado',
                ]);
            }

            if ($method === 'card') {
                $card = CardPaymentService::createCheckout(
                    $unitId,
                    $paymentId,
                    $orderId,
                    (float) round($total, 2),
                    'Pedido #' . $orderNumber . ' — ' . (string) $customer['name']
                );
                $checkoutUrl = $card['checkout_url'];
                $pdo->prepare(
                    'INSERT INTO card_transactions (payment_id, external_id, checkout_url, status, created_at, updated_at)
                     VALUES (:pid,:ext,:url,:st,NOW(),NOW())'
                )->execute([
                    'pid' => $paymentId,
                    'ext' => $card['external_id'],
                    'url' => $checkoutUrl,
                    'st' => 'criado',
                ]);
            }

            $log = $pdo->prepare(
                'INSERT INTO order_status_logs (order_id, status, note, actor_type, created_at)
                 VALUES (:oid,:st,:n,:a,NOW())'
            );
            $log->execute(['oid' => $orderId, 'st' => 'pendente', 'n' => 'Pedido criado', 'a' => 'customer']);

            $pdo->commit();

            self::notifyStatusSms($orderId, 'pendente');

            $config = require dirname(__DIR__, 2) . '/config/app.php';
            $trackUrl = rtrim((string) ($config['url'] ?? ''), '/') . '/acompanhar/' . $tracking;
            $notifyEmail = (string) ($config['commercial_email'] ?? '');
            if ($notifyEmail !== '' && Env::get('NOTIFY_ORDER_EMAIL', '1') === '1') {
                EmailService::sendOrderConfirmation(
                    $notifyEmail,
                    ['order_number' => $orderNumber, 'total' => round($total, 2), 'customer_name' => $customer['name'], 'customer_phone' => $customer['phone']],
                    $u,
                    $trackUrl
                );
            }

            if ($userId > 0 && Env::get('NOTIFY_CUSTOMER_EMAIL', '0') === '1') {
                try {
                    $ue = $pdo->prepare(
                        'SELECT email FROM users WHERE id = :id AND email IS NOT NULL AND email != "" LIMIT 1'
                    );
                    $ue->execute(['id' => $userId]);
                    $customerEmail = (string) ($ue->fetchColumn() ?: '');
                    if ($customerEmail !== '' && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                        EmailService::sendCustomerOrderCreated(
                            $customerEmail,
                            ['order_number' => $orderNumber, 'total' => round($total, 2)],
                            $u,
                            $trackUrl
                        );
                    }
                } catch (Throwable) {
                    // coluna email pode não existir antes da migration 017
                }
            }

            return [
                'order_id' => $orderId,
                'tracking_token' => $tracking,
                'payment_id' => $paymentId,
                'checkout_url' => $checkoutUrl,
            ];
        } catch (Throwable $e) {
            $pdo->rollBack();
            Logger::log('error', 'Falha ao criar pedido', ['e' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Monta linhas com preços reais vindos do banco para evitar manipulação no cliente.
     *
     * @param list<array<string,mixed>> $items Itens do carrinho em sessão
     * @return list<array<string,mixed>>
     */
    private static function buildLines(PDO $pdo, int $unitId, array $items): array
    {
        $lines = [];
        $productIds = [];
        $addonIds = [];
        foreach ($items as $it) {
            $pid = (int) ($it['product_id'] ?? 0);
            if ($pid > 0) {
                $productIds[] = $pid;
            }
            foreach ((array) ($it['addons'] ?? []) as $aid) {
                $aid = (int) $aid;
                if ($aid > 0) {
                    $addonIds[] = $aid;
                }
            }
        }

        $products = CatalogBatchLoader::productsByIds($pdo, $unitId, $productIds);
        $addonsMap = CatalogBatchLoader::addonsByIds($pdo, $productIds, $addonIds);

        foreach ($items as $it) {
            $pid = (int) ($it['product_id'] ?? 0);
            $qty = max(1, (int) ($it['qty'] ?? 1));
            $p = $products[$pid] ?? null;
            if ($p === null) {
                continue;
            }

            $unitPrice = (float) $p['price'];
            $addons = [];
            $addonsTotal = 0.0;
            foreach ((array) ($it['addons'] ?? []) as $aid) {
                $aid = (int) $aid;
                if ($aid <= 0) {
                    continue;
                }
                $a = $addonsMap[$aid] ?? null;
                if ($a === null || (int) ($a['product_id'] ?? 0) !== $pid) {
                    continue;
                }
                $addons[] = ['id' => (int) $a['id'], 'name' => (string) $a['name'], 'price' => (float) $a['price']];
                $addonsTotal += (float) $a['price'];
            }

            $lineUnit = $unitPrice + $addonsTotal;
            $lineTotal = $lineUnit * $qty;
            $lines[] = [
                'product_id' => $pid,
                'product_name' => (string) $p['name'],
                'qty' => $qty,
                'unit_price' => round($lineUnit, 2),
                'line_total' => round($lineTotal, 2),
                'addons' => $addons,
            ];
        }

        if ($lines === []) {
            throw new \RuntimeException('Nenhum item válido no carrinho.');
        }

        return $lines;
    }

    /**
     * Cancelamento pelo cliente (apenas antes do preparo e sem pagamento PIX confirmado).
     */
    public static function cancelByCustomer(int $orderId, int $userId, ?string $reason = null): void
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT id, status, payment_status, payment_method FROM orders
             WHERE id = :id AND user_id = :u AND deleted_at IS NULL LIMIT 1'
        );
        $st->execute(['id' => $orderId, 'u' => $userId]);
        $order = $st->fetch(PDO::FETCH_ASSOC);
        if ($order === false) {
            throw new \RuntimeException('Pedido não encontrado.');
        }

        $status = (string) $order['status'];
        if (!in_array($status, ['pendente', 'confirmado'], true)) {
            throw new \RuntimeException('Este pedido não pode mais ser cancelado pelo app.');
        }

        if (in_array((string) ($order['payment_method'] ?? ''), ['pix', 'card'], true)
            && ($order['payment_status'] ?? '') === 'pago') {
            throw new \RuntimeException('Pedido já pago online. Fale com a loja para cancelar.');
        }

        $reason = trim((string) ($reason ?? ''));
        if ($reason === '') {
            $reason = 'Cancelado pelo cliente';
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'UPDATE orders SET status = :st, cancel_reason = :r, updated_at = NOW() WHERE id = :id'
            )->execute(['st' => 'cancelado', 'r' => $reason, 'id' => $orderId]);

            $pdo->prepare(
                'INSERT INTO order_status_logs (order_id, status, note, actor_type, actor_id, created_at)
                 VALUES (:oid,:st,:n,:atype,:aid,NOW())'
            )->execute([
                'oid' => $orderId,
                'st' => 'cancelado',
                'n' => $reason,
                'atype' => 'customer',
                'aid' => $userId,
            ]);

            if (in_array((string) ($order['payment_method'] ?? ''), ['pix', 'card'], true)) {
                $pdo->prepare(
                    'UPDATE payments SET status = :st, updated_at = NOW() WHERE order_id = :oid AND type IN ("pix","card")'
                )->execute(['st' => 'cancelado', 'oid' => $orderId]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        AuditLogService::record('customer', $userId, 'order.cancel', 'order', $orderId, [
            'reason' => $reason,
        ]);
    }

    /**
     * Confirma pagamento na entrega após o pedido ser marcado como entregue.
     *
     * @param array<string, mixed>|null $order Linha de orders (opcional)
     */
    public static function confirmOnDeliveryPayment(PDO $pdo, int $orderId, ?array $order = null): void
    {
        if ($order === null) {
            $st = $pdo->prepare('SELECT payment_method FROM orders WHERE id = :id LIMIT 1');
            $st->execute(['id' => $orderId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            $order = $row !== false ? $row : [];
        }

        if (($order['payment_method'] ?? '') !== 'on_delivery') {
            return;
        }

        $pdo->prepare('UPDATE orders SET payment_status = :ps, updated_at = NOW() WHERE id = :id')
            ->execute(['ps' => 'confirmado_entrega', 'id' => $orderId]);
        $pdo->prepare(
            'UPDATE payments SET status = :st, updated_at = NOW() WHERE order_id = :oid AND type = "on_delivery"'
        )->execute(['st' => 'pago', 'oid' => $orderId]);
    }

    /**
     * Envia SMS opcional ao cliente quando o status do pedido muda.
     */
    public static function notifyStatusSms(int $orderId, string $status): void
    {
        if (Env::get('NOTIFY_ORDER_SMS', '0') !== '1') {
            return;
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT customer_phone, order_number FROM orders WHERE id = :id LIMIT 1');
        $st->execute(['id' => $orderId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return;
        }

        $e164 = \App\Helpers\Phone::normalizeBr((string) $row['customer_phone']);
        if ($e164 === null) {
            return;
        }

        $msg = sprintf('Desk Food: pedido %s agora está em "%s".', $row['order_number'], $status);
        JobQueueService::dispatch('sms', ['to' => $e164, 'message' => $msg]);
    }
}
