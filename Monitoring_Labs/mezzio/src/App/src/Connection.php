<?php
namespace App;
/**
 * Returns database connection
 */
use PDO;
class Connection
{
    public ?PDO $pdo = NULL;
    public function __invoke(string $dbFile = '')
    {
        if (empty($dbFile)) {
            $dbFile = BASE_DIR . '/data/training.db';
        }
        if (empty($this->pdo)) {
            $this->pdo = new PDO('sqlite://' . $dbFile);
        }
        return $this->pdo;
    }
}

