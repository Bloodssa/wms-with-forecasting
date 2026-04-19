<?php

namespace App\Enum;

enum WarrantyStatusType: string
{
    case ACTIVE = 'active';
    case PENDING = 'pending';
    case NEAR_EXPIRY = 'near-expiry';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}   
