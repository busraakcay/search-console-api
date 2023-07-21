<?php

class ApiRequest
{
    private $url;
    private $options;

    public function __construct($url)
    {
        $this->url = $_ENV['API_URL'] . $url;
        $this->options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        );
    }

    public function get($isDataInclude = true)
    {
        $curl = curl_init($this->url);
        curl_setopt_array($curl, $this->options);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/html; charset=UTF-8'));

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            echo "Curl error: " . $error;
        }

        curl_close($curl);

        if ($isDataInclude) {
            $response = stripslashes(html_entity_decode($response));
            $response = json_decode($response, true);
            return $response["data"];
        } else
            return  json_decode($response, true);
    }

    public function getUrls()
    {
        $data = $this->get();
        $urlArray = array();
        foreach ($data as $item) {
            array_push($urlArray, $item["link"]);
        }
        return $urlArray;
    }
}
