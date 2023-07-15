<?php

class Controller
{
    private $searchConsoleApi;
    private $apiRequest;

    public function __construct()
    {
        $this->searchConsoleApi = new GoogleSearchConsoleClient();
        $this->apiRequest = new ApiRequest();
    }

    public function index($get)
    {
        $isLoggedIn = false;
        if (isset($get['code'])) {
            $authorizationCode = $get['code'];
            $isLoggedIn = true;
            $apiResponse = $this->apiRequest->get();
        }
        require_once 'app/views/dashboard.php';
    }

    public function loginWithGoogle()
    {
        $this->searchConsoleApi->loginWithGoogle();
    }

    public function urlResults($get)
    {
        $authorizationCode = $get['code'];
        $this->searchConsoleApi = new GoogleSearchConsoleClient($authorizationCode);
        $urlInspection = $this->searchConsoleApi->inspectUrl($get['link']);
        $inspectedURL = createInspectedURL($urlInspection);
        if (count($inspectedURL) > 0) {
            $inspectedURL["link"] = $get['link'];
        }
        require_once 'app/views/urlResults.php';
    }

    public function scanUrls($get)
    {
        // $urls = $this->apiRequest->getUrls();
        $urls = [
            "https://makinecim.com/ısıtma.soğutma.kalorifer.kazan.doğalgaz.fueloil.motorin.sıvı.yakıt..tesisat.baymak.buderus.demirdöküm.radyatör.?CityId=Kayıt+Yok/No+Record",
            "https://makinecim.com/tr/4-kademe-85-santim-pistonlar-5-kademe-85-santim-pistonlar-6-kademe-85-santim-pistonlar-hidrolik/319195/ilan",
            "https://makinecim.com/tr/sanayi-tipi-buzdolaplari-satilik-2el-sifir-sanayi-tipi-buzdolaplari-fiyatlari/81/sc",
            "https://makinecim.com/et-kiyma-makinasi?CityId=Konya",
            "https://makinecim.com/tr/mermer-kapma-aparati/394829/ilan",
            "https://makinecim.com/tr/mermer-kapma-kaldirma-aparati/394796/ilan",
        ];

        if (isset($get['code'])) {
            $authorizationCode = $get['code'];
            $this->searchConsoleApi = new GoogleSearchConsoleClient($authorizationCode);
            $inspectedURLs = array();
            foreach ($urls as $url) {
                $urlInspection = $this->searchConsoleApi->inspectUrl($url);
                $inspectedURL = createInspectedURL($urlInspection);
                if (count($inspectedURL) > 0) {
                    $inspectedURL["link"] = $url;
                    array_push($inspectedURLs, $inspectedURL);
                }
            }
        } else {
            header("Location: index.php");
            exit;
        }
        require_once 'app/views/scanUrls.php';
    }
}
