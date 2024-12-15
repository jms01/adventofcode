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

// find safe reports
$safeReportCount = 0;
foreach ($input as $report) {
    $reportIsSafe = true;
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
            break;
        }
        if (($currentValue - $lastValue) > 3) {
            // delta more than 3 = not safe
            $reportIsSafe = false;
            break;
        }
        $lastValue = $currentValue;
    }
    if ($reportIsSafe) {
        $safeReportCount++;
    }
}

echo 'Answer: ', $safeReportCount, PHP_EOL;
