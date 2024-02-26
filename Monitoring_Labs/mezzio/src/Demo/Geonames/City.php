<?php
namespace Demo\Geonames;

use ArrayIterator;
#[App\Geonames\City]
class City extends Base
{
    /**
     * Returns iteration of city names
     */
    #[App\Geonames\City\pickCity\return("ArrayIterator")]
    public static function getNames() : ArrayIterator
    {
        $geo  = self::getGeo();
        $list = new ArrayIterator();
        while (!$geo->eof()) {
            $row = $geo->fgetcsv($this->delim);
            if (!empty($row[2])) {
                // key is city_state
                $key = $row[2] . '_' . $row[9];
                // lat/lon == cols 3 and 4
                $list->offsetSet($key, [$row[3], $row[4]]);
            }
        }
        $list->ksort();
        return $list;
    }
    /**
     * Gets count of number of cities in geonames file
     *
     * @param string $country : country code
     * @return int $count
     */
    public function cityCount(string $country = '') : int
    {
        if (self::$count === 0) {
            $geo = self::getGeo();
            while (!$geo->eof()) {
                $line = $geo->fgetcsv($this->delim);
                if (!empty($country)) {
                    $file_country = $line[7] ?? '';
                    if ($file_country === $country) self::$count++;
                } else {
                    self::$count++;
                }
            }
            self::$count--;
        }
        return self::$count;
    }
}
