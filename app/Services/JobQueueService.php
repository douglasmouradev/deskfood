<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\Env;
use App\Helpers\Logger;
use PDO;
use Throwable;

/**
 * Fila simples em MySQL para SMS, e-mail e tarefas pesadas.
 */
final class JobQueueService
{
    public static function dispatch(string $type, array $payload, int $delaySeconds = 0): void
    {
        if (Env::get('JOBS_ASYNC', '0') !== '1') {
            self::execute($type, $payload);

            return;
        }

        $pdo = Database::pdo();
        $available = (new \DateTimeImmutable('+' . max(0, $delaySeconds) . ' seconds'))->format('Y-m-d H:i:s');
        try {
            $pdo->prepare(
                'INSERT INTO background_jobs (job_type, payload, available_at, created_at, updated_at)
                 VALUES (:t, :p, :a, NOW(), NOW())'
            )->execute([
                't' => $type,
                'p' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'a' => $available,
            ]);
        } catch (Throwable $e) {
            Logger::log('warning', 'job_queue_fallback_sync', ['type' => $type, 'message' => $e->getMessage()]);
            self::execute($type, $payload);
        }
    }

    /**
     * Processa um lote de jobs (CLI worker / cron).
     */
    public static function work(int $maxJobs = 25): int
    {
        $pdo = Database::pdo();
        $processed = 0;

        for ($i = 0; $i < $maxJobs; ++$i) {
            $pdo->beginTransaction();
            try {
                $st = $pdo->query(
                    'SELECT id, job_type, payload, attempts, max_attempts
                     FROM background_jobs
                     WHERE failed_at IS NULL AND reserved_at IS NULL AND available_at <= NOW()
                     ORDER BY id ASC
                     LIMIT 1
                     FOR UPDATE SKIP LOCKED'
                );
                $job = $st->fetch(PDO::FETCH_ASSOC);
                if ($job === false) {
                    $pdo->commit();
                    break;
                }

                $pdo->prepare('UPDATE background_jobs SET reserved_at = NOW(), updated_at = NOW() WHERE id = :id')
                    ->execute(['id' => (int) $job['id']]);
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                Logger::log('error', 'Job lock falhou', ['e' => $e->getMessage()]);
                break;
            }

            $id = (int) $job['id'];
            $type = (string) $job['job_type'];
            $payload = json_decode((string) $job['payload'], true);
            if (!is_array($payload)) {
                self::markFailed($pdo, $id, 'Payload inválido');
                continue;
            }

            try {
                self::execute($type, $payload);
                $pdo->prepare('DELETE FROM background_jobs WHERE id = :id')->execute(['id' => $id]);
                ++$processed;
            } catch (Throwable $e) {
                $attempts = (int) $job['attempts'] + 1;
                $max = (int) $job['max_attempts'];
                if ($attempts >= $max) {
                    self::markFailed($pdo, $id, $e->getMessage(), $attempts);
                } else {
                    $retry = (new \DateTimeImmutable('+' . min(300, 30 * $attempts) . ' seconds'))->format('Y-m-d H:i:s');
                    $pdo->prepare(
                        'UPDATE background_jobs SET attempts = :a, reserved_at = NULL, available_at = :r, last_error = :e, updated_at = NOW() WHERE id = :id'
                    )->execute(['a' => $attempts, 'r' => $retry, 'e' => substr($e->getMessage(), 0, 500), 'id' => $id]);
                }
            }
        }

        return $processed;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function execute(string $type, array $payload): void
    {
        match ($type) {
            'sms' => SmsService::send(
                (string) ($payload['to'] ?? ''),
                (string) ($payload['message'] ?? '')
            ),
            'email' => EmailService::send(
                (string) ($payload['to'] ?? ''),
                (string) ($payload['subject'] ?? ''),
                (string) ($payload['body'] ?? ''),
                isset($payload['text']) ? (string) $payload['text'] : null
            ),
            default => throw new \RuntimeException('Tipo de job desconhecido: ' . $type),
        };
    }

    private static function markFailed(PDO $pdo, int $id, string $error, int $attempts = 0): void
    {
        $pdo->prepare(
            'UPDATE background_jobs SET failed_at = NOW(), reserved_at = NULL, attempts = :a, last_error = :e, updated_at = NOW() WHERE id = :id'
        )->execute(['a' => $attempts, 'e' => substr($error, 0, 500), 'id' => $id]);
    }
}
