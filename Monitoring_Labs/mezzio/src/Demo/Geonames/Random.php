<?php
namespace Demo\Geonames;

use SplFileObject;
use RuntimeException;
#[Demo\Geonames\Random]
class Random extends City
{
    const ERR_GEONAMES = "\nShort Geonames file doesn't exist\n"
                       . "To build the file, prceed as follows:\n"
                       . "wget " . Build::GEONAMES_URL . "\n"
                       . "unzip -o data/" . Build::GEONAMES_SHORT . "\n"
                       . "App\Geonames\Build::buildShort()\n"
                       . "App\Geonames\Build::filterByCountry('US', \$src, \$dest)\n"
                       . "\nYou need to filter by US because the (free) US weather service only provides weather for the USA\n";
    #[App\Geonames\Random\__invoke\return("?array")]
    public function __invoke(string $country)
    {
        return $this->pickCity();
    }
    /**
     * Picks city at random
     * Returns array like this:
     * ['name' => 'City Name', 'lat' => 'latitude', 'lon' => 'longitude']
     *
     * @param string $country : leave blank if you don't want to filter by country
     */
    #[App\Geonames\Random\pickCity\return("?array")]
    public function pickCity(string $country = '')
    {
        $count = $this->cityCount($country);
        $num   = rand(0, $count);
        $geo = self::getGeo();
        while (!$geo->eof()) {
            $line = $geo->fgetcsv($this->delim);
            if (!empty($country)) {
                $file_country = $line[7] ?? '';
                if ($file_country === $country) $num--;
            } else {
                $num--;
            }
            if ($num <= 0) break;
        }
        return $line;
    }
}
