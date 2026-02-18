<?php

namespace App\Enums;

enum Currency: string
{
    case TRY = 'TRY';
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';

    public function code(): string
    {
        return match ($this) {
            self::TRY => '₺',
            self::USD => '$',
            self::EUR => '€',
            self::GBP => '£',
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::TRY => 'TRY',
            self::USD => 'USD',
            self::EUR => 'EUR',
            self::GBP => 'GBP',
        };
    }
}
