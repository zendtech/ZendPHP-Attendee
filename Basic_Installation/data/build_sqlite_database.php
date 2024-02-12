<?php
// CLI USAGE: php build_sqlite_database.php ISO2_CODE DB_FILENAME [--append]
// Web Usaage: /build_sqlite_database.php?country=ISO2_CODE&dbf=DB_FILENAME&append=1
/**
 * Updates the `zipcodes` table from geonames.org data
 *
 * Before running this, do the following (change ISO2_CODE to desired country):
    export ISO2_CODE=US
    wget https://download.geonames.org/export/zip/$ISO2_CODE.zip
    unzip ISO2_CODE.zip
 * If "--append" is set, it doesn't remove the "postcodes" table
 *
*/

// grab CLI/Web params
$country  = $argv[1] ?? $_GET['country'] ?? 'US';
$db_name  = $argv[2] ?? $_GET['dbf'] ?? 'training.db';
$append   = isset($argv[3]) || isset($_GET['append']);

// sanitize
$country = strtoupper(trim(strip_tags(substr($country,0,2))));
$db_name = trim(strip_tags($db_name));

// init vars
$geo      = [];
$exp      = NULL;
$success  = FALSE;
$expected = 0;
$actual   = 0;
$blkSize  = 16384;
$txtFile  = __DIR__ . '/' . $country . '.txt';
$dbFile   = __DIR__ . '/' . $db_name;
$geoNames = 'https://download.geonames.org/export/zip/';

// fields
$fields = [
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

// set up PDO + prepared statement + files
echo "\n<pre>\n";
echo "\nProcessing " . basename(__FILE__) . "\n";
try {
    // download source file
    if (!file_exists($txtFile)) [
        $geo = new SplFileObject($txtFile, 'w');
        $src = new SplFileObject($geoNames . $country . '.zip', 'r');
        while(!$src->eof()) {
            $geo->fwrite($src->fread($blkSize));
        }
        unset($geo);
        echo shell_exec('unzip ' . $country . '.zip -o ' . $country . '.txt');
        echo PHP_EOL;
    }
    $geo = new SplFileObject($txtFile, 'r');
    // set up PDO
    $pdo = new PDO('sqlite://' . $dbFile);
    // create `postcodes` table if --append not set
    if (!$append) {
        $sql = 'DROP TABLE IF EXISTS postcodes;' . PHP_EOL;
        $sql .= 'CREATE TABLE postcodes (' . PHP_EOL;
        $sql .= '    id INTEGER PRIMARY KEY AUTOINCREMENT,' . PHP_EOL;
        foreach ($fields as $name => $type) {
            $sql .= '    ' . $name . ' ' . $type . ',' . PHP_EOL;
        }
        $sql = substr(trim($sql), 0, -1);
        $sql .= PHP_EOL . ');';
        echo $sql . PHP_EOL;
        $pdo->exec($sql);
    }
    $hdr_count = count($fields);
    // build insert
    $sql = 'INSERT INTO postcodes (';
    foreach ($fields as $key => $value) {
        if ($key === 'id') continue;
        $sql .= $key . ',';
    }
    $sql = substr($sql, 0, -1);
    $sql .= ') VALUES ('
          . substr(str_repeat('?,', $hdr_count), 0, -1)
          . ');';
    echo $sql . PHP_EOL;
    $stmt = $pdo->prepare($sql);
    while ($row = $geo->fgetcsv("\t")) {
        echo 'Processing: ' . ($row[2] ?? '') . "\n";
        $expected++;
        if (count($row) === $hdr_count) {
            $actual += (int) (bool) ($stmt->execute($row));
        }
    }
} catch (Exception $e) {
    echo $e;
}

echo ($expected == $actual) ? 'SUCCESS' : 'FAILED';
echo "\nExpected:\t$expected\n";
echo "Actual    :\t$actual\n";

// close db connection and files
unset($pdo);
unset($geo);

// data structure
/*
CREATE TABLE `zipcodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ZIPCode` varchar(5) NOT NULL DEFAULT '',
  `ZIPType` char(1) NOT NULL DEFAULT '',
  `CityName` varchar(64) NOT NULL DEFAULT '',
  `CityType` char(1) NOT NULL DEFAULT '',
  `CountyName` varchar(64) NOT NULL DEFAULT '',
  `CountyFIPS` varchar(5) NOT NULL DEFAULT '',
  `StateName` varchar(64) NOT NULL DEFAULT '',
  `StateAbbr` char(2) NOT NULL DEFAULT '',
  `StateFIPS` char(2) NOT NULL DEFAULT '',
  `MSACode` varchar(4) NOT NULL DEFAULT '',
  `AreaCode` varchar(16) NOT NULL DEFAULT '',
  `TimeZone` varchar(16) NOT NULL DEFAULT '',
  `UTC` decimal(3,1) NOT NULL DEFAULT '0.0',
  `DST` char(1) NOT NULL DEFAULT '',
  `Latitude` decimal(9,6) NOT NULL DEFAULT '0.000000',
  `Longitude` decimal(9,6) NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`id`),
  KEY `ZIPCode` (`ZIPCode`),
  KEY `ZIPCode_2` (`ZIPCode`,`Latitude`,`Longitude`)
) ENGINE=MyISAM AUTO_INCREMENT=80013 DEFAULT CHARSET=latin1;
*/

// geonames data structure:
/*
country_code      : iso country code, 2 characters
postal_code       : varchar(20)
place_name        : varchar(180)
admin_name1       : 1. order subdivision (state) varchar(100)
admin_code1       : 1. order subdivision (state) varchar(20)
admin_name2       : 2. order subdivision (county/province) varchar(100)
admin_code2       : 2. order subdivision (county/province) varchar(20)
admin_name3       : 3. order subdivision (community) varchar(100)
admin_code3       : 3. order subdivision (community) varchar(20)
latitude          : estimated latitude (wgs84)
longitude         : estimated longitude (wgs84)
accuracy          : accuracy of lat/lng from 1=estimated, 4=geonameid, 6=centroid of addresses or shape
*/
