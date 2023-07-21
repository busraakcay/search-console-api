<?php

class Controller
{
    private $searchConsoleApi;
    private $mailer;

    public function __construct()
    {
        $this->searchConsoleApi = new GoogleSearchConsoleClient();
        $this->mailer = new Mailer();
    }

    public function index($get)
    {
        // $isLoggedIn = false;
        // if (isset($get['code'])) {
        $authorizationCode = $_ENV['AUTHORIZATION_CODE'];
        $isLoggedIn = true;
        $apiRequest = new ApiRequest("listings/searchconsole");
        $apiResponse = $apiRequest->get();
        // }
        require_once 'app/views/dashboard.php';
    }

    public function loginWithGoogle()
    {
        $this->searchConsoleApi->loginWithGoogle();
    }

    public function urlResults($get)
    {
        $authorizationCode = $_ENV['AUTHORIZATION_CODE'];
        $this->searchConsoleApi = new GoogleSearchConsoleClient($authorizationCode);
        $urlInspection = $this->searchConsoleApi->inspectUrl($get['link']);
        $inspectedURL = createInspectedURL($urlInspection);
        if (count($inspectedURL) > 0) {
            $inspectedURL["link"] = $get['link'];
        }
        require_once 'app/views/urlResults.php';
    }

    public function analyzeKeywords()
    {
        $apiRequest = new ApiRequest("searchconsolekeywords");
        $keywords = $apiRequest->get(false);
        /** ***** */
        // $keywords = array_slice($keywords, 0, 10);
        /** ***** */
        $date = date('Y-m-d', strtotime('-3 days'));
        $analyzedKeywords = $this->searchConsoleApi->analyzeKeywords($keywords, $date);
        $htmlTable = generateHTMLTable($analyzedKeywords);
        // echo $htmlTable;
        $isMailSend = $this->mailer->sendEmail("Kelime Analizi", $htmlTable);
        if ($isMailSend) {
            echo "Mail gönderildi.\n";
        } else {
            echo "Mail gönderilirken bir hata oluştu.\n";
        }
    }

    public function analyzeKeywordsWeekly()
    {
        $apiRequest = new ApiRequest("searchconsolekeywords");
        $keywords = $apiRequest->get(false);
        /** ***** */
        // $keywords = array_slice($keywords, 16, 7);
        /** ***** */
        // $thisWeekTable = $this->searchConsoleApi->analyzeKeywords($keywords, 7, 3);
        // $lastWeekTable = $this->searchConsoleApi->analyzeKeywords($keywords, 14, 10);
        $startDateForThisWeek = 0;
        $startDateForLastWeek = 7;
        $dateForThisWeek = date('Y-m-d', strtotime('-' . ($startDateForThisWeek + 3) . 'days'));
        $dateForLastWeek = date('Y-m-d', strtotime('-' . ($startDateForLastWeek + 3) . 'days'));
        $thisWeekTable = $this->searchConsoleApi->analyzeKeywords($keywords, $dateForThisWeek);
        $lastWeekTable = $this->searchConsoleApi->analyzeKeywords($keywords, $dateForLastWeek);

        foreach ($thisWeekTable as $key => $thisWeekRow) {
            foreach ($thisWeekRow["rows"] as $queryKey => $row) {
                foreach ($lastWeekTable as $lastWeekRow) {
                    foreach ($lastWeekRow["rows"] as $lastRow) {
                        if ($lastRow["query"] === $row["query"]) {
                            $thisWeekTable[$key]["rows"][$queryKey]["lastWeekAnalyze"] = $lastRow;
                        }
                    }
                }
            }
        }
        // require_once 'app/views/analyzeKeywordsWeekly.php';
        // die();
        $html = generateHTMLTableForWeeklyResult($thisWeekTable);
        // echo $html;
        $subject = formatDateString($dateForLastWeek) . "-" . formatDateString($dateForThisWeek) . " Haftalık Kelime Analizi";
        $isMailSend = $this->mailer->sendEmail($subject, $html);
        if ($isMailSend) {
            echo "Mail gönderildi.\n";
        } else {
            echo "Mail gönderilirken bir hata oluştu.\n";
        }
    }

    public function scanUrls()
    {
        $apiRequest = new ApiRequest("listings/searchconsole");
        $urls = $apiRequest->getUrls();
        /** ***** */
        // $urls = array_slice($urls, 0, 3);
        /** ***** */
        // if (isset($get['code'])) {
        $authorizationCode = $_ENV['AUTHORIZATION_CODE'];
        $this->searchConsoleApi = new GoogleSearchConsoleClient($authorizationCode);
        $inspectedURLs = array();
        $htmlTemplates = "";
        $batchSize = 5;
        $delaySeconds = 5;
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
            $htmlTemplates .= $html;
            sleep($delaySeconds);
        }
        $isMailSend = $this->mailer->sendEmail("URL Denetleme Sonucu", $htmlTemplates);
        if ($isMailSend) {
            echo "Mail gönderildi.\n";
        } else {
            echo "Mail gönderilirken bir hata oluştu.\n";
        }
        //  require_once 'app/views/scanUrls.php';
        // } else {
        //     header("Location: index.php");
        //     exit;
        // }
    }
}
