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
    return $date->format('d.m.Y');
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

function makeHtmlTemplate($inspectedURLs)
{
    $html = '<div style="padding: 20px; font-family: Helvetica, Arial, sans-serif;">';
    foreach ($inspectedURLs as $inspectedURL) {
        $html .= '<div>
        <h4 style="padding-bottom: 16px;">Index Durumu <small>[' . $inspectedURL["indexStatusResult"]["verdict"] . ']</small></h4>
        <small><b>' . $inspectedURL["link"] . '</b></small>
        <br><br>
        <p><b>URL Durumu: </b>' . $inspectedURL["indexStatusResult"]["coverageState"] . '</p>
        <p><b>Index Analizi: </b>' . getVerdictDescription($inspectedURL["indexStatusResult"]["verdict"]) . '</p>
        <p><b>Son Taranma Tarihi: </b>' . formatDateString($inspectedURL["indexStatusResult"]["lastCrawlTime"]) . '</p>
        <p><b>Index Durumu: </b>' . getIndexingStateDescription($inspectedURL["indexStatusResult"]["indexingState"]) . '</p>
        <p><b>Sayfa Getirme Durumu: </b>' . getPageFetchStateDescription($inspectedURL["indexStatusResult"]["pageFetchState"]) . '</p>
        <p><b>Robots.txt Kural Durumu: </b>' . getRobotTxtStateDescription($inspectedURL["indexStatusResult"]["robotsTxtState"]) . '</p>
        <p>Makinecim.com\'da ilanı görmek için <a href="' . $inspectedURL["link"] . '">tıklayınız</a></p>
        <p>Bu URL\'i search console\'da denetlemek için <a href="' . $inspectedURL["searchConsoleLink"] . '">tıklayınız</a></p>';

        if (isset($inspectedURL["mobileUsabilityResult"])) {
            $html .= '<br>
            <h4 style="padding-bottom: 16px;">Mobil Kullanılabilirlik <small>[' . $inspectedURL["mobileUsabilityResult"]["verdict"] . ']</small></h4>';

            foreach ($inspectedURL["mobileUsabilityResult"]["issues"] as $key => $issue) {
                $html .= '<p><b>[Sorun ' . ($key + 1) . ']</b> ' . $issue["message"] . ' [' . $issue["issueType"] . ']</p>';
            }
        }
        if (isset($inspectedURL["richResultsResult"])) {
            $html .= '<br>
            <h4 style="padding-bottom: 16px;">Zengin Sonuçlar <small>[' . $inspectedURL["richResultsResult"]["verdict"] . ']</small></h4>';

            foreach ($inspectedURL["richResultsResult"]["detectedItems"] as $key => $detectedItems) {
                $html .= '<p><b>[Sorun ' . ($key + 1) . '] </b>' . $detectedItems["richResultType"] . '</p>
                <ul class="ml-2">';

                foreach ($detectedItems["items"] as $itemKey => $item) {
                    $html .= '<li><b>[Öğe ' . ($itemKey + 1) . '] </b>' . $item["name"] . '</li>';
                }

                $html .= '</ul>';
            }
        }
        $html .= '</div><hr>';
    }
    $html .= '</div><br>';
    return $html;
}

function generateHTMLTable($data)
{
    $html = '';
    foreach ($data as $item) {
        $html .= '<table style="width: 100%; overflow: hidden; margin-bottom: 20px; font-family: Helvetica, Arial, sans-serif;">';
        $html .= '<tr style="padding: 8px; text-align: left;">';
        $html .= '<th style="padding: 8px; text-align: left; background-color: #f2f2f2; font-size: 12px;" colspan="5">' . $item['keyword'] . '</th></tr>';
        $html .= '<tr><th style="padding: 8px; text-align: left; background-color: #f2f2f2; font-size: 11px;">query</th>';
        $html .= '<th style="padding: 8px; text-align: left; background-color: #f2f2f2; font-size: 11px;">cl</th>';
        $html .= '<th style="padding: 8px; text-align: left; background-color: #f2f2f2; font-size: 11px;">ctr</th>';
        $html .= '<th style="padding: 8px; text-align: left; background-color: #f2f2f2; font-size: 11px;">imp</th>';
        $html .= '<th style="padding: 8px; text-align: left; background-color: #f2f2f2; font-size: 11px;">pos</th></tr>';
        if (count($item['rows']) > 0) {
            foreach ($item['rows'] as $row) {
                $html .= '<tr>';
                $html .= '<td style="padding: 8px; text-align: left; font-size: 11px;">' . $row['query'] . '</td>';
                $html .= '<td style="padding: 8px; text-align: left; font-size: 11px;">' . $row['clicks'] . '</td>';
                $html .= '<td style="padding: 8px; text-align: left; font-size: 11px;">' . number_format($row['ctr'], 2) . '</td>';
                $html .= '<td style="padding: 8px; text-align: left; font-size: 11px;">' . $row['impressions'] . '</td>';
                $html .= '<td style="padding: 8px; text-align: left; font-size: 11px;">' . number_format($row['position'], 2) . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="5" style="padding: 8px; text-align: center; color:#858585; font-size: 11px;">Herhangi bir sonuç bulunamadı.</td></tr>';
        }
        $html .= '</table>';
    }
    $html .= '<br />';
    return $html;
}

function calculateValues($data, $key, $hasLast = false)
{
    $total = 0;
    if (isset($data["rows"])) {
        foreach ($data["rows"] as $row) {
            if ($hasLast) {
                $total += $row["lastWeekAnalyze"][$key];
            } else {
                $total += $row[$key];
            }
        }
    }
    return number_format($total, 2);
}

function generateHTMLTableForWeeklyResult($data)
{
    $emptySpan = '<span style="margin:2px;"><small></small></span>';
    $html = '<table style="width: 100%; font-size:12px; font-family: Helvetica, Arial, sans-serif;">
    <tr>
        <td colspan="2" style="width: 100%;">
        <th style="padding: 8px 16px; text-align: center; background-color: #f2f2f2;">click</th>
        <th style="padding: 8px 16px; text-align: center; background-color: #f2f2f2;">ctr</th>
        <th style="padding: 8px 16px; text-align: center; background-color: #f2f2f2;">imp</th>
        <th style="padding: 8px 16px; text-align: center; background-color: #f2f2f2;">pos</th>
    </tr>';
    foreach ($data as $item) {
        $numberOfRowsForLastWeek = 0;
        $numberOfRowsForThisWeek = 0;
        if (count($item['rows']) > 1) {
            foreach ($item['rows'] as $row) {
                if (isset($row['lastWeekAnalyze'])) {
                    $numberOfRowsForLastWeek += 1;
                } else {
                    if ($numberOfRowsForLastWeek == 0) {
                        $numberOfRowsForLastWeek = 1;
                    }
                }
                $numberOfRowsForThisWeek += 1;
            }
        } else {
            $numberOfRowsForLastWeek = 1;
            $numberOfRowsForThisWeek = 1;
        }
        $formatnumberOfRowsForThisWeek = number_format($numberOfRowsForThisWeek, 2);
        $formatNumberOfRowsForLastWeek = number_format($numberOfRowsForLastWeek, 2);

        $calculateClickValues = number_format(calculateValues($item, "clicks"), 2);
        $calculateLastClickValues = number_format(calculateValues($item, "clicks", true), 2);

        $calculateCtrValues = number_format(calculateValues($item, "ctr") / $formatnumberOfRowsForThisWeek, 2);
        $calculateLastCtrValues = number_format(calculateValues($item, "ctr", true) / $formatNumberOfRowsForLastWeek, 2);

        $calculateImpValues = number_format(calculateValues($item, "impressions"), 2);
        $calculateLastImpValues = number_format(calculateValues($item, "impressions", true), 2);

        $calculatePosValues = number_format(calculateValues($item, "position") / $formatnumberOfRowsForThisWeek, 2);
        $calculateLastPosValues = number_format(calculateValues($item, "position", true) / $formatNumberOfRowsForLastWeek, 2);

        $compareSumClicks = $calculateClickValues - $calculateLastClickValues;
        $compareSumCtr = $calculateCtrValues - $calculateLastCtrValues;
        $compareSumImp = $calculateImpValues - $calculateLastImpValues;
        $compareSumPos = $calculatePosValues - $calculateLastPosValues;

        if ($compareSumClicks > 0) {
            $compareSumClicksSpan = '<span style="color: green; margin-right:2px; font-size:10px;"><small>▲</small></span>';
        } elseif ($compareSumClicks < 0) {
            $compareSumClicksSpan = '<span style="color: red; margin-right:2px; font-size:10px;"><small>▼</small></span>';
        } else {
            $compareSumClicksSpan = '<span style="color: orange; margin-right:2px; font-size:14px">•</span>';
        }

        if ($compareSumCtr > 0) {
            $compareSumCtrSpan = '<span style="color: green; margin-right:2px; font-size:10px;"><small>▲</small></span>';
        } elseif ($compareSumCtr < 0) {
            $compareSumCtrSpan = '<span style="color: red; margin-right:2px; font-size:10px;"><small>▼</small></span>';
        } else {
            $compareSumCtrSpan = '<span style="color: orange; margin-right:2px; font-size:14px">•</span>';
        }

        if ($compareSumImp > 0) {
            $compareSumImpSpan = '<span style="color: green; margin-right:2px; font-size:10px;"><small>▲</small></span>';
        } elseif ($compareSumImp < 0) {
            $compareSumImpSpan = '<span style="color: red; margin-right:2px; font-size:10px;"><small>▼</small></span>';
        } else {
            $compareSumImpSpan = '<span style="color: orange; margin-right:2px; font-size:14px">•</span>';
        }

        if ($compareSumPos < 0) {
            $compareSumPosSpan = '<span style="color: green; margin-right:2px; font-size:10px;"><small>▲</small></span>';
        } elseif ($compareSumPos > 0) {
            if ($calculateLastPosValues == 0) {
                $compareSumPosSpan = '<span style="color: green; margin-right:2px; font-size:10px;"><small>▲</small></span>';
            } else {
                $compareSumPosSpan = '<span style="color: red; margin-right:2px; font-size:10px;"><small>▼</small></span>';
            }
        } else {
            $compareSumPosSpan = '<span style="color: orange; margin-right:2px; font-size:14px">•</span>';
        }

        $html .= '
            <tr>
                <th colspan="2" style="width: 100%; padding: 8px 16px; text-align: left; background-color: #f2f2f2;">' . $item['keyword'] . '</th>
                <th style="text-align: center; background-color: #f2f2f2;">
                    <table>
                        <tr>
                            <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $emptySpan . $calculateLastClickValues . '</td>
                            <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $compareSumClicksSpan . $calculateClickValues . '</td>
                        </tr>
                    </table>
                </th>
                <th style="text-align: center; background-color: #f2f2f2;">
                    <table>
                        <tr>
                            <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $emptySpan . $calculateLastCtrValues . '</td>
                            <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $compareSumCtrSpan . $calculateCtrValues . '</td>
                        </tr>
                    </table>
                </th>
                <th style="text-align: center; background-color: #f2f2f2;">
                    <table>
                        <tr>
                            <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $emptySpan . $calculateLastImpValues . '</td>
                            <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $compareSumImpSpan . $calculateImpValues . '</td>
                        </tr>
                    </table>
                </th>
                <th style="text-align: center; background-color: #f2f2f2;">
                    <table>
                        <tr>
                            <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $emptySpan . $calculateLastPosValues . '</td>
                            <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $compareSumPosSpan . $calculatePosValues . '</td>
                        </tr>
                    </table>
                </th>
            </tr>';
        if (count($item['rows']) > 0) {
            foreach ($item['rows'] as $row) {
                if (isset($row['lastWeekAnalyze'])) {
                    $compareClicks = $row['clicks'] - $row['lastWeekAnalyze']['clicks'];
                    $compareCtr = $row['ctr'] - $row['lastWeekAnalyze']['ctr'];
                    $compareImp = $row['impressions'] - $row['lastWeekAnalyze']['impressions'];
                    $comparePos = $row['position'] - $row['lastWeekAnalyze']['position'];


                    $formatLastCl = number_format($row['lastWeekAnalyze']['clicks'], 2);

                    $formatLastCtr = number_format($row['lastWeekAnalyze']['ctr'], 2);

                    $formatLastImp = number_format($row['lastWeekAnalyze']['impressions'], 2);

                    $formatLastPos = number_format($row['lastWeekAnalyze']['position'], 2);


                    if ($compareClicks > 0) {
                        $compareClicksSpan = '<span style="color: green; margin-right:2px; font-size:10px;"><small>▲</small></span>';
                    } elseif ($compareClicks < 0) {
                        $compareClicksSpan = '<span style="color: red; margin-right:2px; font-size:10px;"><small>▼</small></span>';
                    } else {
                        $compareClicksSpan = '<span style="color: orange; margin-right:2px; font-size:14px">•</span>';
                    }

                    if ($compareCtr > 0) {
                        $compareCtrSpan = '<span style="color: green; margin-right:2px; font-size:10px;"><small>▲</small></span>';
                    } elseif ($compareCtr < 0) {
                        $compareCtrSpan = '<span style="color: red; margin-right:2px; font-size:10px;"><small>▼</small></span>';
                    } else {
                        $compareCtrSpan = '<span style="color: orange; margin-right:2px; font-size:14px">•</span>';
                    }

                    if ($compareImp > 0) {
                        $compareImpSpan = '<span style="color: green; margin-right:2px; font-size:10px;"><small>▲</small></span>';
                    } elseif ($compareImp < 0) {
                        $compareImpSpan = '<span style="color: red; margin-right:2px; font-size:10px;"><small>▼</small></span>';
                    } else {
                        $compareImpSpan = '<span style="color: orange; margin-right:2px; font-size:14px">•</span>';
                    }

                    if ($comparePos < 0) {
                        $comparePosSpan = '<span style="color: green; margin-right:2px; font-size:10px;"><small>▲</small></span>';
                    } elseif ($comparePos > 0) {
                        $comparePosSpan = '<span style="color: red; margin-right:2px; font-size:10px;"><small>▼</small></span>';
                    } else {
                        $comparePosSpan = '<span style="color: orange; margin-right:2px; font-size:14px">•</span>';
                    }
                } else {
                    $formatLastCl = '<span style="color: black;"><b>―</b>';

                    $formatLastCtr = '<span style="color: black;"><b>―</b>';

                    $formatLastPos = '<span style="color: black;"><b>―</b>';

                    $formatLastImp = '<span style="color: black;"><b>―</b>';

                    $compareClicksSpan = $emptySpan;
                    $compareCtrSpan = $emptySpan;
                    $compareImpSpan = $emptySpan;
                    $comparePosSpan = $emptySpan;
                }
                $html .= '<tr';
                if (!isset($row['lastWeekAnalyze'])) {
                    $html .= ' style="background-color: #e5f4e8;">';
                } else {
                    $html .= '>';
                }
                $html .= '
                        <td style="width: 10%; background-color: white"></td>
                        <td style="width: 90%; padding: 8px 16px; text-align: left;  border-left: 0px;">' . $row['query'] . '</td>
                        <td style="text-align: center;">
                        <table>
                                <tr>
                                    <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $emptySpan . $formatLastCl . '</td>
                                    <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $compareClicksSpan . number_format($row["clicks"], 2) . '</td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align: center;">
                        <table>
                                <tr>
                                    <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $emptySpan . $formatLastCtr . '</td>
                                    <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $compareCtrSpan .  number_format($row["ctr"], 2) . '</td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align: center;">
                        <table>
                                <tr>
                                    <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $emptySpan . $formatLastImp . '</td>
                                    <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $compareImpSpan . number_format($row["impressions"], 2) . '</td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align: center;">
                        <table>
                                <tr>
                                    <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $emptySpan . $formatLastPos . '</td>
                                    <td style="width: 200px; padding: 8px 16px; text-align: center;">' . $comparePosSpan . number_format($row["position"], 2) . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>';
            }
        } else {
            $html .= '<tr><td colspan="6" style="padding: 8px 16px; text-align: center; color:#858585; font-size: 10px;">Herhangi bir sonuç bulunamadı.</td></tr>';
        }
    }
    $html .= '</table>';
    return $html;
}
