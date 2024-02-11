<?php
namespace App;
/**
 * Returns database connection
 */
use PDO;
class Connection
{
    public const DB_FILE = __DIR__ . '/../../data/training.db';
    public ?PDO $pdo = NULL;
    public function getConnection(string $dbFile = static::DB_FILE)
    {
        if (empty($this->pdo)) {
            $this->pdo = new PDO('sqlite://' . $dbFile);
        }
        return $this->pdo;
    }
}

