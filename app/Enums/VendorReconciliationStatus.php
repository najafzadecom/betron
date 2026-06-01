<?php

namespace App\Enums;

enum VendorReconciliationStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Approved => __('Approved'),
            self::Archived => __('Archived'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-warning bg-opacity-10 text-warning',
            self::Approved => 'bg-success bg-opacity-10 text-success',
            self::Archived => 'bg-secondary bg-opacity-10 text-secondary',
        };
    }
}
