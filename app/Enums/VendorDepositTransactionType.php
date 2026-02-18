<?php

namespace App\Enums;

enum VendorDepositTransactionType: string
{
    case ADD = 'add';
    case SUBTRACT = 'subtract';
    case TRANSACTION = 'transaction'; // Yatırım - depozito azalır
    case WITHDRAWAL = 'withdrawal'; // Çekim - depozito artar

    public function label(): string
    {
        return match ($this) {
            self::ADD => __('Add Deposit'),
            self::SUBTRACT => __('Subtract Deposit'),
            self::TRANSACTION => __('Transaction Deposit'),
            self::WITHDRAWAL => __('Withdrawal Deposit'),
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::ADD => '<span class="badge bg-success bg-opacity-10 text-success">' . __('Add Deposit') . '</span>',
            self::SUBTRACT => '<span class="badge bg-warning bg-opacity-10 text-warning">' . __('Subtract Deposit') . '</span>',
            self::TRANSACTION => '<span class="badge bg-danger bg-opacity-10 text-danger">' . __('Transaction Deposit') . '</span>',
            self::WITHDRAWAL => '<span class="badge bg-primary bg-opacity-10 text-primary">' . __('Withdrawal Deposit') . '</span>',
        };
    }

    /**
     * Check if this type increases deposit
     */
    public function increasesDeposit(): bool
    {
        return in_array($this, [self::ADD, self::WITHDRAWAL]);
    }

    /**
     * Check if this type decreases deposit
     */
    public function decreasesDeposit(): bool
    {
        return in_array($this, [self::SUBTRACT, self::TRANSACTION]);
    }
}

