<?php

use Crwlr\Html2Text\DomDocumentFactory;
use Crwlr\Html2Text\Exceptions\InvalidHtmlException;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

//expect()->extend('toBeOne', function () {
//    return $this->toBe(1);
//});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function helper_dump(mixed $var): void
{
    error_log(var_export($var, true));
}

function helper_dieDump(mixed $var): void
{
    error_log(var_export($var, true));

    exit;
}

function helper_makeDom(string $html): DOMDocument
{
    $trimmedLowerCaseHtml = strtolower(trim($html));

    if (!str_starts_with($trimmedLowerCaseHtml, '<!doctype html>') && !str_contains($trimmedLowerCaseHtml, '<html')) {
        $html = '<!DOCTYPE html><html lang="en"><head><title>test</title></head><body>' . $html . '</body></html>';
    }

    return DomDocumentFactory::make($html);
}

/**
 * @throws Exception
 */
function helper_getElementById(string|DOMDocument $dom, string $id): DOMNode
{
    if (is_string($dom)) {
        $dom = helper_makeDom($dom);
    }

    $element = $dom->getElementById($id);

    if (!$element instanceof DOMNode) {
        throw new Exception('Element not found');
    }

    return $element;
}

/**
 * @throws InvalidHtmlException|Exception
 */
function helper_getFirstTextNodeInId(string|DOMDocument $dom, string $id): DOMNode
{
    $el = helper_getElementById($dom, $id);

    foreach ($el->childNodes as $childNode) {
        /** @var DOMNode $childNode */
        if ($childNode->nodeType === XML_TEXT_NODE) {
            return $childNode;
        }
    }

    throw new Exception('Text node not found');
}

function helper_compareStringCharByChar(string $string1, string $string2): void
{
    $charNameHelper = function (string $char): string {
        if ($char === PHP_EOL) {
            return "linebreak";
        } elseif ($char === ' ') {
            return "space";
        }

        return $char;
    };

    $identical = true;

    foreach (mb_str_split($string1) as $index => $char) {
        if ($string2[$index] !== $char) {
            helper_dump($charNameHelper($char) . ' ❌ ' . $charNameHelper($string2[$index]));

            $identical = false;
        } else {
            helper_dump($charNameHelper($char) . ' 👍🏻');
        }
    }

    if ($identical) {
        helper_dump('The two strings are identical!');
    } else {
        helper_dump('The two strings are different');
    }
}
