<?php
declare(strict_types=1);
namespace App\Enums;

enum TripStatus: string
{
    case OPEN      = 'OPEN';
    case FULL      = 'FULL';
    case CANCELLED = 'CANCELLED';
    case COMPLETED = 'COMPLETED';

    public function label(): string
    {
        return match($this) {
            self::OPEN      => 'Disponible',
            self::FULL      => 'Complet',
            self::CANCELLED => 'Annulé',
            self::COMPLETED => 'Terminé',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::OPEN      => 'bg-success',
            self::FULL      => 'bg-warning text-dark',
            self::CANCELLED => 'bg-danger',
            self::COMPLETED => 'bg-secondary',
        };
    }
}
