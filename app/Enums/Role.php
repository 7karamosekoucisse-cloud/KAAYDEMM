<?php
declare(strict_types=1);
namespace App\Enums;

enum Role: string
{
    case PASSENGER = 'PASSENGER';
    case DRIVER    = 'DRIVER';
    case ADMIN     = 'ADMIN';

    public function label(): string
    {
        return match($this) {
            self::PASSENGER => 'Passager',
            self::DRIVER    => 'Conducteur',
            self::ADMIN     => 'Administrateur',
        };
    }
}
