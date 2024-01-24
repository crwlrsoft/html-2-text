<?php

namespace tests\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;
use Crwlr\Html2Text\NodeConverters\UnorderedListConverter;

function helper_getUnorderedListExampleHtml(): string
{
    return <<<HTML
        <ul id="a">
            <li>item 1</li>
            <li>item 2</li>
            <ul>
                <li>item 2.1</li>
                <li>item 2.2</li>
                <li>item 2.3</li>
                <ul>
                    <li>item 2.3.1</li>
                    <li>item 2.3.2</li>
                </ul>
            </ul>
            <li>item 3</li>
        </ul>
        HTML;
}

it('correctly converts an unordered list element', function () {
    $nodeConverter = new UnorderedListConverter();

    $nodeConverter->setConverter(new Html2Text());

    $html = helper_getUnorderedListExampleHtml();

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT


        * item 1
        * item 2
          * item 2.1
          * item 2.2
          * item 2.3
            * item 2.3.1
            * item 2.3.2
        * item 3


        TEXT);
});

it('applies the indentation size set in the converter instance (Html2Text)', function () {
    $nodeConverter = new UnorderedListConverter();

    $nodeConverter->setConverter(new Html2Text(5));

    $html = helper_getUnorderedListExampleHtml();

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT


        * item 1
        * item 2
             * item 2.1
             * item 2.2
             * item 2.3
                  * item 2.3.1
                  * item 2.3.2
        * item 3


        TEXT);
});

it('correctly converts list elements containing a block element', function () {
    $nodeConverter = new UnorderedListConverter();

    $nodeConverter->setConverter(new Html2Text());

    $html = <<<HTML
        <ul id="a">
            <li>item</li>
            <li>item 2 <p>paragraph</p> <p>foo</p></li>
            <li>item 3</li>
            <ul>
                <li>item 3.1 <p>paragraph</p> yo</li>
            </ul>
        </ul>
        HTML;

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    // This is because PhpStorm removes trailing spaces inside heredoc :/
    $expectedText = PHP_EOL . PHP_EOL .
        '* item' . PHP_EOL .
        '* item 2 ' . PHP_EOL .
        '  ' . PHP_EOL .
        '  paragraph' . PHP_EOL .
        '  ' . PHP_EOL .
        '  foo' . PHP_EOL .
        '  ' . PHP_EOL .
        '* item 3' . PHP_EOL .
        '  * item 3.1 ' . PHP_EOL .
        '    ' . PHP_EOL .
        '    paragraph' . PHP_EOL .
        '    ' . PHP_EOL .
        '    yo' . PHP_EOL .
        PHP_EOL;

    expect($nodeConverter->convert($node))->toBe($expectedText);
});
