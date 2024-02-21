<?php
namespace Demo\Geonames;

use SplFileObject;
use RuntimeException;
#[App\Geonames\Random]
class Random extends Base
{
    const ERR_GEONAMES = "\nShort Geonames file doesn't exist\n"
                       . "To build the file, prceed as follows:\n"
                       . "wget " . Build::GEONAMES_URL . "\n"
                       . "unzip -o data/" . Build::GEONAMES_SHORT . "\n"
                       . "App\Geonames\Build::buildShort()\n"
                       . "App\Geonames\Build::filterByCountry('US', \$src, \$dest)\n"
                       . "\nYou need to filter by US because the (free) US weather service only provides weather for the USA\n";
    /**
     * Picks city at random
     * Returns array like this:
     * ['name' => 'City Name', 'lat' => 'latitude', 'lon' => 'longitude']
     */
    #[App\Geonames\Random\pickCity\return("?array")]
    public function __invoke()
    {
        $skip = rand(0, self::cityCount());
        $geo  = self::getGeo();
        $line = [];
        for ($x = 0; $x < $skip; $x++)
            $line = $geo->fgetcsv();
        return $line;
    }
}
