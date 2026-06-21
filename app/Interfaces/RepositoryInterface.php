<?php
declare(strict_types=1);
namespace App\Interfaces;

use App\Models\AbstractEntity;

interface RepositoryInterface
{
    public function find(int $id): ?AbstractEntity;
    public function findAll(int $limit = 100, int $offset = 0): array;
    public function save(AbstractEntity $entity): AbstractEntity;
    public function delete(int $id): bool;
}
