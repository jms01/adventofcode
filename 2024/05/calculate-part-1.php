#!/bin/php
<?php

declare(strict_types=1);

$calculationCount = 0;

function calculationCountInc($count = 1)
{
    global $calculationCount;
    $calculationCount += $count;
    return $calculationCount;
}

$input = file_get_contents('PLACE_INPUT_HERE.txt');
$input = trim($input);

list($printRules, $printJobs) = explode("\n\n", $input);

$printRules = explode("\n", $printRules);
$printRules = array_map(
    function ($printRule) {
        $printRule = explode('|', $printRule);
        return [
            'pageNumberBefore' => $printRule[0],
            'pageNumberAfter' => $printRule[1],
        ];
    },
    $printRules,
);

class PrintingSpecification
{
    // singleton pattern
    private static $instance = null;
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private $rules;

    private function __construct()
    {
        $this->rules = [];
    }

    public function addRule($prePageNumber, $postPageNumber)
    {
        // store as regular expression
        $this->rules[] = [
            'applicableRegexPre' => '/(^|,)(' . $prePageNumber . ')(,|$)/',
            'applicableRegexPost' => '/(^|,)(' . $postPageNumber . ')(,|$)/',
            'orderCorrectRegex' => '/(^|,)' . $prePageNumber . ',.*(' . $postPageNumber . ')(,|$)/',
        ];
    }

    public function isValid(string $printJob)
    {
        $isValid = true;
        foreach ($this->rules as $rule) {
            calculationCountInc(2);
            if (
                preg_match($rule['applicableRegexPre'], $printJob) === 1
                && preg_match($rule['applicableRegexPost'], $printJob) === 1
            ) {
                // rule is applicable
                calculationCountInc();
                if (preg_match($rule['orderCorrectRegex'], $printJob) !== 1) {
                    // order not correct
                    $isValid = false;
                    break;
                }
            } else {
                // ignore rule, one or both page numbers are not present in $printJob
            }
        }
        return $isValid;
    }
}

// load rules in printing spec class
$printingSpec = PrintingSpecification::getInstance();
foreach ($printRules as $rule) {
    $printingSpec->addRule($rule['pageNumberBefore'], $rule['pageNumberAfter']);
}

// find valid print jobs
$printJobs = explode("\n", $printJobs);

$printJobsValid = array_filter(
    $printJobs,
    fn($printJob) => $printingSpec->isValid($printJob),
);

// extract middle page numbers
$middlePageNumbers = array_map(
    function ($printJob) {
        $printJob = explode(',', $printJob);
        if ((count($printJob) % 2) === 1) {
            $middleIndex = floor(count($printJob) / 2) + 1;
            return intval($printJob[$middleIndex - 1]);
        } else {
            throw new RuntimeException('Cannot determine middle page number. Print job has even number of elements.');
        }
    },
    $printJobsValid,
);

// sum all middle page numbers
$answer = array_sum($middlePageNumbers);

echo 'Answer: ', $answer, PHP_EOL;
echo 'Calculations done: ', $calculationCount, ' (preg_match)', PHP_EOL;
