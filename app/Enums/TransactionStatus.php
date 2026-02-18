<?php

namespace App\Enums;

enum TransactionStatus: int
{
    case Pending = 0;
    case Processing = 1;
    case Cancelled = 2;
    case AutoConfirmed = 3;
    case ManualConfirmed = 30;
    case AutoCancelled = 4;
    case ManualCancelled = 40;
    case Draft = 5;

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Processing => __('Processing'),
            self::Cancelled => __('Cancelled'),
            self::AutoConfirmed => __('Auto Confirmed'),
            self::ManualConfirmed => __('Manual Confirmed'),
            self::AutoCancelled => __('Auto Cancelled'),
            self::ManualCancelled => __('Manual Cancelled'),
            self::Draft => __('Draft'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-light text-body',
            self::Processing => 'bg-opacity-10 bg-primary text-primary',
            self::Cancelled => 'bg-opacity-10 bg-danger text-danger',
            self::AutoConfirmed => 'bg-opacity-10 bg-success text-success',
            self::ManualConfirmed => 'bg-opacity-10 bg-success text-success',
            self::AutoCancelled => 'bg-opacity-10 bg-warning text-warning',
            self::ManualCancelled => 'bg-opacity-10 bg-warning text-warning',
            self::Draft => 'bg-light text-body',
        };
    }
}
