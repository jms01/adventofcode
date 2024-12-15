#!/bin/php
<?php

declare(strict_types=1);

$input = file_get_contents('PLACE_INPUT_HERE.txt');
$input = trim($input);

// split to do/don't chunks
$input = preg_split('/(do|don\'t)\(\)/', $input, -1, PREG_SPLIT_DELIM_CAPTURE);

// initial state is multiplications is enabled
array_unshift($input, 'do');

// chunk array to [ 0 => do|don't, 1 => <payload> ]
$input = array_chunk($input, 2);

// filter out "don't" chunks
$input = array_filter($input, function ($value) {
    return $value[0] === 'do';
});

// only keep payloads
$input = array_map(function ($value) {
    return $value[1];
}, $input);

// serialize items
$input = implode('do()', $input);

// calculate multiplications in string
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
