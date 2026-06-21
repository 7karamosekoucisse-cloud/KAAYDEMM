<?php
declare(strict_types=1);
namespace App\Models;

use App\Traits\{Timestampable, Hydratable};

abstract class AbstractEntity
{
    use Timestampable, Hydratable;

    protected ?int $id = null;

    public function getId(): ?int    { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    abstract public function validate(): void;

    public function isNew(): bool { return $this->id === null; }
}
