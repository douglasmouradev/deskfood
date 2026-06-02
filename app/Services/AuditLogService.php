<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\ClientIp;
use App\Helpers\Logger;
use PDO;
use Throwable;

/**
 * Registro de auditoria para ações sensíveis (webhooks, segurança, etc.).
 */
final class AuditLogService
{
    /**
     * Persiste uma linha em `audit_logs` sem interromper o fluxo em caso de falha.
     *
     * @param array<string, mixed> $details Payload JSON opcional
     */
    public static function record(
        string $actorType,
        ?int $actorId,
        string $action,
        ?string $entityType,
        ?int $entityId,
        array $details = []
    ): void {
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare(
                'INSERT INTO audit_logs (actor_type, actor_id, action, entity_type, entity_id, ip_address, user_agent, details, created_at)
                 VALUES (:at,:aid,:act,:et,:eid,:ip,:ua,:det,NOW())'
            );
            $stmt->execute([
                'at' => $actorType,
                'aid' => $actorId,
                'act' => $action,
                'et' => $entityType,
                'eid' => $entityId,
                'ip' => ClientIp::get(),
                'ua' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512),
                'det' => $details !== [] ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            ]);
        } catch (Throwable $e) {
            Logger::log('error', 'Falha ao gravar audit_logs', ['e' => $e->getMessage()]);
        }
    }
}
