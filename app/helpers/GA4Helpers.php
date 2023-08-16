<?php

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


function printPivotReportResponse(RunPivotReportResponse $response)
{
    print 'Report result: ' . PHP_EOL;
    echo "<br />";
    foreach ($response->getRows() as $row) {
        printf(
            '%s %s' . PHP_EOL,
            $row->getDimensionValues()[0]->getValue(),
            $row->getMetricValues()[0]->getValue()
        );
        echo "<br />";
    }
}

function printGetMetadataByPropertyId(Metadata $response)
{
    foreach ($response->getDimensions() as $dimension) {
        print('DIMENSION' . PHP_EOL);
        printf(
            '%s (%s): %s' . PHP_EOL,
            $dimension->getApiName(),
            $dimension->getUiName(),
            $dimension->getDescription(),
        );
        echo "<br />";
        printf(
            'custom definition: %s' . PHP_EOL,
            $dimension->getCustomDefinition() ? 'true' : 'false'
        );
        echo "<br />";
        if ($dimension->getDeprecatedApiNames()->count() > 0) {
            print('Deprecated API names: ');
            foreach ($dimension->getDeprecatedApiNames() as $name) {
                print($name . ', ');
                echo "<br />";
            }
            print(PHP_EOL);
            echo "<br />";
        }
        print(PHP_EOL);
        echo "<br />";
    }

    foreach ($response->getMetrics() as $metric) {
        print('METRIC' . PHP_EOL);
        printf(
            '%s (%s): %s' . PHP_EOL,
            $metric->getApiName(),
            $metric->getUiName(),
            $metric->getDescription(),
        );
        echo "<br />";
        printf(
            'custom definition: %s' . PHP_EOL,
            $metric->getCustomDefinition() ? 'true' : 'false'
        );
        echo "<br />";

        if ($metric->getDeprecatedApiNames()->count() > 0) {
            print('Deprecated API names: ');
            foreach ($metric->getDeprecatedApiNames() as $name) {
                print($name . ',');
                echo "<br />";
            }
            print(PHP_EOL);
            echo "<br />";
        }
        print(PHP_EOL);
        echo "<br />";
    }
}

function printRunRealtimeReportResponse(RunRealtimeReportResponse $response)
{
    // [START analyticsdata_print_run_realtime_report_response_header]
    printf('%s rows received%s', $response->getRowCount(), PHP_EOL);
    foreach ($response->getDimensionHeaders() as $dimensionHeader) {
        printf('Dimension header name: %s%s', $dimensionHeader->getName(), PHP_EOL);
    }
    foreach ($response->getMetricHeaders() as $metricHeader) {
        printf(
            'Metric header name: %s (%s)%s',
            $metricHeader->getName(),
            MetricType::name($metricHeader->getType()),
            PHP_EOL
        );
    }
    // [END analyticsdata_print_run_realtime_report_response_header]

    // [START analyticsdata_print_run_realtime_report_response_rows]
    print 'Report result: ' . PHP_EOL;

    foreach ($response->getRows() as $row) {
        printf(
            '%s %s' . PHP_EOL,
            $row->getDimensionValues()[0]->getValue(),
            $row->getMetricValues()[0]->getValue()
        );
    }
    // [END analyticsdata_print_run_realtime_report_response_rows]
}
