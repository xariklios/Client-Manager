<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function label(): string
    {
        return match($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
            self::Archived => 'Archived',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active   => 'bg-green-100 text-green-700',
            self::Inactive => 'bg-yellow-100 text-yellow-700',
            self::Archived => 'bg-gray-100 text-gray-600',
        };
    }
}
