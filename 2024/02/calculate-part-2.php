#!/bin/php
<?php

declare(strict_types=1);

$input = file_get_contents('PLACE_INPUT_HERE.txt');
$input = trim($input);

$input = explode("\n", $input);
$input = array_map(function ($line) {
    $values = explode(' ', $line);
    $values = array_map(
        function ($value) {
            return intval($value);
        },
        $values,
    );
    return $values;
}, $input);

// determine if report is safe
function isSafeReport($report, $dampenerAvailable = true)
{
    $reportIsSafe = true;
    $orgReport = array_values($report);

    if ($report[0] > $report[1]) {
        // make values negative, when sequence seems to be descending
        $report = array_map(
            function ($value) {
                return $value * -1;
            },
            $report,
        );
    }

    // only considering ascending sequences
    $lastValue = $report[0];
    for ($i = 1; $i < count($report); $i++) {
        $currentValue = $report[$i];
        if ($lastValue >= $currentValue) {
            // descending edge in ascending sequence = not safe
            // or same value, delta of 0 = not safe
            $reportIsSafe = false;
        } elseif (($currentValue - $lastValue) > 3) {
            // delta more than 3 = not safe
            $reportIsSafe = false;
        }
        if (!$reportIsSafe) {
            break;
        }
        $lastValue = $currentValue;
    }

    // dampener functionality
    if ($dampenerAvailable && !$reportIsSafe) {
        // brute force, see if report without one measurement is safe
        for ($i = 0; $i < count($report); $i++) {
            $reportWithoutValue = array_values($orgReport);
            array_splice($reportWithoutValue, $i, 1);
            if (isSafeReport($reportWithoutValue, false)) {
                $reportIsSafe = true;
                break;
            }
        }
    }

    return $reportIsSafe;
}

// find safe reports
$safeReportCount = 0;
foreach ($input as $report) {
    if (isSafeReport($report)) {
        $safeReportCount++;
    }
}

echo 'Answer: ', $safeReportCount, PHP_EOL;
