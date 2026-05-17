<?php

namespace App\Enums;

enum RecurringChargeCategory: string
{
    case Hosting     = 'hosting';
    case Domain      = 'domain';
    case Maintenance = 'maintenance';
    case Bundle      = 'bundle';
    case Other       = 'other';

    public function label(): string
    {
        return match($this) {
            self::Hosting     => 'Hosting',
            self::Domain      => 'Domain',
            self::Maintenance => 'Maintenance',
            self::Bundle      => 'Bundle',
            self::Other       => 'Other',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Hosting     => 'bg-blue-100 text-blue-700',
            self::Domain      => 'bg-purple-100 text-purple-700',
            self::Maintenance => 'bg-yellow-100 text-yellow-700',
            self::Bundle      => 'bg-indigo-100 text-indigo-700',
            self::Other       => 'bg-gray-100 text-gray-600',
        };
    }
}
