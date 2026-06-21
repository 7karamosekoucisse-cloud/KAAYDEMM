<?php
declare(strict_types=1);
namespace App\Models;

class Report extends AbstractEntity
{
    private int    $reporterId  = 0;
    private int    $targetId    = 0;
    private string $targetType  = 'user';
    private string $reason      = '';
    private ?string $details    = null;
    private string $status      = 'PENDING';
    private ?int   $resolvedBy  = null;
    private ?string $resolution = null;

    private ?string $reporterName = null;
    private ?string $targetName   = null;

    public function validate(): void {}

    public function getReporterId(): int    { return $this->reporterId; }
    public function getTargetId(): int      { return $this->targetId; }
    public function getTargetType(): string { return $this->targetType; }
    public function getReason(): string     { return $this->reason; }
    public function getDetails(): ?string   { return $this->details; }
    public function getStatus(): string     { return $this->status; }
    public function getResolvedBy(): ?int   { return $this->resolvedBy; }
    public function getResolution(): ?string { return $this->resolution; }
    public function getReporterName(): ?string { return $this->reporterName; }
    public function getTargetName(): ?string   { return $this->targetName; }

    public function setReporterId(int $v): void     { $this->reporterId = $v; }
    public function setTargetId(int $v): void       { $this->targetId = $v; }
    public function setTargetType(string $v): void  { $this->targetType = $v; }
    public function setReason(string $v): void      { $this->reason = $v; }
    public function setDetails(?string $v): void    { $this->details = $v; }
    public function setStatus(string $v): void      { $this->status = $v; }
    public function setResolvedBy(?int $v): void    { $this->resolvedBy = $v; }
    public function setResolution(?string $v): void { $this->resolution = $v; }
    public function setReporterName(?string $v): void { $this->reporterName = $v; }
    public function setTargetName(?string $v): void   { $this->targetName = $v; }
}
