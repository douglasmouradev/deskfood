<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Interpreta business_hours (JSON) das unidades e valida se estão abertas.
 */
final class BusinessHoursService
{
    /** @var array<int, string> */
    private const DAY_KEYS = [
        0 => 'dom',
        1 => 'seg',
        2 => 'ter',
        3 => 'qua',
        4 => 'qui',
        5 => 'sex',
        6 => 'sab',
    ];

    /**
     * @param array<string, mixed> $unit Linha de units com business_hours opcional
     */
    public static function isOpen(array $unit, ?\DateTimeInterface $at = null): bool
    {
        $hours = self::parse((string) ($unit['business_hours'] ?? ''));
        if ($hours === null) {
            return true;
        }

        $at = $at ?? new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
        $key = self::DAY_KEYS[(int) $at->format('w')] ?? null;
        if ($key === null || !isset($hours[$key])) {
            return true;
        }

        $slot = $hours[$key];
        if (!is_array($slot) || empty($slot['open']) || empty($slot['close'])) {
            return true;
        }

        $open = self::timeToMinutes((string) $slot['open']);
        $close = self::timeToMinutes((string) $slot['close']);
        if ($open === null || $close === null) {
            return true;
        }

        $now = ((int) $at->format('H')) * 60 + (int) $at->format('i');
        if ($close > $open) {
            return $now >= $open && $now < $close;
        }

        return $now >= $open || $now < $close;
    }

    /**
     * @param array<string, mixed> $unit
     */
    public static function statusLabel(array $unit, ?\DateTimeInterface $at = null): string
    {
        if (self::isOpen($unit, $at)) {
            return 'Aberto agora';
        }

        $hours = self::parse((string) ($unit['business_hours'] ?? ''));
        if ($hours === null) {
            return 'Fechado no momento';
        }

        $at = $at ?? new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
        $key = self::DAY_KEYS[(int) $at->format('w')] ?? null;
        $slot = ($key !== null && isset($hours[$key]) && is_array($hours[$key])) ? $hours[$key] : null;
        if ($slot !== null && !empty($slot['open'])) {
            return 'Fechado · abre às ' . (string) $slot['open'];
        }

        return 'Fechado no momento';
    }

    /**
     * @return array<string, array{open?:string, close?:string}>|null
     */
    private static function parse(string $json): ?array
    {
        $json = trim($json);
        if ($json === '') {
            return null;
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($data) ? $data : null;
    }

    private static function timeToMinutes(string $time): ?int
    {
        if (!preg_match('/^(\d{1,2}):(\d{2})$/', trim($time), $m)) {
            return null;
        }

        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($h > 23 || $min > 59) {
            return null;
        }

        return $h * 60 + $min;
    }
}
