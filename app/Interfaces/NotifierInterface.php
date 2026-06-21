<?php
declare(strict_types=1);
namespace App\Interfaces;

use App\Models\User;

interface NotifierInterface
{
    public function notify(User $user, string $type, string $title, string $body, array $payload = [], ?string $link = null): void;
}
