<?php

namespace App\Enums;

enum ParaQrStatus: int
{
    case PENDING = 0;
    case CONFIRMED = 1;
    case REFUSED = 2;
    case DECLINED = 3;
    case REFUNDED = 4;
    case OBJECTED = 5;
    case CANCELED = 6;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::CONFIRMED => __('Confirmed'),
            self::REFUSED => __('Refused'),
            self::DECLINED => __('Declined'),
            self::REFUNDED => __('Refunded'),
            self::OBJECTED => __('Objected'),
            self::CANCELED => __('Canceled')
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'bg-opacity-10 bg-warning text-warning',
            self::CONFIRMED => 'bg-opacity-10 bg-success text-success',
            self::REFUSED => 'bg-opacity-10 bg-warning text-warning',
            self::DECLINED => 'bg-opacity-10 bg-danger text-danger',
            self::REFUNDED => 'bg-opacity-10 bg-secondary text-secondary',
            self::OBJECTED => 'bg-opacity-10 bg-light text-black',
            self::CANCELED => 'bg-opacity-10 bg-danger text-danger'
        };
    }
}
