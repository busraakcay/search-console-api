<?php

require __DIR__ . '/../../vendor/autoload.php';

use Google\Client;
use Google\Service\Webmasters;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\InspectUrlIndexRequest;
use Google\Service\Webmasters\SearchAnalyticsQueryRequest;

class GoogleSearchConsoleClient
{
    private $client;
    private $siteUrl;

    public function __construct($authorizationCode = null)
    {
        $this->siteUrl = $_ENV['SITE_NAME'];
        $this->client = new Client();
        $this->client->setAuthConfig("credentials.json");
        $this->client->setClientId($_ENV['CLIENT_ID']);
        $this->client->setClientSecret($_ENV['CLIENT_SECRET']);
        // $this->client->setLoginHint($_ENV['E_MAIL']);
        $this->client->setApprovalPrompt('force');
        $this->client->setAccessType('offline');
        $this->client->addScope(Webmasters::WEBMASTERS_READONLY); // WEBMASTERS
        // if ($authorizationCode !== null) {
        //     $this->startSession($authorizationCode);
        // }
        $this->startSession();
    }

    public function loginWithGoogle()
    {
        $this->client->setRedirectUri($_ENV['REDIRECT_URL']);
        $authUrl = $this->client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        exit;
    }

    public function startSession()
    {
        try {
            $accessToken = $this->client->fetchAccessTokenWithRefreshToken($_ENV['REFRESH_TOKEN']);
            $this->client->setAccessToken($accessToken);
        } catch (\Exception $e) {
            $this->client->setAccessToken($_ENV['ACCESS_TOKEN']);
        }
        // $accessToken = $this->client->fetchAccessTokenWithRefreshToken($_ENV['REFRESH_TOKEN']);
        // session_start();
        // if (isset($_SESSION['access_token'])) {
        //     if ($this->isAccessTokenExpired($_SESSION['access_token'])) {
        //         if (isset($_SESSION['access_token']['refresh_token'])) {
        //             $accessToken = $this->client->fetchAccessTokenWithRefreshToken($_SESSION['access_token']['refresh_token']);
        //             $_SESSION['access_token'] = $accessToken;
        //         } else {
        //             $accessToken = $this->client->fetchAccessTokenWithAuthCode($authorizationCode);
        //             $_SESSION['access_token'] = $accessToken;
        //         }
        //     } else {
        //         $accessToken = $_SESSION['access_token'];
        //     }
        // } else {
        //     $accessToken = $this->client->fetchAccessTokenWithAuthCode($authorizationCode);
        //     $_SESSION['access_token'] = $accessToken;
        // }
    }

    private function isAccessTokenExpired($accessToken)
    {
        $expiryTimestamp = $accessToken['created'] + $accessToken['expires_in'];
        return (time() >= $expiryTimestamp);
    }

    public function getClicksLast10Days()
    {
        $service = new Webmasters($this->client);
        $searchAnalytics = $service->searchanalytics;
        $startDate = date('Y-m-d', strtotime('-10 days'));
        $endDate = date('Y-m-d');
        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate($startDate);
        $request->setEndDate($endDate);
        $request->setDimensions(['date']);
        $request->setRowLimit(10);
        $request->setAggregationType('auto');
        $response = $searchAnalytics->query($this->siteUrl, $request);
        $rows = $response->getRows();
        return $rows;
    }

    public function inspectUrl($inspectionUrl)
    {
        $service = new SearchConsole($this->client);
        $query = new InspectUrlIndexRequest();
        $query->setSiteUrl($this->siteUrl);
        $query->setInspectionUrl($inspectionUrl);
        $query->setLanguageCode("tr-TR");
        $results = $service->urlInspection_index->inspect($query);
        return $results;
    }
}
