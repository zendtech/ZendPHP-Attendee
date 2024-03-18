<?php
namespace Demo\Geonames;

use SplFileObject;
use RuntimeException;
class Build
{
    const GEONAMES_URL   = 'https://download.geonames.org/export/dump/';
    const GEONAMES_ZIP   = 'cities15000.zip';
    const GEONAMES_FULL  = 'cities15000.txt';
    const GEONAMES_SHORT = 'cities15000short.txt';
    const GEONAMES_FILTERED = 'cities15000filtered.txt';
    const DATA_PATH      = __DIR__ . '/../../../data/';
    public static $geo   = NULL;
    public static $count = 0;
    /**
     * Builds shorter file from GEONAMES_URL
     *
     */
    public static function buildShort()
    {
        $output = '';
        if (!file_exists(self::DATA_PATH)) {
            mkdir(self::DATA_PATH);
        }
        if (!file_exists(self::DATA_PATH . self::GEONAMES_FULL)) {
            // wget self::GEONAMES_URL
            $path = self::DATA_PATH . '/' . self::GEONAMES_ZIP;
            // build $cmd to wget GEONAMES_URL
            $cmd = 'wget ' . self::GEONAMES_URL . self::GEONAMES_ZIP;
            $output .= shell_exec($cmd);
            // build $cmd to unzip
            $cmd = 'unzip ' . self::DATA_PATH . self::GEONAMES_ZIP;
            $output .= shell_exec($cmd);
            unlink(self::DATA_PATH . self::GEONAMES_ZIP);
        }
        // open file handle
        $src = new SplFileObject(self::DATA_PATH . self::GEONAMES_FULL, 'r');
        $dest = new SplFileObject(self::DATA_PATH . self::GEONAMES_SHORT, 'w');
        // read in tab-separated line
        $output .= 'Adding: ';
        while (!$src->eof()) {
            $line = $src->fgetcsv("\t");
            if (!empty($line)) {
                // get rid of `alternatenames` column
                if (!empty($line[3])) {
                    unset($line[3]);
                    // write out to self::GEONAMES_SHORT
                    $dest->fputcsv($line);
                    $output .= $line[1] . ' | ';
                }
            }
        }
        // delete ZIP file + long file
        unset($src);
        unset($dest);
        unlink(self::DATA_PATH . self::GEONAMES_FULL);
        return $output;
    }
    /**
     * Filters short file to US only (to use free US weather service)
     *
     * @param string $iso : ISO2 code to include
     * @param string $src : source filename in DATA_PATH
     * @param string $dest : destination filename in DATA_PATH (gets overwritten)
     * @return string $output
     */
    public static function filterByCountry(string $iso2, string $src, string  $dest)
    {
        $output = '';
        // open file handle
        $src = new SplFileObject(self::DATA_PATH . $src, 'r');
        $dest = new SplFileObject(self::DATA_PATH . $dest, 'w');
        // read in tab-separated line
        $output .= "Reading: {$src->getPathname()}\nWriting: {$dest->getPathname()}\n";
        while (!$src->eof()) {
            $line = $src->fgetcsv();
            if (!empty($line[7])) {
                // fiter by `country code` column
                echo $line[1] . ' ';
                if (!empty($line[7]) && trim($line[7]) === $iso2) {
                    // write out to $dest
                    $dest->fputcsv($line);
                    $output .= $line[1] . ' | ';
                }
            }
        }
        return $output;
    }
}

/**
The main 'geoname' table has the following fields :
---------------------------------------------------
geonameid         : integer id of record in geonames database
name              : name of geographical point (utf8) varchar(200)
asciiname         : name of geographical point in plain ascii characters, varchar(200)
alternatenames    : alternatenames, comma separated, ascii names automatically transliterated, convenience attribute from alternatename table, varchar(10000)
latitude          : latitude in decimal degrees (wgs84)
longitude         : longitude in decimal degrees (wgs84)
feature class     : see http://www.geonames.org/export/codes.html, char(1)
feature code      : see http://www.geonames.org/export/codes.html, varchar(10)
country code      : ISO-3166 2-letter country code, 2 characters
cc2               : alternate country codes, comma separated, ISO-3166 2-letter country code, 200 characters
admin1 code       : fipscode (subject to change to iso code), see exceptions below, see file admin1Codes.txt for display names of this code; varchar(20)
admin2 code       : code for the second administrative division, a county in the US, see file admin2Codes.txt; varchar(80)
admin3 code       : code for third level administrative division, varchar(20)
admin4 code       : code for fourth level administrative division, varchar(20)
population        : bigint (8 byte int)
elevation         : in meters, integer
dem               : digital elevation model, srtm3 or gtopo30, average elevation of 3''x3'' (ca 90mx90m) or 30''x30'' (ca 900mx900m) area in meters, integer. srtm processed by cgiar/ciat.
timezone          : the iana timezone id (see file timeZone.txt) varchar(40)
modification date : date of last modification in yyyy-MM-dd format


AdminCodes:
Most adm1 are FIPS codes. ISO codes are used for US, CH, BE and ME. UK and Greece are using an additional level between country and fips code. The code '00' stands for general features where no specific adm1 code is defined.
The corresponding admin feature is found with the same countrycode and adminX codes and the respective feature code ADMx.
*/
