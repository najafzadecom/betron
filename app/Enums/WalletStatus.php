<?php

namespace App\Enums;

enum WalletStatus: int
{
    case Inactive = 0;
    case Active = 1;
    case Pending = 2;

    public function label(): string
    {
        return match ($this) {
            self::Inactive => __('Inactive'),
            self::Active => __('Active'),
            self::Pending => __('Pending')
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Inactive => 'bg-opacity-10 bg-danger text-danger',
            self::Active => 'bg-opacity-10 bg-success text-success',
            self::Pending => 'bg-opacity-10 bg-warning text-warning',
        };
    }
}
