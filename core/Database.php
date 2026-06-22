<?php
declare(strict_types=1);
namespace Core;

use PDO;
use PDOException;

final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct(array $cfg)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
        );
        $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
    }

    public static function getInstance(array $cfg = []): self
    {
        if (self::$instance === null) {
            if (empty($cfg)) {
                $cfg = require dirname(__DIR__) . '/config/config.php';
                $cfg = $cfg['db'];
            }
            self::$instance = new self($cfg);
        }
        return self::$instance;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function beginTransaction(): void   { $this->pdo->beginTransaction(); }
    public function commit(): void             { $this->pdo->commit(); }
    public function rollBack(): void           { $this->pdo->rollBack(); }
    public function lastInsertId(): string     { return $this->pdo->lastInsertId(); }
}
