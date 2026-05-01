<?php

namespace App\Modules\RH\Support\Enums;

final class EmployeeStatus
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const ARCHIVED = 'archived';

    public static function fromAtivo(mixed $ativo): string
    {
        if (self::isInactiveValue($ativo)) {
            return self::ARCHIVED;
        }

        return self::ACTIVE;
    }

    public static function isActiveValue(mixed $ativo): bool
    {
        return $ativo === null || in_array($ativo, [1, '1', 'S', 's', 'SIM', 'sim', 'A', 'a', true], true);
    }

    public static function isInactiveValue(mixed $ativo): bool
    {
        return in_array($ativo, [0, '0', 'N', 'n', 'NAO', 'nao', 'NÃO', 'não', 'I', 'i', false], true);
    }

    public static function toAtivoColumn(string $status): mixed
    {
        return match ($status) {
            self::INACTIVE, self::ARCHIVED => 0,
            default => 1,
        };
    }

    public static function all(): array
    {
        return [self::ACTIVE, self::INACTIVE, self::ARCHIVED];
    }
}
