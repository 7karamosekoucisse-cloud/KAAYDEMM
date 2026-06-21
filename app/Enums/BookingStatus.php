<?php
declare(strict_types=1);
namespace App\Enums;

enum BookingStatus: string
{
    case PENDING   = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match($this) {
            self::PENDING   => 'En attente',
            self::CONFIRMED => 'Confirmée',
            self::COMPLETED => 'Terminée',
            self::CANCELLED => 'Annulée',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::PENDING   => 'bg-warning text-dark',
            self::CONFIRMED => 'bg-success',
            self::COMPLETED => 'bg-info',
            self::CANCELLED => 'bg-danger',
        };
    }

    /** @return self[] */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::PENDING   => [self::CONFIRMED, self::CANCELLED],
            self::CONFIRMED => [self::COMPLETED, self::CANCELLED],
            self::COMPLETED, self::CANCELLED => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
