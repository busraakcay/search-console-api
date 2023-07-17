<?php

class Controller
{
    private $searchConsoleApi;
    private $apiRequest;
    private $mailer;

    public function __construct()
    {
        $this->searchConsoleApi = new GoogleSearchConsoleClient();
        $this->apiRequest = new ApiRequest();
        $this->mailer = new Mailer();
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
        $urls = $this->apiRequest->getUrls();
        if (isset($get['code'])) {
            $authorizationCode = $get['code'];
            $this->searchConsoleApi = new GoogleSearchConsoleClient($authorizationCode);
            $inspectedURLs = array();
            $batchSize = 5;
            $delaySeconds = 10;
            $totalUrls = count($urls);
            for ($i = 0; $i < $totalUrls; $i += $batchSize) {
                $batchUrls = array_slice($urls, $i, $batchSize);
                $batchInspectedURLs = array();
                foreach ($batchUrls as $url) {
                    $urlInspection = $this->searchConsoleApi->inspectUrl($url);
                    $inspectedURL = createInspectedURL($urlInspection);
                    if (count($inspectedURL) > 0) {
                        $inspectedURL["link"] = $url;
                        $batchInspectedURLs[] = $inspectedURL;
                    }
                    array_push($inspectedURLs, $inspectedURL);
                }
                $html = makeHtmlTemplate($batchInspectedURLs);
                $isMailSend = $this->mailer->sendEmail($html);
                if ($isMailSend) {
                    echo "Mail gönderildi.\n";
                    sleep($delaySeconds);
                } else {
                    echo "Mail gönderilirken bir hata oluştu.\n";
                }
            }
            require_once 'app/views/scanUrls.php';
        } else {
            header("Location: index.php");
            exit;
        }
    }
}
