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

$directions = [
    // cardinal directions, clockwise
    'up', 'right', 'down', 'left',
    // diagonals, clockwise
    'diagonal-up-right', 'diagonal-down-right',
    'diagonal-down-left', 'diagonal-up-left',
];

$searchLetters = [ /*'X',*/ 'M', 'A', 'S' ];

$answer = 0;

for ($y = 0; $y < count($input); $y++) {
    for ($x = 0; $x < count($input[$y]); $x++) {
        if ($input[$y][$x] === 'X') {
            // can be start of XMAS
            foreach ($directions as $direction) {
                $curX = $x;
                $curY = $y;
                $xmasFound = true;
                foreach ($searchLetters as $curLetter) {
                    // move position in direction
                    switch ($direction) {
                        case 'up':
                            $curY -= 1;
                            break;
                        case 'right':
                            $curX += 1;
                            break;
                        case 'down':
                            $curY += 1;
                            break;
                        case 'left':
                            $curX -= 1;
                            break;
                        case 'diagonal-up-right':
                            $curY -= 1;
                            $curX += 1;
                            break;
                        case 'diagonal-down-right':
                            $curY += 1;
                            $curX += 1;
                            break;
                        case 'diagonal-down-left':
                            $curY += 1;
                            $curX -= 1;
                            break;
                        case 'diagonal-up-left':
                            $curY -= 1;
                            $curX -= 1;
                            break;
                        default: throw new RuntimeException('Unkown direction "' . $direction . '".');
                    }
                    if (
                        // check if current position is valid
                        $curY < 0 || $curX < 0
                        || $curY >= count($input) || $curX >= count($input[$curY])
                        // check if current position contains the $curLetter
                        || $input[$curY][$curX] !== $curLetter
                    ) {
                        // one of the conditions is not met
                        // abort this search direction
                        $xmasFound = false;
                        break;
                    }
                }
                if ($xmasFound) {
                    echo 'XMAS found in direction: ', $direction, PHP_EOL;
                    $answer++;
                    continue; // check other directions
                }
            }
        }
    }
}

echo 'Answer: ', $answer, PHP_EOL;
