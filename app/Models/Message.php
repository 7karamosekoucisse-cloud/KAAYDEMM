<?php
declare(strict_types=1);
namespace App\Models;

class Message extends AbstractEntity
{
    private int    $senderId    = 0;
    private int    $recipientId = 0;
    private string $content     = '';
    private bool   $isRead      = false;
    private ?int   $bookingId   = null;

    private ?string $senderName   = null;
    private ?string $senderAvatar = null;

    public function validate(): void {}

    public function getSenderId(): int      { return $this->senderId; }
    public function getRecipientId(): int   { return $this->recipientId; }
    public function getContent(): string    { return $this->content; }
    public function isRead(): bool          { return $this->isRead; }
    public function getBookingId(): ?int    { return $this->bookingId; }
    public function getSenderName(): ?string  { return $this->senderName; }
    public function getSenderAvatar(): ?string { return $this->senderAvatar; }

    public function setSenderId(int $v): void      { $this->senderId = $v; }
    public function setRecipientId(int $v): void   { $this->recipientId = $v; }
    public function setContent(string $v): void    { $this->content = trim($v); }
    public function setIsRead(bool $v): void       { $this->isRead = $v; }
    public function setBookingId(?int $v): void    { $this->bookingId = $v; }
    public function setSenderName(?string $v): void  { $this->senderName = $v; }
    public function setSenderAvatar(?string $v): void { $this->senderAvatar = $v; }
}
