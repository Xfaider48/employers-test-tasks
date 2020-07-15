<?php

function addWordToMap(array &$wordsMap, string $word): void
{
    $word = mb_strtolower($word);
    if (!isset($wordsMap[$word])) {
        $wordsMap[$word] = 1;
    } else {
        $wordsMap[$word]++;
    }
}

function topWords(string $text, int $top = 5): array
{
    $wordsMap = [];
    $length = mb_strlen($text);
    $currentWord = '';
    for ($i = 0; $i < $length; $i++) {
        $char = mb_substr($text, $i, 1);
        if (preg_match('/\pL/', $char) !== 1) {
            if ($currentWord !== '') {
                addWordToMap($wordsMap, $currentWord);
            }

            $currentWord = '';
        } else {
            $currentWord .= $char;
        }
    }

    if ($currentWord!== '') {
        addWordToMap($wordsMap, $currentWord);
    }

    arsort($wordsMap);
    return array_slice($wordsMap, 0, $top, true);
}