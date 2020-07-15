<?php

/**
 * Brute force resolve method
 *
 * @param array<array> $arrayOfSets
 *
 * @return array
 */
function resolveWithoutOptimization(array $arrayOfSets): array
{
    $mergedSets = [];

    foreach ($arrayOfSets as $sets) {
        if (!$mergedSets) {
            $mergedSets[] = $sets;
            continue;
        }

        $foundEqualIndexes = [];
        foreach ($sets as $value) {
            foreach ($mergedSets as $mergedIndex => $mergedSet) {
                if (in_array($value, $mergedSet)) {
                    $foundEqualIndexes[] = $mergedIndex;
                    break;
                }
            }
        }

        $foundEqualIndexes = array_unique($foundEqualIndexes);
        if ($foundEqualIndexes) {
            $first = current($foundEqualIndexes);
            for ($i = 1; $i < count($foundEqualIndexes); $i++) {
                $mergedSets[$first] = array_merge($mergedSets[$first], $mergedSets[$foundEqualIndexes[$i]]);
                unset($mergedSets[$foundEqualIndexes[$i]]);
            }

            $mergedSets[$first] = array_merge($mergedSets[$first], $sets);
        } else {
            $mergedSets[] = $sets;
        }
    }

    foreach ($mergedSets as &$sets) {
        $sets = array_unique($sets);
        sort($sets);
    }

    return $mergedSets;
}