#!/bin/php
<?php

declare(strict_types=1);

$input = file_get_contents('PLACE_INPUT_HERE.txt');
$input = trim($input);

$input = explode("\n", $input);
$input = array_map(
    function ($line) {
        return str_split($line, 1);
    },
    $input,
);

$answer = 0;

for ($y = 1; $y < (count($input) - 1); $y++) {
    for ($x = 1; $x < (count($input[$y]) - 1); $x++) {
        if ($input[$y][$x] === 'A') {
            // can be center of MAS

            // get adjecent letters in diagonal planes
            $letterNW = $input[$y - 1][$x + 1];
            $letterSW = $input[$y + 1][$x + 1];
            $letterSE = $input[$y + 1][$x - 1];
            $letterNE = $input[$y - 1][$x - 1];

            // check all four directions
            $masFoundInDirections = [];
            if ($letterNW === 'M' && $letterSE === 'S') { // diagonal nw to se
                $masFoundInDirections[] = 'diagonal-nw-to-se';
            } elseif ($letterSE === 'M' && $letterNW === 'S') { // reverse
                $masFoundInDirections[] = 'diagonal-se-to-nw';
            }

            if ($letterSW === 'M' && $letterNE === 'S') { // diagonal sw to ne
                $masFoundInDirections[] = 'diagonal-sw-to-ne';
            } elseif ($letterNE === 'M' && $letterSW === 'S') { // reverse
                $masFoundInDirections[] = 'diagonal-ne-to-sw';
            }

            // any two directions with M-A-S result in the pattern being found
            if (count($masFoundInDirections) >= 2) {
                $answer++;
            }
        }
    }
}

echo 'Answer: ', $answer, PHP_EOL;
