<?php
namespace Postcode;

use SplFileObject;
use App\Postcode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;

class BuildHandler implements RequestHandlerInterface
{
    public function __construct(public TemplateRendererInterface $renderer,
                                public Postcode $postcode,
                                public string $dataDir)
    {}

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        // get request params
        $params   = $request->getQueryParams();
        $country  = $params['country'] ?? 'US';
        $append   = isset($params['append']);
        // sanitize params
        $country = strtoupper(trim(strip_tags(substr($country,0,2))));
        // init vars
        $geo      = [];
        $exp      = NULL;
        $success  = FALSE;
        $expected = 0;
        $actual   = 0;
        $blkSize  = 16384;
        $txtFile  = str_replace('//', '/', $this->dataDir . '/' . $country . '.txt');
        // set up PDO + prepared statement + files
        $output   = '';
        $output .= "\n<pre>\n";
        try {
            // download source file
            if (!file_exists($txtFile)) {
                $zipFile = str_replace('//', '/', $this->dataDir . '/' . $country . '.zip');
                $srcUrl  = str_replace('//', '/', Postcode::GEO_NAMES . '/' . $country . '.zip');
                $geo = new SplFileObject($txtFile, 'w');
                $src = fopen($srcUrl, 'r');
                while(!eof($src)) {
                    $geo->fwrite(fread($src, $blkSize));
                }
                unset($geo);
                fclose($src);
                $output .= shell_exec('unzip ' . $zipFIle . ' -o ' . $txtFile);
                $output .= PHP_EOL;
            }
            $geo = new SplFileObject($txtFile, 'r');
            // create `postcodes` table if --append not set
            if (!$append) {
                $output .= 'Building table: ' . Postcode::TABLE . PHP_EOL;
                if ($this->postcode->createTable()) {
                    $output .= 'SUCCESS: created table ' . Postcode::TABLE . PHP_EOL;
                } else {
                    $output .= 'ERROR: unable to create table ' . Postcode::TABLE . PHP_EOL;
                }
            } else {
                $output .= 'Appending to table: ' . Postcode::TABLE . PHP_EOL;
            }
            $hdr_count = count($this->postcode->fields);
            // build insert
            $insStmt  = $this->postcode->getPreparedInsert();
            // insert data from download file
            while ($row = $geo->fgetcsv("\t")) {
                $output .= 'Processing: ' . ($row[2] ?? '') . "\n";
                $expected++;
                if (count($row) === $hdr_count) {
                    $actual += (int) (bool) ($insStmt->execute($row));
                }
            }
        } catch (Exception $e) {
            $output .= $e;
        }

        $output .= ($expected == $actual) ? 'SUCCESS' : 'FAILED';
        $output .= "\nExpected:\t$expected\n";
        $output .= "Actual    :\t$actual\n";
        $output .= "\n</pre>\n";
        // close db connection and files
        unset($pdo);
        unset($geo);
        // Render and return a response:
        return new HtmlResponse($this->renderer->render(
            'postcode::build',
            ['output' => $output] // parameters to pass to template
        ));
    }
}

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
