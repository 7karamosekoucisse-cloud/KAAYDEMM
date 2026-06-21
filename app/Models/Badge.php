<?php
declare(strict_types=1);
namespace App\Models;

class Badge extends AbstractEntity
{
    private int    $userId     = 0;
    private string $code       = '';
    private string $name       = '';
    private string $icon       = '';
    private string $description = '';

    public function validate(): void {}

    public function getUserId(): int        { return $this->userId; }
    public function getCode(): string       { return $this->code; }
    public function getName(): string       { return $this->name; }
    public function getIcon(): string       { return $this->icon; }
    public function getDescription(): string { return $this->description; }

    public function setUserId(int $v): void        { $this->userId = $v; }
    public function setCode(string $v): void       { $this->code = $v; }
    public function setName(string $v): void       { $this->name = $v; }
    public function setIcon(string $v): void       { $this->icon = $v; }
    public function setDescription(string $v): void { $this->description = $v; }
}
