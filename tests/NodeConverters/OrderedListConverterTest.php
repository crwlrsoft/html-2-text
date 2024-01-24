<?php

namespace tests\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;
use Crwlr\Html2Text\NodeConverters\OrderedListConverter;

function helper_getOrderedListExampleHtml(): string
{
    return <<<HTML
        <ol id="a">
            <li>item 1</li>
            <li>item 2</li>
            <ol>
                <li>item 2.1</li>
                <li>item 2.2</li>
                <li>item 2.3</li>
                <ol>
                    <li>item 2.3.1</li>
                    <li>item 2.3.2</li>
                </ol>
            </ol>
            <li>item 3</li>
        </ol>
        HTML;
}

it('correctly converts an ordered list element', function () {
    $nodeConverter = new OrderedListConverter();

    $nodeConverter->setConverter(new Html2Text());

    $html = helper_getOrderedListExampleHtml();

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT


        1. item 1
        2. item 2
          1. item 2.1
          2. item 2.2
          3. item 2.3
            1. item 2.3.1
            2. item 2.3.2
        3. item 3


        TEXT);
});

it('applies the indentation size set in the converter instance (Html2Text)', function () {
    $nodeConverter = new OrderedListConverter();

    $nodeConverter->setConverter(new Html2Text(5));

    $html = helper_getOrderedListExampleHtml();

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT


        1. item 1
        2. item 2
             1. item 2.1
             2. item 2.2
             3. item 2.3
                  1. item 2.3.1
                  2. item 2.3.2
        3. item 3


        TEXT);
});

it('correctly converts list elements containing a block element', function () {
    $nodeConverter = new OrderedListConverter();

    $nodeConverter->setConverter(new Html2Text());

    $html = <<<HTML
        <ol id="a">
            <li>item</li>
            <li>item 2 <p>paragraph</p> <p>foo</p></li>
            <li>item 3</li>
            <ol>
                <li>item 3.1 <p>paragraph</p> yo</li>
            </ol>
        </ol>
        HTML;

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    // This is because PhpStorm removes trailing spaces inside heredoc :/
    $expectedText = PHP_EOL . PHP_EOL .
        '1. item' . PHP_EOL .
        '2. item 2 ' . PHP_EOL .
        '   ' . PHP_EOL .
        '   paragraph' . PHP_EOL .
        '   ' . PHP_EOL .
        '   foo' . PHP_EOL .
        '   ' . PHP_EOL .
        '3. item 3' . PHP_EOL .
        '  1. item 3.1 ' . PHP_EOL .
        '     ' . PHP_EOL .
        '     paragraph' . PHP_EOL .
        '     ' . PHP_EOL .
        '     yo' . PHP_EOL .
        PHP_EOL;

    expect($nodeConverter->convert($node))->toBe($expectedText);
});
