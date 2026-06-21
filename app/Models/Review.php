<?php
declare(strict_types=1);
namespace App\Models;

use App\Exceptions\ValidationException;

class Review extends AbstractEntity
{
    private int    $bookingId  = 0;
    private int    $authorId   = 0;
    private int    $targetId   = 0;
    private int    $rating     = 5;
    private ?string $comment   = null;
    private bool   $isPunctual = true;
    private bool   $isClean    = true;
    private bool   $isFriendly = true;

    private ?string $authorName   = null;
    private ?string $authorAvatar = null;
    private ?string $targetName   = null;

    public function validate(): void
    {
        $errors = [];
        if ($this->rating < 1 || $this->rating > 5) $errors['rating'] = 'Note entre 1 et 5';
        if (!empty($errors)) throw new ValidationException($errors);
    }

    public function getBookingId(): int   { return $this->bookingId; }
    public function getAuthorId(): int    { return $this->authorId; }
    public function getTargetId(): int    { return $this->targetId; }
    public function getRating(): int      { return $this->rating; }
    public function getComment(): ?string { return $this->comment; }
    public function isPunctual(): bool    { return $this->isPunctual; }
    public function isClean(): bool       { return $this->isClean; }
    public function isFriendly(): bool    { return $this->isFriendly; }
    public function getAuthorName(): ?string  { return $this->authorName; }
    public function getAuthorAvatar(): ?string { return $this->authorAvatar; }
    public function getTargetName(): ?string  { return $this->targetName; }

    public function setBookingId(int $v): void    { $this->bookingId = $v; }
    public function setAuthorId(int $v): void     { $this->authorId = $v; }
    public function setTargetId(int $v): void     { $this->targetId = $v; }
    public function setRating(int $v): void       { $this->rating = max(1, min(5, $v)); }
    public function setComment(?string $v): void  { $this->comment = $v; }
    public function setIsPunctual(bool $v): void  { $this->isPunctual = $v; }
    public function setIsClean(bool $v): void     { $this->isClean = $v; }
    public function setIsFriendly(bool $v): void  { $this->isFriendly = $v; }
    public function setAuthorName(?string $v): void  { $this->authorName = $v; }
    public function setAuthorAvatar(?string $v): void { $this->authorAvatar = $v; }
    public function setTargetName(?string $v): void  { $this->targetName = $v; }
}
