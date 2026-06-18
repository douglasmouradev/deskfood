<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Valida se o endereço de entrega está dentro da área atendida pela unidade.
 */
final class DeliveryService
{
    /**
     * @param array<string,mixed> $unit
     * @param array<string,mixed> $delivery
     */
    public static function assertDeliverable(array $unit, array $delivery): void
    {
        $radius = (float) ($unit['delivery_radius_km'] ?? 0);
        if ($radius <= 0) {
            return;
        }

        $unitCity = \App\Helpers\Str::lower(trim((string) ($unit['city'] ?? '')));
        $unitState = strtoupper(trim((string) ($unit['state'] ?? '')));
        $delCity = \App\Helpers\Str::lower(trim((string) ($delivery['city'] ?? '')));
        $delState = strtoupper(trim((string) ($delivery['state'] ?? '')));

        if ($unitCity === '' || $delCity === '') {
            throw new \RuntimeException('Informe cidade e estado para entrega.');
        }

        if ($unitState !== $delState) {
            throw new \RuntimeException(
                sprintf('Entregamos apenas em %s (%s). Seu endereço está em outro estado.', $unit['city'] ?? '', $unitState)
            );
        }

        if ($unitCity !== $delCity) {
            throw new \RuntimeException(
                sprintf('Raio de entrega: até %.1f km da unidade. Endereço fora de %s.', $radius, $unit['city'] ?? '')
            );
        }

        $unitZip = preg_replace('/\D/', '', (string) ($unit['zip'] ?? ''));
        $delZip = preg_replace('/\D/', '', (string) ($delivery['zip'] ?? ''));
        if (strlen($unitZip) >= 5 && strlen($delZip) >= 5) {
            $unitPrefix = (int) substr($unitZip, 0, 5);
            $delPrefix = (int) substr($delZip, 0, 5);
            $diff = abs($unitPrefix - $delPrefix);
            $maxDiff = (int) max(1, ceil($radius * 15));
            if ($diff > $maxDiff) {
                throw new \RuntimeException(
                    sprintf('CEP fora da área de entrega (até %.1f km).', $radius)
                );
            }
        }
    }
}
