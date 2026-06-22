<?php
declare(strict_types=1);
namespace Core;

final class EventDispatcher
{
    private array $listeners = [];
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function on(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function emit(string $event, mixed $payload = null): void
    {
        foreach ($this->listeners[$event] ?? [] as $listener) {
            $listener($payload);
        }
    }
}
