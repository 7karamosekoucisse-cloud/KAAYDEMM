<?php
declare(strict_types=1);
namespace App\Models;

class Notification extends AbstractEntity
{
    private int    $userId  = 0;
    private string $type    = '';
    private string $title   = '';
    private string $body    = '';
    private ?string $link   = null;
    private array  $payload = [];
    private bool   $isRead  = false;

    public function validate(): void {}

    public function getUserId(): int    { return $this->userId; }
    public function getType(): string   { return $this->type; }
    public function getTitle(): string  { return $this->title; }
    public function getBody(): string   { return $this->body; }
    public function getLink(): ?string  { return $this->link; }
    public function getPayload(): array { return $this->payload; }
    public function isRead(): bool      { return $this->isRead; }

    public function setUserId(int $v): void    { $this->userId = $v; }
    public function setType(string $v): void   { $this->type = $v; }
    public function setTitle(string $v): void  { $this->title = $v; }
    public function setBody(string $v): void   { $this->body = $v; }
    public function setLink(?string $v): void  { $this->link = $v; }
    public function setPayload(array $v): void { $this->payload = $v; }
    public function setIsRead(bool $v): void   { $this->isRead = $v; }
}
