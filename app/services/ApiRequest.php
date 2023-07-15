<?php

class ApiRequest
{
    private $url;
    private $options;

    public function __construct($url = null)
    {
        $this->url = $_ENV['BASE_URL'] . "listings/latest"; // $url
        $this->options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        );
    }

    public function get()
    {
        $curl = curl_init($this->url);
        curl_setopt_array($curl, $this->options);

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            echo "Curl error: " . $error;
        }

        curl_close($curl);
        $response = stripslashes(html_entity_decode($response));
        $response = json_decode($response, true);

        return $response["data"];
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
