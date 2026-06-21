<?php
declare(strict_types=1);
namespace App\Traits;

use DateTimeImmutable;

trait Timestampable
{
    protected ?DateTimeImmutable $createdAt = null;
    protected ?DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
    public function setCreatedAt(?DateTimeImmutable $d): void { $this->createdAt = $d; }
    public function setUpdatedAt(?DateTimeImmutable $d): void { $this->updatedAt = $d; }

    public function touch(): void { $this->updatedAt = new DateTimeImmutable(); }

    public function formatDate(?DateTimeImmutable $d, string $fmt = 'd/m/Y H:i'): string
    {
        return $d ? $d->format($fmt) : '—';
    }
}
