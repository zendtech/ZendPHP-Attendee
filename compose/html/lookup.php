<?php
/*

*** Usage ***********************************************

WEB: http://10.10.10.10/lookup.php?city=Xyz&state=ZZ
CLI: php lookup.php [CITY] [STATE]

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
define('FMT_STRING', '%2s|%11s|%30s|%12s|%2s|%12s|%3s|%12s|%3s|%10s|%10s|%2s');
define('HEADERS', ['ISO2','PostCode','City','State','Code','Name2','Code2','Name3','Code3','Latitude','Longitude','Accuracy']);
$find_city = function ($row, $city, array &$status) {
    if (str_contains($row[2], $city)) {
        if (count($row) === 12) {
            $status['status']++;
            $status['data'][$row[1]] = array_combine(HEADERS, $row);
        }
    }
};
$resp = ['status' => 0];
$city  = $_REQUEST['city'] ?? $argv[1] ?? '';
$state = $_REQUEST['state'] ?? $argv[2] ?? '';
$city  = trim(strip_tags($city));
$state = trim(strip_tags($state));
if (empty($city)) {
    $resp['status'] = 0;
    $resp['data'][] = 'You must specify a city';
} else {
    $data  = new SplFileObject(__DIR__ . '/../data/US_Post_Codes.txt');
    while (!$data->eof()) {
        $row = $data->fgetcsv("\t");
        if (empty($row)) continue;
        // if no state, just look by city
        if (empty($state)) {
            $find_city($row, $city, $resp);
        } else {
            if (str_contains($row[3], $state) || $row[4] === $state) {
                $find_city($row, $city, $resp);
            }
        }
    }
}
echo json_encode($resp, JSON_PRETTY_PRINT);
