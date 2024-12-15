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

// create a lookup array
// how many times a value of $inputA is present in $inputB
$inputAinBCounts = array_count_values($inputB);

// calculate similarity multiplying every value in $inputA
// by how many times it's present in $inputB
$answer = array_reduce($inputA, function ($carry, $a) use ($inputAinBCounts) {
    $countInB = 0;
    if (isset($inputAinBCounts[$a])) {
        $countInB = $inputAinBCounts[$a];
    }
    return $carry + ($a * $countInB);
}, 0);

echo 'Answer: ', $answer, PHP_EOL;
