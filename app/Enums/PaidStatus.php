<?php

namespace App\Enums;

enum PaidStatus: int
{
    case Paid = 1;
    case Unpaid = 0;

    public function label(): string
    {
        return match ($this) {
            self::Paid => __('Paid'),
            self::Unpaid => __('Unpaid'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Paid => 'bg-opacity-10 bg-success text-success',
            self::Unpaid => 'bg-opacity-10 bg-warning text-warning',
        };
    }
}
