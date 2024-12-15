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

// parse print rules
$printRules = explode("\n", $printRules);
$printRules = array_map(
    function ($printRule) {
        $printRule = explode('|', $printRule);
        return [
            'pageNumberPre' => $printRule[0],
            'pageNumberPost' => $printRule[1],
        ];
    },
    $printRules,
);

// parse print jobs
$printJobs = explode("\n", $printJobs);
$printJobs = array_map(
    fn($printJob) => explode(',', $printJob),
    $printJobs,
);

// utility functions
function array_search_last($needle, array $haystack, bool $strict = false)
{
    return array_search($needle, array_reverse($haystack, true), $strict);
}

class RuleRegistry
{
    private $rules = [];

    public function addRule($pageNumberPre, $pageNumberPost)
    {
        if (!isset($this->rules[$pageNumberPre])) {
            $this->rules[$pageNumberPre] = [
                'pageNumbersProhibitedBefore' => [],
                'pageNumbersProhibitedAfter' => [],
            ];
        }
        // used for validation
        $this->rules[$pageNumberPre]['pageNumbersProhibitedBefore'][] = $pageNumberPost;
        // used for repairing print jobs
        $this->rules[$pageNumberPost]['pageNumbersProhibitedAfter'][] = $pageNumberPre;
    }

    public function getPageNumbersProhibitedBeforeByPageNumber($pageNumber)
    {
        $rules = [];
        if (isset($this->rules[$pageNumber])) {
            $rules = $this->rules[$pageNumber]['pageNumbersProhibitedBefore'];
        }
        return $rules;
    }

    public function getPageNumbersProhibitedAfterByPageNumber($pageNumber)
    {
        $rules = [];
        if (isset($this->rules[$pageNumber])) {
            $rules = $this->rules[$pageNumber]['pageNumbersProhibitedAfter'];
        }
        return $rules;
    }
}

class PrintingSpecification
{
    private $ruleRegistry;
    public function __construct(RuleRegistry $ruleRegistry)
    {
        $this->ruleRegistry = $ruleRegistry;
    }

    public function isValid(array $printJob)
    {
        $isValid = true;
        $printJobUnique = array_unique($printJob, SORT_NUMERIC);
        foreach ($printJobUnique as $pageNumberToCheckPre) {
            // only check rules for page numbers that are actually in this print job
            // and check them only once per job

            // get all rules governing $pageNumberToCheckPre
            $pageNumbersProhibitedBefore =
                $this->ruleRegistry->getPageNumbersProhibitedBeforeByPageNumber($pageNumberToCheckPre);
            // only keep rules for page numbers which are in this job
            $pageNumbersToCheckPost = array_intersect($pageNumbersProhibitedBefore, $printJobUnique);
            calculationCountInc(min(count($pageNumbersProhibitedBefore), count($printJobUnique)));

            if (!empty($pageNumbersToCheckPost)) {
                // make sure there are actually rules to check

                // get last position of $pageNumberToCheckPre
                $lastPageNumberPosPre = array_search_last($pageNumberToCheckPre, $printJob, true);
                // get all page numbers before the last occurrence of $pageNumberToCheckPre
                $pageNumbersBefore = array_slice($printJob, 0, $lastPageNumberPosPre);

                // check that none of the $pageNumbersToCheckPost
                // occur before the last position of $pageNumberToCheckPre
                $pageNumbersInViolation = array_intersect($pageNumbersToCheckPost, $pageNumbersBefore);
                calculationCountInc(min(count($pageNumbersToCheckPost), count($pageNumbersBefore)));

                if (!empty($pageNumbersInViolation)) {
                    // at least one page number is found to be in violation
                    // print job not valid
                    $isValid = false;
                    break;
                }
            } else {
                // no rules or no applicable page numbers in this print job
                // skip checking this page number
            }
        }
        return $isValid;
    }
}

// load rules in rule registry
$ruleRegistry = new RuleRegistry();
foreach ($printRules as $rule) {
    $ruleRegistry->addRule($rule['pageNumberPre'], $rule['pageNumberPost']);
}

// create printing spec instance
$printingSpec = new PrintingSpecification($ruleRegistry);

// find invalid print jobs
$printJobsInvalid = array_filter(
    $printJobs,
    fn($printJob) => !$printingSpec->isValid($printJob),
);

// fix invalid print jobs
$printJobsValid = array_map(
    function ($printJob) use ($ruleRegistry, $printingSpec) {
        // reorder jobs based on the relevant rule count for each page number

        // create an array of page number rule buckets
        $printJobRules = array_combine(
            array_values($printJob),
            array_fill(0, count($printJob), []),
        );

        foreach ($printJobRules as $pageNumber => $rules) {
            // get all prohibited page numbers after this page number
            $allProhibitedPageNumbers =
                $ruleRegistry->getPageNumbersProhibitedAfterByPageNumber($pageNumber);
            // only keep the page numbers that are actually in this print job
            $relevantProhibitedPageNumbers = array_intersect($printJob, $allProhibitedPageNumbers);
            calculationCountInc(min(count($printJob), count($allProhibitedPageNumbers)));
            // save as the relevant rules for this page number
            $printJobRules[$pageNumber] = $relevantProhibitedPageNumbers;
        }

        // sort the array based on the number of rules for each page number
        // page numbers with more rules need to be more towards the end of the job
        uasort(
            $printJobRules,
            fn($a, $b) => count($a) <=> count($b), // spaceship operator!
        );
        calculationCountInc(count($printJobRules));

        // keys are now in order
        // the keys (in order) become the valid print job
        $fixedPrintJob = array_keys($printJobRules);

        // the used strategy does not work when a page number
        // occurs multiple times in the same job
        if (count($printJob) !== count($fixedPrintJob)) {
            throw new RuntimeException(sprintf(
                'Fixed print job has %d elements, but original print job has %d elements. Something missing?',
                count($printJob),
                count($fixedPrintJob),
            ));
        }

        // make sure fixed print job is actually valid
        if (!$printingSpec->isValid($fixedPrintJob)) {
            throw new RuntimeException('Print job fixed, but still not valid.');
        }

        return $fixedPrintJob;
    },
    $printJobsInvalid,
);

// extract middle page numbers
$middlePageNumbers = array_map(
    function ($printJob) {
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
echo 'Calculations done: ', $calculationCount, ' (array_intersect)', PHP_EOL;
