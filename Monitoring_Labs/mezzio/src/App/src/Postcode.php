<?php
namespace App;
/*

*** Usage ***********************************************

WEB: http://zendphp1.local/api/query?city=Xyz&state=ZZ
CLI: php post_code_lookup.php [CITY] [STATE]
*** Source: https://download.geonames.org/export/zip/ ***

The data format is tab-delimited text in utf8 encoding, with the following fields :
country code      : iso country code, 2 characters
postal code       : varchar(20)
place name        : varchar(180)
admin name1       : 1. order subdivision (state) varchar(100)
admin code1       : 1. order subdivision (state) varchar(20)
admin name2       : 2. order subdivision (county/province) varchar(100)
admin code2       : 2. order subdivision (county/province) varchar(20)
admin name3       : 3. order subdivision (community) varchar(100)
admin code3       : 3. order subdivision (community) varchar(20)
latitude          : estimated latitude (wgs84)
longitude         : estimated longitude (wgs84)
accuracy          : accuracy of lat/lng from 1=estimated, 4=geonameid, 6=centroid of addresses or shape

**** License **************************************************************
Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are
met:

* Redistributions of source code must retain the above copyright
  notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above
  copyright notice, this list of conditions and the following disclaimer
  in the documentation and/or other materials provided with the
  distribution.
* Neither the name of the  nor the names of its
  contributors may be used to endorse or promote products derived from
  this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/
use SplFileObject;
class Postcode
{
    public const DELIM      = "\t";
    public const DATA_FILE  = BASE_DIR . '/data/US.txt';
    public const FMT_STRING = '%2s|%11s|%30s|%12s|%2s|%12s|%3s|%12s|%3s|%10s|%10s|%2s';
    public const HEADERS    = ['ISO2','PostCode','City','State','Code','Name2','Code2','Name3','Code3','Latitude','Longitude','Accuracy'];
    public const USAGE      = 'WEB: http://zendphp1.local/api/query?city=Xyz&state=ZZ';
    public function find_city( array &$resp,
                        array $row,
                        string $city,
                        string $state = '')
    {
        // check to see if city is present in $row
        if (empty($row[2])) return FALSE;
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
            if (count($row) === 12) {
                $resp['found']++;
                $resp['data'][$row[1]] = array_combine(self::HEADERS, $row);
            }
        }
    }
    public function lookup(string $city, string $state = '') : array
    {
        $resp['found'] = 0;
        if (empty($city)) {
            $resp['data']['Usage'] = self::USAGE;
        } else {
            $data  = new SplFileObject(self::DATA_FILE);
            while (!$data->eof()) {
                $row = $data->fgetcsv(self::DELIM);
                if (empty($row)) continue;
                $this->find_city($resp, $row, $city, $state);
            }
        }
        return $resp;
    }
}
