<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Normaliza payloads de webhook PIX (Efi, Mercado Pago, genérico) para external_id.
 */
final class PixWebhookNormalizer
{
    /**
     * @param array<string, mixed> $payload
     * @return list<string> IDs para buscar em pix_transactions.external_id (sem duplicatas)
     */
    public static function extractExternalIds(array $payload): array
    {
        $ids = [];

        $direct = (string) ($payload['txid'] ?? $payload['external_id'] ?? '');
        if ($direct !== '') {
            $ids[] = $direct;
        }

        if (isset($payload['pix']) && is_array($payload['pix'])) {
            foreach ($payload['pix'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $txid = (string) ($item['txid'] ?? '');
                if ($txid !== '') {
                    $ids[] = $txid;
                }
            }
        }

        $data = $payload['data'] ?? null;
        if (is_array($data)) {
            $mpId = (string) ($data['id'] ?? '');
            if ($mpId !== '') {
                $ids[] = $mpId;
            }
        }

        $resourceId = (string) ($payload['id'] ?? '');
        if ($resourceId !== '' && self::looksLikeMercadoPagoNotification($payload)) {
            $ids[] = $resourceId;
        }

        return array_values(array_unique(array_filter($ids, static fn (string $id): bool => $id !== '')));
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function isEfiPixPayload(array $payload): bool
    {
        if (!isset($payload['pix']) || !is_array($payload['pix'])) {
            return false;
        }

        foreach ($payload['pix'] as $item) {
            if (is_array($item) && isset($item['txid'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function looksLikeMercadoPagoNotification(array $payload): bool
    {
        $type = (string) ($payload['type'] ?? '');
        $action = (string) ($payload['action'] ?? '');
        if ($type === 'payment' || str_contains($action, 'payment')) {
            return true;
        }

        return isset($payload['data']['id']);
    }

    /**
     * @param array<string, mixed> $query GET params (topic, id)
     * @return array<string, mixed>|null
     */
    public static function mercadoPagoFromQuery(array $query): ?array
    {
        $topic = (string) ($query['topic'] ?? $query['type'] ?? '');
        $id = (string) ($query['id'] ?? $query['data_id'] ?? '');
        if ($id === '') {
            return null;
        }
        if ($topic !== '' && $topic !== 'payment' && !str_contains($topic, 'payment')) {
            return null;
        }

        return [
            'type' => 'payment',
            'action' => 'payment.updated',
            'data' => ['id' => $id],
        ];
    }
}
