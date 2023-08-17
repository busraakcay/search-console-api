<?php

require __DIR__ . '/../../vendor/autoload.php';

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\MetricType;
use Google\Analytics\Data\V1beta\GetMetadataRequest;
use Google\Analytics\Data\V1beta\Metadata;
use Google\ApiCore\ApiException;

use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\Pivot;
use Google\Analytics\Data\V1beta\RunPivotReportRequest;
use Google\Analytics\Data\V1beta\RunPivotReportResponse;
use Google\Analytics\Data\V1beta\RunRealtimeReportRequest;
use Google\Analytics\Data\V1beta\RunRealtimeReportResponse;

use function PHPSTORM_META\type;

class GA4ApiClient
{
    private $client;
    private $propertyId;

    public function __construct()
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $_ENV["GOOGLE_APPLICATION_CREDENTIALS_LOCATION"]);
        $this->propertyId = $_ENV["PROPERTY_ID"];
        $this->client = new BetaAnalyticsDataClient();
    }

    public function getOnlineUsersLast5Minutes()
    {
        $response = $this->client->runRealtimeReport([
            'property' => 'properties/' . $this->propertyId,
            'dateRanges' => [
                new DateRange([
                    'start_date' => '5minutesAgo',
                    'end_date' => 'now',
                ]),
            ],
            'metrics' => [
                new Metric([
                    'name' => 'activeUsers',
                ]),
            ],
        ]);

        $metricValue = $response->getRows()[0]->getMetricValues()[0]->getValue();

        return $metricValue;
    }


    public function getActiveUsers($startDate, $endDate)
    {
        try {
            $response = $this->client->runReport([ // returns a customized report of ga4 event data.
                'property' => 'properties/' . $this->propertyId,
                'dateRanges' => [
                    new DateRange([
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]),
                ],
                'metrics' => [
                    new Metric(
                        [
                            'name' => 'activeUsers',
                        ]
                    )
                ]
            ]);
            return $response->getRows()[0]->getMetricValues()[0]->getValue();
        } finally {
            $this->client->close();
        }
    }

    public function getActiveUserAndDateJson($firstDay, $lastDay)
    {
        $startDate = strtotime($lastDay);
        $endDate = strtotime($firstDay);

        $userDateCountData = array();
        while ($endDate >= $startDate) {
            $startDateFormatted = date("Y-m-d", $startDate);
            $formatedDate = $startDateFormatted;
            $userCount = $this->getActiveUsers($formatedDate, $formatedDate);
            $formatDateDMY = formatDateString($formatedDate);
            array_push($userDateCountData, ["label" => $formatDateDMY, "y" => $userCount]);
            $startDate = strtotime('+1 day', $startDate);
        }
        return $userDateCountData;
    }

    public function runReport($startDate, $endDate, $dimensionNames, $metricName = 'activeUsers', $size = null)
    {
        try {
            $dimensions = [];
            foreach ($dimensionNames as $dimensionName) {
                $dimensions[] = new Dimension([
                    'name' => $dimensionName,
                ]);
            }

            $response = $this->client->runReport([
                'property' => 'properties/' . $this->propertyId,
                'dateRanges' => [
                    new DateRange([
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]),
                ],
                'dimensions' => $dimensions,
                'metrics' => [
                    new Metric([
                        'name' => $metricName,
                    ])
                ]
            ]);

            $resultArray = [];
            $recordCount = 0;
            foreach ($response->getRows() as $row) {
                if (isset($size)) {
                    if ($recordCount >= $size) {
                        break;
                    }
                }
                $dimensionValues = $row->getDimensionValues();
                $dimensionArray = [];

                foreach ($dimensionValues as $dimensionValue) {
                    $dimensionArray[] = $dimensionValue->getValue();
                }

                $resultArray[] = [
                    'title' => $dimensionArray[0] ?? '',
                    'url' => $dimensionArray[1] ?? '',
                    'user' => $row->getMetricValues()[0]->getValue(),
                ];
                $recordCount++;
            }
            return $resultArray;
        } finally {
            $this->client->close();
        }
    }

    public function getMetaData()
    {
        $formattedName = sprintf('properties/%s/metadata', $this->propertyId);

        try {
            $response = $this->client->getMetaData($formattedName);
        } catch (ApiException $ex) {
            printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
        }

        printf(
            'Dimensions and metrics available for Google Analytics 4 property'
                . ' %s (including custom fields):' . PHP_EOL,
            $this->propertyId
        );
        echo "<br />";
        printGetMetadataByPropertyId($response);
    }

    public function runReport_test()
    {
        try {
            $response = $this->client->runReport([ // returns a customized report of ga4 event data.
                'property' => 'properties/' . $this->propertyId,
                'dateRanges' => [
                    new DateRange([
                        'start_date' => '2020-03-31',
                        'end_date' => 'today',
                    ]),
                ],
                'dimensions' => [
                    new Dimension(
                        [
                            'name' => 'searchTerm',
                        ]
                    ),
                ],
                'metrics' => [
                    new Metric(
                        [
                            'name' => 'activeUsers',
                        ]
                    )
                ]
            ]);

            foreach ($response->getRows() as $row) {
                print $row->getDimensionValues()[0]->getValue()
                    . ' ' . $row->getMetricValues()[0]->getValue() . "<br/>";
            }

            // Multiple Dimension
            // foreach ($response->getRows() as $row) {
            //     $dimensionValues = $row->getDimensionValues();
            //     $dimensionString = '';
            //     foreach ($dimensionValues as $dimensionValue) {
            //         $dimensionString .= $dimensionValue->getValue() . ' ';
            //     }
            //     print rtrim($dimensionString) . ' ' . $row->getMetricValues()[0]->getValue() . "<br/>";
            // }

        } finally {
            $this->client->close();
        }
    }

    public function runPivotReport() // like runReport but a summarized version of the data 
    {
        $response = $this->client->runPivotReport(
            [
                'property' => 'properties/' . $this->propertyId,
                'dateRanges' => [
                    new DateRange([
                        'start_date' => '2020-03-31',
                        'end_date' => 'today',
                    ]),
                ],
                'pivots' => [
                    new Pivot([
                        'field_names' => ['country'],
                        'limit' => 250,
                        'order_bys' => [new OrderBy([
                            'dimension' => new DimensionOrderBy([
                                'dimension_name' => 'country',
                            ]),
                        ])],
                    ]),
                    new Pivot([
                        'field_names' => ['browser'],
                        'offset' => 3,
                        'limit' => 3,
                        'order_bys' => [new OrderBy([
                            'metric' => new MetricOrderBy([
                                'metric_name' => 'activeUsers',
                            ]),
                            'desc' => true,
                        ])],
                    ]),
                ],
                'dimensions' => [
                    new Dimension(
                        [
                            'name' => 'country',
                        ]
                    ),
                    new Dimension(
                        [
                            'name' => 'browser',
                        ]
                    ),
                ],
                'metrics' => [
                    new Metric(
                        [
                            'name' => 'activeUsers',
                        ]
                    )
                ]
            ]
        );
        printPivotReportResponse($response);
    }

    public function runRealtimeReport()
    {
        $response = $this->client->runRealtimeReport([
            'property' => 'properties/' . $this->propertyId,
            'dimensions' => [
                new Dimension([
                    'name' => 'city',
                ]),
                // new Dimension(
                //     [
                //         'name' => 'country',
                //     ]
                // ),
            ],
            'metrics' => [
                new Metric([
                    'name' => 'activeUsers',
                ]),
            ],
        ]);

        printRunRealtimeReportResponse($response);
    }
}
