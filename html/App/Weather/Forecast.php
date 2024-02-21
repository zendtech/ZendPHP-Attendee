<?php
namespace App\Weather;
// thanks to https://weather-gov.github.io/api/
use RuntimeException;
class Forecast
{
    const API_URL = 'https://api.weather.gov/points/%3.4g,%3.4g';
    const USER_AGENT = 'User-Agent: PHP';
    const ERROR_CURL = 'ERROR: no results from cURL request';
    const ERROR_RETURN = 'ERROR: nothing returned from weather service';
    /**
     * Makes weather service forecast request
     * See: https://weather-gov.github.io/api/general-faqs
     *
     * @param float $lat : latitude
     * @param float $lon : longitude
     * @param array $msg : error messages
     * @return string|null $json : returns weather forecast as JSON string; NULL is no results
     */
    public function getForecast(float $lat, float $lon, array &$msg = []) : string|null
    {
        // make weather forecast request to self::API_URL/lat,lon
        $ch  = curl_init();
        $url = sprintf(self::API_URL, $lat, $lon);
        $options = [
            CURLOPT_RETURNTRANSFER => true,         // return web page
            CURLOPT_HEADER         => false,        // don't return headers
            CURLOPT_USERAGENT      => 'PHP',        // who am i
            CURLOPT_AUTOREFERER    => true,         // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
            CURLOPT_TIMEOUT        => 120,          // timeout on response
            CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
            CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => false,        //
            CURLOPT_VERBOSE        => 0             //
        ];
        curl_setopt_array($ch, $options);
        // retrieve forecast URL
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch) ?? '';
        if (empty($result)) {
            error_log(__METHOD__ . ':' . __LINE__ . ':' . $url);
            error_log(__METHOD__ . ':' . __LINE__ . ':' . $result);
            error_log(__METHOD__ . ':' . __LINE__ . ':' . curl_error($ch));
            $msg[] = self::ERROR_CURL . ' [' . __LINE__ . ']';
            return NULL;
        }
        // decode query return
        $data    = json_decode($result);
        $new_url = $data?->properties->forecast ?? '';
        if (empty($new_url)) {
            error_log(__METHOD__ . ':' . __LINE__ . ':' . $new_url);
            error_log(__METHOD__ . ':' . __LINE__ . ':' . var_export($data));
            error_log(__METHOD__ . ':' . __LINE__ . ':' . $result);
            $msg[] = self::ERROR_RETURN . ' [' . __LINE__ . ']';
            return NULL;
        }
        // make weather forecast request to URL
        curl_setopt($ch, CURLOPT_URL, $new_url);
        $result = curl_exec($ch);
        if (empty($result)) {
            error_log(__METHOD__ . ':' . __LINE__ . ':' . curl_error($ch));
            $result = NULL;
            $msg[] = self::ERROR_CURL . ' [' . __LINE__ . ']';
        }
        // return results
        return $result;
    }
}
