<?php

namespace App\Enums;

enum ChargeStatus: string
{
    case Unpaid        = 'unpaid';
    case PartiallyPaid = 'partially_paid';
    case Paid          = 'paid';
    case Cancelled     = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Unpaid        => 'Unpaid',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid          => 'Paid',
            self::Cancelled     => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Unpaid        => 'bg-red-100 text-red-700',
            self::PartiallyPaid => 'bg-yellow-100 text-yellow-700',
            self::Paid          => 'bg-green-100 text-green-700',
            self::Cancelled     => 'bg-gray-100 text-gray-500',
        };
    }
}
