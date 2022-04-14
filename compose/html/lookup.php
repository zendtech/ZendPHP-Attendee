<?php
/*
Source: https://download.geonames.org/export/zip/
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
*/
define('FMT_STRING', '%2s|%11s|%30s|%12s|%2s|%12s|%3s|%12s|%3s|%10s|%10s|%2s');
$find_city = function ($row, $city) {
    $output = '';
    if (str_contains($row[2], $city))
        if (count($row) === 12)
            $output = vsprintf(FMT_STRING . PHP_EOL, $row);
    return $output;
};
$html = '';
$city  = $_REQUEST['city'] ?? '';
$state = $_REQUEST['state'] ?? '';
$city  = trim(strip_tags($city));
$state = trim(strip_tags($state));
if (empty($city)) {
    $html = '<b style="color:red;">You must specify a city</b>';
} else {
    $data  = new SplFileObject(__DIR__ . '/../data/US_Post_Codes.txt');
    while (!$data->eof()) {
        $row = $data->fgetcsv("\t");
        if (empty($row)) continue;
        // if no state, just look by city
        if (empty($state)) {
            $html .= $find_city($row, $city);
        } else {
            if (str_contains($row[3], $state) || $row[4] === $state) {
                $html .= $find_city($row, $city);
            }
        }
    }
}
$html = $html ?: '<b style="color:red;">No results</b>';
$contents = file_get_contents('lookup.html');
echo str_replace('<!-- REPLACE -->', $html, $contents);
