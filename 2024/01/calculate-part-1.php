#!/bin/php
<?php

declare(strict_types=1);

$input = file_get_contents('PLACE_INPUT_HERE.txt');
$input = trim($input);

$input = explode("\n", $input);
$input = array_map(function ($line) {
    return explode('   ', $line);
}, $input);

// split input into columns
$inputA = array_column($input, 0);
$inputB = array_column($input, 1);

// sort both columns
sort($inputA);
sort($inputB);

// zip the two arrays together
// $inputSorted = [ [$inputA[0], $inputB[0]], [$inputA[1], $inputB[1]], ...];
$inputSorted = array_map(null, $inputA, $inputB);

// calculate distance between input A and B
$answer = array_reduce($inputSorted, function ($carry, $values) {
    $valueA = $values[0];
    $valueB = $values[1];

    return $carry + abs($valueA - $valueB);
}, 0);

echo 'Answer: ', $answer, PHP_EOL;
