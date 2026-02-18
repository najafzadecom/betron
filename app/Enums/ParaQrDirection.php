<?php

namespace App\Enums;

enum ParaQrDirection: int
{
    case DEPOSIT = 0;
    case WITHDRAW = 1;

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => __('Deposit'),
            self::WITHDRAW => __('Withdraw'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DEPOSIT => 'bg-opacity-10 bg-success text-success',
            self::WITHDRAW => 'bg-opacity-10 bg-warning text-warning',
        };
    }
}
