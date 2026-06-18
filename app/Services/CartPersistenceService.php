<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use PDO;

/**
 * Persiste carrinho em MySQL (sobrevive a restart de sessão / sticky session opcional).
 */
final class CartPersistenceService
{
    public static function enabled(): bool
    {
        return \App\Helpers\Env::get('CART_PERSIST', '1') === '1';
    }

    /**
     * @param array<string, mixed> $cart
     */
    public static function save(array $cart): void
    {
        if (!self::enabled() || empty($cart['unit_id'])) {
            return;
        }

        $sid = session_id();
        if ($sid === '') {
            return;
        }

        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $pdo = Database::pdo();
        $pdo->prepare(
            'INSERT INTO session_carts (session_id, user_id, unit_id, payload, created_at, updated_at)
             VALUES (:s, :u, :uid, :p, NOW(), NOW())
             ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), unit_id = VALUES(unit_id), payload = VALUES(payload), updated_at = NOW()'
        )->execute([
            's' => $sid,
            'u' => $userId,
            'uid' => (int) $cart['unit_id'],
            'p' => json_encode($cart, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function load(): ?array
    {
        if (!self::enabled()) {
            return null;
        }

        $sid = session_id();
        if ($sid === '') {
            return null;
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT payload FROM session_carts WHERE session_id = :s LIMIT 1');
        $st->execute(['s' => $sid]);
        $raw = $st->fetchColumn();
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode((string) $raw, true);

        return is_array($data) ? $data : null;
    }

    public static function clear(): void
    {
        $sid = session_id();
        if ($sid === '') {
            return;
        }

        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM session_carts WHERE session_id = :s')->execute(['s' => $sid]);
    }

    /**
     * Hidrata $_SESSION['cart'] se vazio.
     */
    public static function hydrateSession(): void
    {
        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            return;
        }

        $loaded = self::load();
        if ($loaded !== null) {
            $_SESSION['cart'] = $loaded;
        }
    }
}
