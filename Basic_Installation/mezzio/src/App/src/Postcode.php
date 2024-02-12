<?php
namespace App;
/**
 * Returns database connection
 */
use PDO;
use PDOStatement;
class Postcode
{
    public const TABLE = 'postcodes';
    public const GEO_NAMES = 'https://download.geonames.org/export/zip/';

    // fields in database
    // (minus 'id' INTEGER NOT NULL AUTOINCREMENT)
    public array $fields = [
        'country_code' => 'TEXT NOT NULL',
        'postal_code' => 'TEXT NOT NULL UNIQUE',
        'city_name' => 'TEXT NOT NULL',
        'state_name' => 'TEXT',
        'state_code' => 'TEXT',
        'county_name' => 'TEXT',
        'county_code' => 'TEXT',
        'other_name' => 'TEXT',
        'other_code' => 'TEXT',
        'latitude' => 'REAL',
        'longitude' => 'REAL',
        'accuracy' => 'INT'
    ];
    public function __construct(public PDO $pdo) {}
    /**
     * Creates "postcodes" table
     * It drops table if it already exists
     *
     * @return int : $num_rows affected or false
     */
    public function createTable() : int|false
    {
        $sql = 'DROP TABLE IF EXISTS ' . static::TABLE . ';' . PHP_EOL;
        $sql .= 'CREATE TABLE ' . static::TABLE . ' (' . PHP_EOL;
        $sql .= '    id INTEGER PRIMARY KEY AUTOINCREMENT,' . PHP_EOL;
        foreach ($this->fields as $name => $type) {
            $sql .= '    ' . $name . ' ' . $type . ',' . PHP_EOL;
        }
        $sql = substr(trim($sql), 0, -1);
        $sql .= PHP_EOL . ');';
        return $this->pdo->exec($sql);
    }
    /**
     * Formulates an INSERT statement for this table
     *
     * @return PDOStatement | false if problem
     */
    public function getPreparedInsert() PDOStatement|false
    {
        $hdr_count = count($this->fields);
        $sql = 'INSERT INTO postcodes (';
        foreach ($this->fields as $key => $value) {
            if ($key === 'id') continue;
            $sql .= $key . ',';
        }
        $sql = substr($sql, 0, -1);
        $sql .= ') VALUES ('
              . substr(str_repeat('?,', $hdr_count), 0, -1)
              . ');';
        return $pdo->prepare($sql);
    }
}
