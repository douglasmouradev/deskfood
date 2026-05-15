<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use PDO;

/**
 * Endereços salvos do cliente.
 */
final class AddressService
{
    /** @return list<array<string,mixed>> */
    public static function listForUser(int $userId): array
    {
        try {
            $pdo = Database::pdo();
            $st = $pdo->prepare(
                'SELECT * FROM user_addresses WHERE user_id = :u ORDER BY is_default DESC, id DESC'
            );
            $st->execute(['u' => $userId]);

            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function saveFromCheckout(int $userId, array $data, bool $setDefault = false): void
    {
        try {
            $pdo = Database::pdo();
            if ($setDefault) {
                $pdo->prepare('UPDATE user_addresses SET is_default = 0 WHERE user_id = :u')->execute(['u' => $userId]);
            }

            $pdo->prepare(
                'INSERT INTO user_addresses (user_id, label, street, number, complement, neighborhood, city, state, zip, is_default, created_at, updated_at)
                 VALUES (:u,:l,:st,:n,:c,:nei,:city,:state,:zip,:def,NOW(),NOW())'
            )->execute([
                'u' => $userId,
                'l' => 'Casa',
                'st' => $data['street'],
                'n' => $data['number'],
                'c' => $data['complement'] ?? null,
                'nei' => $data['neighborhood'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip' => $data['zip'],
                'def' => $setDefault ? 1 : 0,
            ]);
        } catch (\Throwable) {
            // tabela pode não existir antes da migration
        }
    }

    public static function find(int $userId, int $addressId): ?array
    {
        try {
            $pdo = Database::pdo();
            $st = $pdo->prepare('SELECT * FROM user_addresses WHERE id = :id AND user_id = :u LIMIT 1');
            $st->execute(['id' => $addressId, 'u' => $userId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);

            return $row !== false ? $row : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
