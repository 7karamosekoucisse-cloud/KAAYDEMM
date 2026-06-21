<?php
declare(strict_types=1);
namespace App\Enums;

enum RoleStatus: string
{
    case PENDING  = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case REVOKED  = 'REVOKED';

    public function label(): string
    {
        return match($this) {
            self::PENDING  => 'En attente',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
            self::REVOKED  => 'Révoqué',
        };
    }
}
