<?php
namespace App;
/**
 * Uses both a postcodes text file and an SQLite database
 * When you call lookup():
 * 1. First does a  database lookup
 * 2. If not found, does a lookup from the text file
 * 3. If a postcode is not in the database, a row is added and access is set to 1
 * 4. If a postcode exists in the database, access is incremented +1
 *
 */
use PDO;
use PDOStatement;
use SplFileObject;
class Postcode
{
    public const DELIM      = "\t";
    public const TABLE      = 'postcodes';
    public const DATA_DIR   = BASE_DIR . '/data/';
    public const DATA_FILE  = BASE_DIR . 'US.txt';
    public const DB_FN      = BASE_DIR . 'training.db';
    public const FMT_STRING = '%2s|%11s|%30s|%12s|%2s|%12s|%3s|%12s|%3s|%10s|%10s|%2s';
    public const HEADERS    = ['ISO2','PostCode','City','State','Code','Name2','Code2','Name3','Code3','Latitude','Longitude','Accuracy','Access'];
    public const USAGE      = 'WEB: http://zendphp1.local/api/query?city=Xyz&state=ZZ';
    public $sql = '';
    public ?PDO $pdo = NULL;
    public $postcode_fields = [
        'country_code' => 'TEXT NOT NULL',
        'postal_code' => 'TEXT NOT NULL PRIMARY KEY',
        'city_name' => 'TEXT NOT NULL',
        'state_name' => 'TEXT',
        'state_code' => 'TEXT',
        'county_name' => 'TEXT',
        'county_code' => 'TEXT',
        'other_name' => 'TEXT',
        'other_code' => 'TEXT',
        'latitude' => 'REAL',
        'longitude' => 'REAL',
        'accuracy' => 'INT',
        'access' => 'INT',
    ];
    public function __construct(string $dbFile = self::DATA_DIR . self::DB_FN)
    {
        $this->pdo = $this->getConnection();
    }
   /**
     * Performs a city/state lookup
     *
     * @param string $city
     * @param string $state
     * @return array
     */
    public function lookup(string $city, string $state = '') : array
    {
        $resp['found'] = 0;
        if (!$this->lookup_from_db($resp, $city, $state)) {
            $this->lookup_from_file($resp, $city, $state);
        }
        // update/insert results in database
        if ($resp['found'] > 0) {
            $select = $this->getSelectStmt();
            $update = $this->getUpdateStmt();
            $insert = $this->getInsertStmt();
            foreach ($resp['data'] as $postcode => $row) {
                $res = $select->execute([$postcode]);
                if (empty($res)) {
                    $insert->execute($row);
                } else {
                    $row = $res->fetch(PDO::FETCH_ASSOC);
                    $access = (int) ($row['access'] ?? 0);
                    $update->execute([++$access, $postcode]);
                }
            }
        }
        return $resp;
    }
     /**
     * Performs a city/state lookup from the Geonames file
     *
     * @param array $resp
     * @param string $city
     * @param string $state
     * @return array
     */
    public function lookup_from_file(array &$resp, string $city, string $state = '') : array
    {
        $resp['found'] = 0;
        if (empty($city)) {
            $resp['data']['Usage'] = self::USAGE;
        } else {
            $obj = new SplFileObject(self::DATA_DIR . self::DATA_FILE);
            while (!$obj->eof()) {
                $row = $obj->fgetcsv(self::DELIM);
                if (empty($row)) continue;
                $arr = [];
                $ok = FALSE;
                if (empty($state)) {
                    $ok = TRUE;
                } else {
                    $name = $row[3] ?? '';
                    $code = $row[4] ?? '';
                    if ($name === $state) $ok = TRUE;
                    if ($code === $state) $ok = TRUE;
                }
                if ($ok && stripos($row[2], $city) !== FALSE) {
                    if (!empty($row)) {
                        $pos = 0;
                        foreach (self::HEADERS as $key) {
                            $arr[$key] = $row++;
                        }
                        $resp['found']++;
                        $resp['data'][$row[1]] = $arr;
                    }
                }
            }
        }
        return (bool) $resp['found'];
    }
    /**
     * Returns a row from the postcodes file at random
     *
     * @return array
     */
    public function getRandomPostcode() : array
    {
        $row = [];
        // chooses a city/state at random from the postcodes file
        $obj = new SplFileObject(self::DATA_DIR . self::DATA_FILE);
        $num = 0;
        // first get the # rows
        while ($row = $obj->fgets()) $num++;
        // now pick one at random
        $obj->rewind();
        $pos = rand(0, $num);
        do {
            $row = $obj->fgetcsv(self::DELIM);
        } while (--$pos > 0 && !$obj->eof());
        return $row;
    }
    /**
     * Performs a city/state lookup from the Geonames file
     *
     * @param array $resp
     * @param string $city
     * @param string $state
     * @return array
     */
    public function lookup_from_db(array &$resp, string $city, string $state = '') : array
    {
        $resp['found'] = 0;
        $sql = 'SELECT * FROM ' . self::TABLE . ' '
             . 'WHERE city = ?';
        if (empty($state)) {
            $stmt = $this->pdo->prepare($sql);
            $data = [$city];
        } else {
            $sql .= ' AND ';
            if (strlen(trim($state)) > 2) {
                $sql .= 'state_name = ?;';
            } else {
                $sql .= 'state_code = ?;';
            }
            $data = [$city, $state];
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $resp['found']++;
            $resp['data'][$row[1]] = $row;
        }
        return (bool) $resp['found'];
    }
    /**
     * Returns a PDO connection
     *
     * @return PDO
     * @throws ERROR
     */
    public function getConnection(string $dbFile)
    {
        $dbFile ??= self::DATA_DIR . self::DB_FN;
        if (empty($this->pdo)) {
            $this->pdo = new PDO('sqlite://' . $dbFile);
        }
        return $this->pdo;
    }
    /**
     * Creates an SQLite database file to match postcodes
     *
     * @param string $dbFile : name of the database file
     * @return int | FALSE : returns # rows affected or bool FALSE
     */
    public function buildPostCodesTable()
    {
        // set up PDO
        $sql = 'DROP TABLE IF EXISTS ' . self::TABLE . ';' . PHP_EOL;
        $sql .= 'CREATE TABLE postcodes (' . PHP_EOL;
        $sql .= '    id INTEGER PRIMARY KEY AUTOINCREMENT,' . PHP_EOL;
        foreach ($this->postcode_fields as $name => $type) {
            $sql .= '    ' . $name . ' ' . $type . ',' . PHP_EOL;
        }
        $sql = substr(trim($sql), 0, -1);
        $sql .= PHP_EOL . ');';
        $this->sql = $sql;
        return $this->pdo->exec($sql);
    }
    /**
     * Selects based on postcode
     *
     * @return PDOStatement | FALSE
     */
    public function getSelectStmt() : PDOStatement|bool
    {
        $sql = 'SELECT * FROM ' . self::TABLE . ' '
             . 'WHERE postal_code = ?';
        return $pdo->prepare($sql);
    }
    /**
     * Updates the 'access' field
     *
     * @return PDOStatement | FALSE
     */
    public function getUpdateStmt() : PDOStatement|bool
    {
        $sql = 'UPDATE ' . self::TABLE . ' '
             . 'SET access = ? '
             . 'WHERE postal_code = ?';
        return $pdo->prepare($sql);
    }
    /**
     * Locates data by
     *
     * @return PDOStatement | FALSE
     */
    public function getInsertStmt() : PDOStatement|bool
    {
        // deal with the access count
        $data['access'] = 1;
        // build insert
        $sql = 'INSERT INTO postcodes (';
        foreach ($this->fields as $key => $value) {
            if ($key === 'id') continue;
            if (!empty($data[$key])) {
                $sql .= $key . ',';
            }
        }
        $sql = substr($sql, 0, -1);
        $sql .= ') VALUES (';
        foreach ($this->fields as $key => $value) {
            if ($key === 'id') continue;
            if (!empty($data[$key])) {
                $sql .= ':' . $key . ',';
            }
        }
        $sql = substr($sql, 0, -1) . ');';
        return $pdo->prepare($sql);
    }
}
