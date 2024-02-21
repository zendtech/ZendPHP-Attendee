<?php
spl_autoload_register(function ($class) {
    require __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
});
use App\Ntp\Client;
use App\Lorem\Ipsum;
use App\Number\Prime;
use App\Weather\Forecast;
use App\Geonames\{Random,Build};

// NTP request
function ntp()
{
    $output = "NTP Time:\n";
    $output .= var_export((new Client())(), TRUE);
    return $output . PHP_EOL;
}
function ipsum()
{
    $output = "Lorem Ipsum:\n";
    $contents = (new Ipsum())();
    preg_match_all('!<p>(.*?)</p>!', $contents, $matches);
    $output .= $matches[1][0] ?? 'Unknown';
    return $output . PHP_EOL;
}
function prime()
{
    $output = '';
    $start = $argv[2] ?? 9000;
    $end   = $argv[3] ?? 9999;
    $primes = (new Prime())((int) $start, (int) $end);
    foreach ($primes as $number) $output .= $number . ' ';
    return $output . PHP_EOL;
}
function city(array &$city = [])
{
    // Pick random city
    $output = '';
    $city = (new Random())();
    $output .= "Random City Info:\n";
    $output .= var_export($city, TRUE);
    return $output;
}
function weather()
{
    // Pick random city
    $output = '';
    $city   = [];
    $output .= city($city);
    // Weather Forecast for Random City
    if (!empty($city[2])) {
        $name = $city[2];
        $lat  = $city[3];
        $lon  = $city[4];
        $output .= "Weather forecast for $name\n";
        $output .= (new Forecast())->getForecast($lat, $lon);
    }
    return $output . PHP_EOL;
}
