<?php
declare(strict_types=1);
namespace App\Traits;

use DateTimeImmutable;

trait Hydratable
{
    public static function fromArray(array $data): static
    {
        $obj = new static();
        foreach ($data as $key => $value) {
            $setter = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($obj, $setter)) {
                $obj->$setter($value);
            }
        }
        return $obj;
    }

    protected function parseDate(mixed $value): ?DateTimeImmutable
    {
        if ($value === null) return null;
        if ($value instanceof DateTimeImmutable) return $value;
        try { return new DateTimeImmutable((string)$value); } catch (\Throwable) { return null; }
    }
}
