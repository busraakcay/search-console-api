<?php

function printArray($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

function formatDateString($dateString)
{
    $date = new DateTime($dateString);
    $date->setTimezone(new DateTimeZone('UTC'));
    return $date->format('d.m.Y H:i:s');
}

function getMessagesForMobileUsability($results)
{
    $resultMessageValues = array();
    if (count($results) > 0) {
        foreach ($results as $result) {
            array_push($resultMessageValues, [
                "issueType" => $result->issueType,
                "message" => $result->message
            ]);
        }
    }
    return $resultMessageValues;
}

function getMessagesForRichResults($results)
{
    $resultMessageValues = array();
    if (count($results) > 0) {
        foreach ($results as $result) {
            array_push($resultMessageValues, [
                "richResultType" => $result->richResultType,
                "items" => getMessagesForRichItems($result->items)
            ]);
        }
    }
    return $resultMessageValues;
}

function getMessagesForRichItems($results)
{
    $resultMessageValues = array();
    if (count($results) > 0) {
        foreach ($results as $result) {
            array_push($resultMessageValues, [
                "name" => $result->name
            ]);
        }
    }
    return $resultMessageValues;
}

function getVerdictDescription($state)
{
    switch ($state) {
        case "VERDICT_UNSPECIFIED":
            return "Bilinmeyen karar.";
        case "PASS":
            return "Sayfa veya öğe geçerli.";
        case "PARTIAL":
            return "Ayırtıldı, artık kullanılmıyor.";
        case "FAIL":
            return "Sayfa veya öğe hatalı veya geçeriz.";
        case "NEUTRAL":
            return "Sayfa veya öğe hariç tutuldu.";
    }
}

function getIndexingStateDescription($state)
{
    switch ($state) {
        case "INDEXING_STATE_UNSPECIFIED":
            return "Bilinmeyen dizine ekleme durumu.";
        case "INDEXING_ALLOWED":
            return "Dizine eklenmesine izin verildi.";
        case "BLOCKED_BY_META_TAG":
            return 'Dizine eklemeye izin verilmiyor, "robots" meta etiketinde "noindex" algılandı.';
        case "BLOCKED_BY_HTTP_HEADER":
            return 'Dizine eklemeye izin verilmiyor, "X-Robots-Tag" http başlığında "noindex" algılandı.';
        case "BLOCKED_BY_ROBOTS_TXT":
            return "Ayırtıldı, artık kullanılmıyor.";
    }
}

function getPageFetchStateDescription($state)
{
    switch ($state) {
        case "PAGE_FETCH_STATE_UNSPECIFIED":
            return "Bilinmeyen getirme durumu.";
        case "SUCCESSFUL":
            return "Getirme başarılı.";
        case "SOFT_404":
            return "Soft 404.";
        case "BLOCKED_ROBOTS_TXT":
            return "Robots.txt tarafından engellendi.";
        case "NOT_FOUND":
            return "Bulunamadı (404).";
        case "ACCESS_DENIED":
            return "Yetkisiz istek (401) nedeniyle engellendi.";
        case "SERVER_ERROR":
            return "Sunucu hatası (5xx).";
        case "REDIRECT_ERROR":
            return "Yönlendirme hatası.";
        case "ACCESS_FORBIDDEN":
            return "Erişim izni verilmemesi (403) nedeniyle engellendi.";
        case "BLOCKED_4XX":
            return "Başka bir 4xx sorunu nedeniyle engellendi (403, 404 değil).";
        case "INTERNAL_CRAWL_ERROR":
            return "Dahili hata.";
        case "INVALID_URL":
            return "Geçersiz URL.";
    }
}

function getRobotTxtStateDescription($state)
{
    switch ($state) {
        case "ROBOTS_TXT_STATE_UNSPECIFIED":
            return "Sayfa getirilemedi ya da bulunamadı çünkü robots.txt dosyasına ulaşılamadı.";
        case "ALLOWED":
            return "Taramaya robots.txt tarafından izin verildi.";
        case "DISALLOWED":
            return "Tarama robots.txt tarafından engellendi.";
    }
}

function createInspectedURL($urlInspection)
{
    $inspectedURL = array();
    if ($urlInspection->inspectionResult->indexStatusResult->verdict !== null) {
        $inspectedURL["indexStatusResult"] = [
            "verdict" => $urlInspection->inspectionResult->indexStatusResult->verdict,
            "coverageState" => $urlInspection->inspectionResult->indexStatusResult->coverageState,
            "lastCrawlTime" => $urlInspection->inspectionResult->indexStatusResult->lastCrawlTime,
            "indexingState" => $urlInspection->inspectionResult->indexStatusResult->indexingState,
            "pageFetchState" => $urlInspection->inspectionResult->indexStatusResult->pageFetchState,
            "robotsTxtState" => $urlInspection->inspectionResult->indexStatusResult->robotsTxtState,
        ];
    }
    if ($urlInspection->inspectionResult->mobileUsabilityResult->verdict !== "PASS" && $urlInspection->inspectionResult->mobileUsabilityResult->verdict !==  null) {
        $inspectedURL["mobileUsabilityResult"] = [
            "verdict" => $urlInspection->inspectionResult->mobileUsabilityResult->verdict,
            "issues" => getMessagesForMobileUsability($urlInspection->inspectionResult->mobileUsabilityResult->issues)
        ];
    }
    if ($urlInspection->inspectionResult->richResultsResult->verdict !== "PASS" && $urlInspection->inspectionResult->richResultsResult->verdict !== null) {
        $inspectedURL["richResultsResult"] = [
            "verdict" => $urlInspection->inspectionResult->richResultsResult->verdict,
            "detectedItems" => getMessagesForRichResults($urlInspection->inspectionResult->richResultsResult->detectedItems)
        ];
    }
    if (count($inspectedURL) > 0) {
        $inspectedURL["searchConsoleLink"] = $urlInspection->inspectionResult->inspectionResultLink;
    }
    return $inspectedURL;
}
