<?php

/**
 * Resolving with merging optimization
 *
 * @param array<array> $arrayOfSets
 *
 * @return array
 */
function resolveWithMergeOptimization(array $arrayOfSets): array
{
    $mergedSets = [];
    $valuesMap = [];
    $currentSetId = 1;

    foreach ($arrayOfSets as $sets) {
        $unknownValues = [];
        $unknownValuesMap = [];
        $foundSetId = null;
        foreach ($sets as $value) {

            // Values already in map?
            if (isset($valuesMap[$value])) {
                $existSetId = $valuesMap[$value];
                if ($foundSetId === null) {
                    $foundSetId = $existSetId;

                    // Merge unknown values into found set
                    // And into values map
                    foreach ($unknownValuesMap as $value => $setId) {
                        $mergedSets[$foundSetId][] = $value;
                        $valuesMap[$value] = $foundSetId;
                        continue;
                    }
                }

                // Found one more set to merge
                if ($foundSetId !== $existSetId) {
                    $existValues = $mergedSets[$existSetId];
                    // Merge existing merged set into first found
                    foreach ($existValues as $existValue) {
                        $valuesMap[$existValue] = $foundSetId;
                        $mergedSets[$foundSetId][] = $existValue;
                    }
                    // Remove merged set
                    unset($mergedSets[$existSetId]);
                }
            } else {
                // Already found set to merge?
                if ($foundSetId) {
                    $mergedSets[$foundSetId][] = $value;
                } else {
                    if (!isset($unknownValuesMap[$value])) {
                        $unknownValuesMap[$value] = $currentSetId;
                        $unknownValues[] = $value;
                    }
                }
            }

        }

        // Equals not found
        if ($foundSetId === null) {
            $valuesMap += $unknownValuesMap;
            $mergedSets[$currentSetId] = $unknownValues;
            $currentSetId++;
        }
    }

    foreach ($mergedSets as &$sets) {
        sort($sets);
    }

    return $mergedSets;
}