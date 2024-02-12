<?php
namespace App;
/**
 * Returns database connection
 */
use PDO;
class Connection
{
    public ?PDO $pdo = NULL;
    public static function getConnection(string $dbFile = '')
    {
        if (empty($dbFile)) {
            $dbFile = BASE_DIR . '/data/training.db';
        }
        if (empty(self::$pdo)) {
            self::$pdo = new PDO('sqlite://' . $dbFile);
        }
        return self::$pdo;
    }
}

