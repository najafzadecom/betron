<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case Paypap = 'paypap';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Paypap => __('Paypap'),
            self::Manual => __('Manual'),
            default => __('Unknown'),
        };
    }
}
