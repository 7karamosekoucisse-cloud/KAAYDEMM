<?php
declare(strict_types=1);
namespace App\Repositories;

use PDO;
use Core\Database;
use App\Traits\Loggable;
use App\Models\AbstractEntity;

abstract class AbstractRepository
{
    use Loggable;

    protected PDO $pdo;
    protected string $table = '';
    protected string $modelClass = '';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->pdo();
    }

    protected function execute(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->execute($sql, $params)->fetch();
        return $row ?: null;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params)->fetchAll();
    }

    protected function hydrate(array $row): AbstractEntity
    {
        return $this->modelClass::fromArray($row);
    }

    protected function hydrateAll(array $rows): array
    {
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function find(int $id): ?AbstractEntity
    {
        $row = $this->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        return $row ? $this->hydrate($row) : null;
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        return $this->hydrateAll(
            $this->fetchAll("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?", [$limit, $offset])
        );
    }

    public function delete(int $id): bool
    {
        return $this->execute("DELETE FROM {$this->table} WHERE id = ?", [$id])->rowCount() > 0;
    }

    abstract public function save(AbstractEntity $entity): AbstractEntity;
}
