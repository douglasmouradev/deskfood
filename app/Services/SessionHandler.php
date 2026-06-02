<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use PDO;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

/**
 * Persistência de sessões PHP na tabela `sessions` (MySQL).
 *
 * Permite escalabilidade horizontal sem arquivos de sessão no disco e
 * associa opcionalmente sessões a usuários clientes ou administradores.
 */
final class SessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = Database::pdo()->prepare(
            'SELECT payload FROM sessions WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? (string) $row['payload'] : '';
    }

    public function write(string $id, string $data): bool
    {
        $pdo = Database::pdo();
        $now = time();
        $userId = $_SESSION['user_id'] ?? null;
        $adminId = $_SESSION['admin_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512);

        $stmt = $pdo->prepare(
            'INSERT INTO sessions (id, user_id, admin_id, payload, last_activity, ip_address, user_agent, created_at, updated_at)
             VALUES (:id, :user_id, :admin_id, :payload, :last_activity, :ip, :ua, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
               user_id = VALUES(user_id),
               admin_id = VALUES(admin_id),
               payload = VALUES(payload),
               last_activity = VALUES(last_activity),
               ip_address = VALUES(ip_address),
               user_agent = VALUES(user_agent),
               updated_at = NOW()'
        );

        return $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'admin_id' => $adminId,
            'payload' => $data,
            'last_activity' => $now,
            'ip' => $ip,
            'ua' => $ua,
        ]);
    }

    public function destroy(string $id): bool
    {
        $stmt = Database::pdo()->prepare('DELETE FROM sessions WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $threshold = time() - $max_lifetime;
        $stmt = Database::pdo()->prepare('DELETE FROM sessions WHERE last_activity < :t');
        $stmt->execute(['t' => $threshold]);

        return $stmt->rowCount();
    }

    public function validateId(string $id): bool
    {
        return preg_match('/^[a-zA-Z0-9,-]{22,128}$/', $id) === 1;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return $this->write($id, $data);
    }
}
