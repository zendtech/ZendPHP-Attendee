<?php
namespace Demo\Unlikely;

class PostCode
{
    const API_URL = 'https://api.unlikelysource.com/api';
    const PARAMS = [
        0 => ['city' => 'Sioux City', 'country' => 'US'],
        1 => ['city' => 'Finchampstead', 'country' => 'UK'],
    ];
    /**
     * Makes request to Unlikelysource post code lookup API
     *
     * @param array $msg : error messages
     * @param array $city_country : [city => '', country => '']
     * @return string $json : returns JSON string
     */
    public static function getHtml(array &$msg = [], array $city_country = []) : string
    {
        $city_country = (!empty($city_country)) ? $city_country : self::PARAMS[array_rand(self::PARAMS)];
        return file_get_contents(self::API_URL . '?' . http_build_query($city_country));
    }
}
