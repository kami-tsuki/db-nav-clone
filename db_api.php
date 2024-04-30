<?php
include 'Models/Station.php';
require 'vendor/autoload.php';

use Models\MultipleStationData;
use Symfony\Component\Yaml\Yaml;

class db_api
{
    private $api_key;
    private $client_id;
    private $base_url;
    private $endpoints;

    public function __construct($config_file = 'db_api_data.yaml')
    {
        $config_yaml = file_get_contents($config_file);
        $this->api_key = Yaml::parse($config_yaml)['api_key'];
        $this->client_id = Yaml::parse($config_yaml)['client_id'];
        $this->base_url = Yaml::parse($config_yaml)['base_url'];
        $endpointsConfig = Yaml::parse($config_yaml)['endpoints'];
        $this->endpoints = [];
        foreach ($endpointsConfig as $key => $value) {
            $this->endpoints[$key] = $value;
        }
        echo 'API data loaded successfully' . '<br>';
    }

    private function getEndpoint($endpoint)
    {
        return $this->endpoints[$endpoint];
    }

    private function prepareRequest($endpoint, $args, $app = 'application/xml')
    {
        $url = $this->buildUrl($endpoint, $args);
        echo 'URL: ' . $url . '<br>';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                "DB-Api-Key: " . $this->api_key,
                "DB-Client-Id: " . $this->client_id,
                "Accept: " . $app,
            ],
        ]);

        return $ch;
    }

    private function buildUrl($endpoint, $args): string
    {
        $url = $this->getEndpoint($endpoint);
        foreach ($args as $arg) {
            $url = str_replace($arg[0], rawurlencode($arg[1]), $url);
        }
        return $this->base_url . $url;
    }

    /**
     * @throws Exception
     */
    private function handleResponse($response, $ch)
    {
        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code !== 200) {
            throw new Exception('HTTP error: ' . $http_code);
        }
    }

    /**
     * @throws Exception
     */
    public function getStation($station, $ep = 'station', $ak = '{pattern}'): MultipleStationData
    {
        $ch = $this->prepareRequest($ep, [[$ak, $station]], 'application/xml');
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo 'HTTP status code: ' . $http_code . '<br>';

        $info = curl_getinfo($ch);
        echo 'cURL info: ' . print_r($info, true) . '<br>';

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        echo 'Response headers: ' . $header . '<br>';

        $this->handleResponse($body, $ch);
        curl_close($ch);

        if (empty($body)) {
            throw new Exception('No response body received');
        }

        echo 'Response: ' . print_r($body, true) . '<br>';

        $xmlResponse = simplexml_load_string($body);
        if ($xmlResponse === false)
            throw new Exception('Error parsing XML');
        else
            return new Models\MultipleStationData($xmlResponse);
    }
}