<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\Logger;
use PDO;
use Throwable;

/**
 * Confirma pagamento online (PIX ou cartão) e atualiza pedido.
 */
final class PaymentConfirmationService
{
    /**
     * @param array<string, mixed> $payload
     * @return array{ok:bool,http:int,duplicate?:bool,message?:string}
     */
    public static function confirmByPaymentId(int $paymentId, array $payload): array
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT p.id AS payment_id, p.type, p.status AS pay_status, p.order_id,
                        o.status AS order_status, o.payment_status AS order_payment_status
                 FROM payments p
                 INNER JOIN orders o ON o.id = p.order_id
                 WHERE p.id = :pid
                 LIMIT 1
                 FOR UPDATE'
            );
            $stmt->execute(['pid' => $paymentId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                $pdo->rollBack();
                AuditLogService::record('system', null, 'webhook.payment.not_found', 'payment', $paymentId, []);

                return ['ok' => false, 'http' => 404, 'message' => 'Pagamento não encontrado.'];
            }

            if (($row['pay_status'] ?? '') === 'pago') {
                $pdo->commit();
                AuditLogService::record('system', null, 'webhook.payment.duplicate', 'order', (int) $row['order_id'], ['payment_id' => $paymentId]);

                return ['ok' => true, 'http' => 200, 'duplicate' => true, 'message' => 'Já processado.'];
            }

            $type = (string) ($row['type'] ?? '');
            $pdo->prepare('UPDATE payments SET status = :s, updated_at = NOW() WHERE id = :id')
                ->execute(['s' => 'pago', 'id' => $paymentId]);

            if ($type === 'pix') {
                $pdo->prepare(
                    'UPDATE pix_transactions SET status = :s, webhook_payload = :w, updated_at = NOW() WHERE payment_id = :pid'
                )->execute([
                    's' => 'pago',
                    'w' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'pid' => $paymentId,
                ]);
            }

            if ($type === 'card') {
                $pdo->prepare(
                    'UPDATE card_transactions SET status = :s, webhook_payload = :w, updated_at = NOW() WHERE payment_id = :pid'
                )->execute([
                    's' => 'pago',
                    'w' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'pid' => $paymentId,
                ]);
            }

            $orderId = (int) $row['order_id'];
            $pdo->prepare(
                'UPDATE orders SET payment_status = :ps, status = CASE WHEN status = :p THEN :c ELSE status END, updated_at = NOW() WHERE id = :oid'
            )->execute([
                'ps' => 'pago',
                'p' => 'pendente',
                'c' => 'confirmado',
                'oid' => $orderId,
            ]);

            $pdo->prepare(
                'INSERT INTO order_status_logs (order_id, status, note, actor_type, created_at) VALUES (:oid,:st,:n,:a,NOW())'
            )->execute([
                'oid' => $orderId,
                'st' => 'confirmado',
                'n' => $type === 'card' ? 'Pagamento com cartão confirmado' : 'Pagamento PIX confirmado',
                'a' => 'system',
            ]);

            $pdo->commit();

            AuditLogService::record('system', null, 'webhook.payment.confirmed', 'order', $orderId, [
                'payment_id' => $paymentId,
                'type' => $type,
            ]);

            try {
                CashRegisterService::recordSaleIfOpen($orderId);
            } catch (Throwable $e) {
                Logger::log('error', 'Falha ao registrar venda no caixa', ['e' => $e->getMessage()]);
            }

            return ['ok' => true, 'http' => 200, 'message' => 'Pagamento confirmado.'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::log('error', 'Erro ao confirmar pagamento', ['payment_id' => $paymentId, 'e' => $e->getMessage()]);

            return ['ok' => false, 'http' => 500, 'message' => 'Erro interno.'];
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{ok:bool,http:int,duplicate?:bool,message?:string}
     */
    public static function confirmByExternalId(string $externalId, array $payload): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT payment_id FROM pix_transactions WHERE external_id = :e LIMIT 1'
        );
        $st->execute(['e' => $externalId]);
        $pixPaymentId = $st->fetchColumn();

        if ($pixPaymentId !== false) {
            return self::confirmByPaymentId((int) $pixPaymentId, $payload);
        }

        $st2 = $pdo->prepare(
            'SELECT payment_id FROM card_transactions WHERE external_id = :e LIMIT 1'
        );
        $st2->execute(['e' => $externalId]);
        $cardPaymentId = $st2->fetchColumn();
        if ($cardPaymentId !== false) {
            return self::confirmByPaymentId((int) $cardPaymentId, $payload);
        }

        AuditLogService::record('system', null, 'webhook.payment.not_found', 'payment', null, ['external_id' => $externalId]);

        return ['ok' => false, 'http' => 404, 'message' => 'Transação não encontrada.'];
    }
}
