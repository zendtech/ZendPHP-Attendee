<?php
namespace App\Geonames;

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
            $row = $geo->fgetcsv();
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
}
