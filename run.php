<?php

$texts = [];
$expect = [];

include "texts.php";

$foo = str_split('udxsmpf');
$letters = str_split('sxocqnmwpfyheljrdgui');
$bar = array_diff($letters, $foo);

function extractVocabulary(string $text): array
{
    global $letters;
    assert($text !== '', 'Text is not empty');
    $pattern = implode('', $letters);
    assert(
        preg_match('/[^' . $pattern . '\s]/', $text) === 0,
        'There are only valid Googlon characters in the text'
    );
    $words = preg_split('/\s+/', $text);

    return sortWords($words);
}

function googlonCompare(string $wordA, string $wordB): int
{
    global $letters;
    $wordA = strtolower($wordA);
    $wordB = strtolower($wordB);
    if ($wordA === $wordB) {
        return 0;
    }

    for ($i = 0; $i < min(strlen($wordA), strlen($wordB)); $i++) {
        $keyA = array_search($wordA[$i], $letters, true);
        $keyB = array_search($wordB[$i], $letters, true);
        assert($keyA !== false && $keyB !== false, 'Letter indexes should exist');

        if ($keyA === $keyB) {
            continue;
        }

        if ($keyA < $keyB) {
            return -1;
        }

        return 1;
    }

    return 0;
}

function sortWords(array $words): array
{
    global $letters;
    assert(!empty($words), 'Array of words is not empty');
    $list = $words;
    sort($list);
    $list = array_unique($list);
    usort($list, 'googlonCompare');

    return $list;
}

function isFoo(string $letter): bool
{
    global $foo;
    assert(strlen($letter) === 1, 'Letter is only one char');

    return in_array($letter, $foo, true);
}

function isBar(string $letter): bool
{
    global $bar;
    assert(strlen($letter) === 1, 'Letter is only one char');

    return in_array($letter, $bar, true);
}

// prepositions are the words of exactly 6 letters
// which end in a foo letter and do not contain the letter u.
function isPreposition(string $word): bool
{
    global $foo;
    assert($word !== '', 'Word is not empty');
    $word = strtolower($word);
    $last = $word[strlen($word) - 1];
    $containsLetterU = in_array('u', str_split($word), true);

    return strlen($word) === 6 && isFoo($last) && !$containsLetterU;
}

// verbs are words of 6 letters or more that end in a bar letter
function isVerb(string $word): bool
{
    global $bar;
    assert($word !== '', 'Word is not empty');
    $word = strtolower($word);
    $last = $word[strlen($word) - 1];

    return strlen($word) >= 6 && isBar($last);
}

// if a verb starts in a bar letter, then the verb is
// inflected in its subjunctive form.
function isSubjVerb(string $word): bool
{
    return isVerb($word) && isBar($word[0]);
}

function toNumber(string $word): int
{
    global $letters;
    assert($word !== '', 'Word is not empty');
    $res = 0;
    for ($i = 0, $iMax = strlen($word); $i < $iMax; $i++) {
        $val = array_search($word[$i], $letters, true);
        assert($val !== false, 'Invalid letter index');
        $res += (int)$val * (20 ** $i);
    }

    return (int)$res;
}

function isPrettyNumber(int $number): bool
{
    return $number % 3 === 0 && $number >= 81827;
}

function countPrepositions(string $text): int
{
    $num = 0;
    foreach (preg_split('/\s+/', $text) as $word) {
        if (isPreposition($word)) {
            $num++;
        }
    }

    return $num;
}

function countVerbs(string $text): int
{
    $num = 0;
    foreach (preg_split('/\s+/', $text) as $word) {
        if (isVerb($word)) {
            $num++;
        }
    }

    return $num;
}

function countSubjVerbs(string $text): int
{
    $num = 0;
    foreach (preg_split('/\s+/', $text) as $word) {
        if (isSubjVerb($word)) {
            $num++;
        }
    }

    return $num;
}

function countPrettyNumbers(string $text): int
{
    $num = 0;
    foreach (preg_split('/\s+/', $text) as $word) {
        if (isPrettyNumber(toNumber($word))) {
            $num++;
        }
    }

    return $num;
}

function addColors(string $text): string
{
    $words = [];
    foreach (preg_split('/\s+/', $text) as $word) {
        if (isPreposition($word)) {
            $words[] = '<span class="text-blue-600">' . $word . '</span>';
        } elseif (isSubjVerb($word)) {
            $words[] = '<span class="text-orange-600">' . $word . '</span>';
        } elseif (isVerb($word)) {
            $words[] = '<span class="text-purple-600">' . $word . '</span>';
        } elseif (isPrettyNumber(toNumber($word))) {
            $words[] = '<span class="text-teal-600">' . $word . '</span>';
        } else {
            $words[] = $word;
        }
    }

    return implode(' ', $words);
}

echo <<<EOF
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Googlon Challenge</title>
        <style>
        body, html, * {
            font-family: sans-serif;
        }
        h1 {
            border-bottom: 2px solid lightgray;
            padding-bottom: 1rem;
        }
</style>
    <script src="https://cdn.tailwindcss.com"></script>
    </head>
<body>
<div class="bg-white px-6 py-6 lg:px-8">
  <div class="mx-auto max-w-3xl text-base leading-7 text-gray-700">
    <p class="text-3xl font-bold tracking-tight text-gray-700 sm:text-4xl">Test Cases</p>
EOF;

$fail = <<<EOF
<svg class="order-first mt-1 h-5 w-5 flex-none text-red-600" fill="currentColor" stroke="white" viewBox="2 2 20 20" stroke-width="2">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg>

EOF;

$pass = <<<EOF
<svg class="order-first mt-1 h-5 w-5 flex-none text-green-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
</svg>
EOF;


foreach ($texts as $key => $text) {
    $numPrepositions = countPrepositions($text);
    $numVerbs = countVerbs($text);
    $numSubjVerbs = countSubjVerbs($text);
    $vocabularyList = implode(', ', extractVocabulary($text));
    $numPrettyNumbers = countPrettyNumbers($text);
    $result1 = $numPrepositions === $expect[$key][0] ? false : 'bg-red-50';
    $output1 = !$result1 ? $pass : $fail;
    $result2 = $numVerbs === $expect[$key][1] ? false : 'bg-red-50';
    $output2 = !$result2 ? $pass : $fail;
    $result3 = $numSubjVerbs === $expect[$key][2] ? false : 'bg-red-50';
    $output3 = !$result3 ? $pass : $fail;
    $result4 = $vocabularyList === implode(', ', preg_split('/\s+/', $expect[$key][3])) ? false : 'bg-red-50';
    $output4 = !$result4 ? $pass : $fail;
    $result5 = $numPrettyNumbers === $expect[$key][4] ? false : 'bg-red-50';
    $output5 = !$result5 ? $pass : $fail;

    $text = addColors($text);

    echo <<<EOF
    <p class="my-4 text-2xl font-bold tracking-tight text-gray-700 sm:text-3xl border-gray-200 border-t-2 pt-4">Text $key</p>
    <p class="my-4 text-xl font-bold tracking-tight text-gray-700 sm:text-2xl">Input</p>
    <figure class="border-l-4 border-gray-300 pl-5">
        <blockquote class="font-semibold text-gray-500">
    $text
    </blockquote>
    </figure>
    <p class="my-4 text-xl font-bold tracking-tight text-gray-700 sm:text-2xl">Output</p>
    <blockquote class="font-semibold text-gray-500 mb-10">
        <div class="flex gap-x-2 $result1"><span>1) There are <span class="text-blue-600">$numPrepositions prepositions</span> in the text</span>
        $output1
        </div>
        <div class="flex gap-x-2 $result2"><span>2) There are <span class="text-purple-600">$numVerbs verbs</span> in the text</span>
        $output2
        </div>
        <div class="flex gap-x-2 $result3"><span>3) There are <span class="text-orange-600">$numSubjVerbs subjunctive verbs</span> in the text</span>
        $output3
        </div>
        <div class="flex gap-x-2 $result4"><span>4) Vocabulary list: <span class="text-gray-600 italic font-normal">$vocabularyList</span></span>
        $output4
        </div>
        <div class="flex gap-x-2 $result5"><span>5) There are <span class="text-teal-600">$numPrettyNumbers distinct pretty numbers</span> in the text</span>
        $output5
        </div>
    </blockquote>
EOF;
}

echo <<<EOF
</body>
</html>
EOF;
