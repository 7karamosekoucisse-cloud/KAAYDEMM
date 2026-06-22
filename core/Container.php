<?php
declare(strict_types=1);
namespace Core;

use ReflectionClass;
use ReflectionNamedType;

final class Container
{
    private array $definitions = [];
    private array $instances   = [];

    public static function build(array $definitions): self
    {
        $c = new self();
        $c->definitions = $definitions;
        return $c;
    }

    public function bind(string $id, callable $factory): void
    {
        $this->definitions[$id] = $factory;
    }

    public function get(string $id): object
    {
        if (!isset($this->instances[$id])) {
            $this->instances[$id] = isset($this->definitions[$id])
                ? ($this->definitions[$id])($this)
                : $this->autowire($id);
        }
        return $this->instances[$id];
    }

    private function autowire(string $class): object
    {
        if (!class_exists($class)) {
            throw new \RuntimeException("Cannot resolve class: $class");
        }
        $rc   = new ReflectionClass($class);
        $ctor = $rc->getConstructor();
        if (!$ctor) return new $class();

        $args = [];
        foreach ($ctor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->get($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException("Cannot resolve param {$param->getName()} of $class");
            }
        }
        return new $class(...$args);
    }
}
