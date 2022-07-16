<?php
/*

*** Usage ***********************************************

php random.php [MAX] [SLEEP]

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
define('DATA_FILE', __DIR__ . '/US_Post_Codes.txt');
define('HEADERS', ['ISO2','PostCode','City','State','Code','Name2','Code2','Name3','Code3','Latitude','Longitude','Accuracy']);
define('URL', 'http://10.10.20.10/index.php');
define('DEFAULT_MAX', 99);
define('DEFAULT_SLEEP', 5);
// how many times?
$max = $argv[1] ?? DEFAULT_MAX;
$sleep = $argv[2] ?? DEFAULT_SLEEP;
// grab list of cities
$data  = new SplFileObject(DATA_FILE);
$list  = [];
$hdr_cnt = count(HEADERS);
while (!$data->eof()) {
    $row = $data->fgetcsv("\t");
    if (empty($row)) continue;
    if (count($row) === $hdr_cnt) {
        array_combine(HEADERS, $row);
        $city = $row['City'] ?? 'None';
        $state = $row['State'] ?? 'None';
        $key = $state . '_' . $city;
        if (!array_key_exists($key, $list)) {
            $list[$key] = $row;
        }
    }
}
// build JS list to test website

for ($x = 0; $x < $max; $x++) {
    $key = array_rand($list);
    [$state,$city] = explode('_', $key);
    $url = sprintf('%s?city=%s&state=%s', URL, $city, $state);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>ZendHQ Test</title>
<meta name="generator" content="Geany 1.36" />
</head>
<body>
<h1>ZendHQ Test</h1>
<hr />
<b>Testing:</b><p id="city"></p>
<p id="result"></p>
<script>
var list = <?php json_encode($url); ?>
</script>
</body>
</html>
