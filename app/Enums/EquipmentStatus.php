<?php

namespace App\Enums;

enum EquipmentStatus: string
{
    case InUse = 'in_use';
    case UnderReview = 'under_review';
    case OutForRepair = 'out_for_repair';
    case OutOfService = 'out_of_service';
    case Retired = 'retired';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::InUse => 'In Use',
            self::UnderReview => 'Under Review',
            self::OutForRepair => 'Out for Repair',
            self::OutOfService => 'Out of Service',
            self::Retired => 'Retired',
            self::Lost => 'Lost',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::InUse => 'emerald',
            self::UnderReview => 'amber',
            self::OutForRepair => 'blue',
            self::OutOfService => 'rose',
            self::Retired => 'zinc',
            self::Lost => 'purple',
        };
    }

    public function isAvailable(): bool
    {
        return $this === self::InUse;
    }
}
