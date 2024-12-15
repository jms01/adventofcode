#!/bin/php
<?php

declare(strict_types=1);

$input = file_get_contents('PLACE_INPUT_HERE.txt');
$input = trim($input);

// find all mul() instructions
preg_match_all('/mul\(\d{1,3},\d{1,3}\)/', $input, $muls);

// parse the instruction's operands
$muls = array_map(
    function ($mul) {
        preg_match('/mul\((\d{1,3}),(\d{1,3})\)/', $mul, $operands);
        return $operands;
    },
    $muls[0],
);

// execute all multiply operations
$answer = array_reduce($muls, function ($carry, $mul) {
    return $carry + (intval($mul[1]) * intval($mul[2]));
});

echo 'Answer: ', $answer, PHP_EOL;
