<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;

class FormatNamesService
{
    public const MULTIPLE_NAME_SEPERATORS = [' and ', ' & '];
    protected array $result = [];

    public function formatNames(array $input, int $jsonFlags = 0): string
    {
        if (empty($input)) {
            throw new Exception('No input data to process');
        }
        $this->result = [];

        //Iterate over each name
        foreach ($input as $name) {
            //Check if the name has multiple names
            if ($this->hasMultipleNames($name)) {
                $names = $this->processMultipleNames($name);

                foreach ($names as $name) {
                    $this->processName($name);
                }
            } else {
                $this->processName($name);
            }
        }

        return json_encode($this->result, $jsonFlags);
    }

    protected function hasMultipleNames(string $name): bool
    {
        //Check for any of the multiple name seperators
        if (Str::contains(strtolower($name), self::MULTIPLE_NAME_SEPERATORS)) {
            return true;
        }

        return false;
    }

    protected function processMultipleNames(string $multipleNames): array
    {
        $separatorUsed = $this->getSeperatorUsed($multipleNames);
        $splitNames = $this->getNamesSplit($separatorUsed, $multipleNames);

        //Name is `Mr and Mrs Smith` or `Mr and Mrs Joe Smith`
        //For the first name in the list, if it only has 1 segment then we need to set the `last_name`
        if (count($splitNames[0]) === 1) {
            $splitNames = $this->processMultipleSplitNames($splitNames);
        }

        $names = [];
        //Build an array of names from their split parts
        foreach ($splitNames as $splitName) {
            $names[] = implode(' ', $splitName);
        }

        return $names;
    }

    protected function getSeperatorUsed(string $multipleNames): string
    {
        //Iterate over the possible seperators to find which one is used
        foreach (self::MULTIPLE_NAME_SEPERATORS as $separator) {
            if (Str::contains(strtolower($multipleNames), $separator)) {
                return $separator;
            }
        }

        throw new Exception('No valid separator found in name: ' . $multipleNames);
    }

    protected function getNamesSplit(string $separatorUsed, string $multipleNames): array
    {
        //Split the names by the separator
        $names = explode($separatorUsed, $multipleNames);

        $splitNames = [];
        //Build an array of names split into their parts
        foreach ($names as $name) {
            $splitNames[] = explode(' ', trim($name));
        }

        return $splitNames;
    }

    protected function processMultipleSplitNames(array $splitNames): array
    {
        //We know that the `last_name` will come from the last name of the last element
        $finalKeyInList = array_key_last($splitNames);
        $lastName = array_pop($splitNames[$finalKeyInList]);
        $finalNameInList = $splitNames[$finalKeyInList];

        //Big assumption here: we need to check for a "traditional" approach to name formatting
        //If there are exactly 2 names in the list and the last name in the list has 3 elements eg. `Dr & Mrs Joe Bloggs`
        //then we need to take the `first_name` element from the last name in the list and give it to the first name in the list
        //Since we strip the last name out above we actually need to check if the final name has 2 elements
        if (
            count($splitNames) === 2 &&
            count($finalNameInList) === 2
        ) {
            $firstName = $splitNames[1][1];
            unset($splitNames[1][1]);
            $splitNames[1] = array_values($splitNames[1]);

            $splitNames[0][] = $firstName;
        }

        foreach ($splitNames as &$splitName) {
            $splitName[] = $lastName;
        }

        return $splitNames;
    }

    protected function processName(string $name): void
    {
        $splitName = explode(' ', trim($name));

        //Process based on number of parts
        match (count($splitName)) {
            2 => $this->processTwoPartName($splitName),
            3 => $this->processThreePartName($splitName),
            default => throw new Exception('Name has an unsupported number of parts: ' . $name)
        };
    }

    protected function processTwoPartName(array $nameParts): void
    {
        $this->result[] = [
            'title' => trim($nameParts[0]),
            'first_name' => null,
            'last_name' => trim($nameParts[1]),
            'initial' => null,
        ];
    }

    protected function processThreePartName(array $nameParts): void
    {
        //Check if the second part (first name) is an initial
        if (
            Str::length($nameParts[1]) === 1 ||
            Str::endsWith($nameParts[1], '.')
        ) {
            $this->result[] = [
                'title' => trim($nameParts[0]),
                'first_name' => null,
                'last_name' => trim($nameParts[2]),
                'initial' => rtrim($nameParts[1], '.'),
            ];
        } else {
            $this->result[] = [
                'title' => trim($nameParts[0]),
                'first_name' => trim($nameParts[1]),
                'last_name' => trim($nameParts[2]),
                'initial' => null,
            ];
        }
    }
}
