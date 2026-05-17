<?php

namespace App\Enums;

enum OfferStatus: string
{
    case Draft    = 'draft';
    case Sent     = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Draft    => 'Draft',
            self::Sent     => 'Sent',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft    => 'bg-gray-100 text-gray-600',
            self::Sent     => 'bg-blue-100 text-blue-700',
            self::Accepted => 'bg-green-100 text-green-700',
            self::Rejected => 'bg-red-100 text-red-700',
        };
    }
}
