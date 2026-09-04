<?php

namespace App\Enum;

enum ServiceType: string
{
    case REPAIR = 'repair';
    case REPLACEMENT = 'replacement';
    case INSPECTION = 'inspection';
    case MAINTENANCE = 'maintenance';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::REPAIR => 'Repair',
            self::REPLACEMENT => 'Replacement',
            self::INSPECTION => 'Inspection',
            self::MAINTENANCE => 'Maintenance',
            self::OTHER => 'Other',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
