<?php

namespace tests;

use Crwlr\Html2Text\DomDocumentFactory;
use Crwlr\Html2Text\Utils;
use DOMElement;

it('gets the text from a DOMNode', function () {
    $document = DomDocumentFactory::make(<<<HTML
        <!DOCTYPE html>
        <html lang="de">
        <head>
        <title>test</title>
        </head>
        <body>
        <div id="element">

              Lorem    ipsum    dolor

              sit    amet

        </div>
        </body>
        </html>
        HTML);

    $node = $document->getElementById('element');

    expect($node)->toBeInstanceOf(DOMElement::class);

    /** @var DOMElement $node */

    $text = Utils::getNodeText($node);

    expect($text)->toBe(' Lorem ipsum dolor sit amet ');
});

it('tells if a node has only text node children', function () {
    $html = '<div id="el-1">hello</div><ul id="list"><li>item</li><li>item</li></ul>';

    $el1 = helper_getElementById($html, 'el-1');

    expect(Utils::hasOnlyTextNodeChildren($el1))->toBeTrue();

    $list = helper_getElementById($html, 'list');

    expect(Utils::hasOnlyTextNodeChildren($list))->toBeFalse();
});

it('tells you if something is an empty text node', function () {
    $document = helper_makeDom(<<<HTML
        <div id="element">

            <p id="paragraph">test</p>
        </div>
        HTML);

    $textNode = helper_getFirstTextNodeInId($document, 'element');

    expect(Utils::isEmptyTextNode($textNode))->toBeTrue();

    $paragraphTextNode = helper_getFirstTextNodeInId($document, 'paragraph');

    expect(Utils::isEmptyTextNode($paragraphTextNode))->toBeFalse();
});

it('returns a line break if the preceding text does not end with a line break', function () {
    expect(Utils::returnLinebreakIfPrecedingTextDoesNotEndWith('asdf' . PHP_EOL))->toBe('');

    expect(Utils::returnLinebreakIfPrecedingTextDoesNotEndWith('asdf'))->toBe(PHP_EOL);
});

it('returns up to two line breaks it the preceding text does not end with two line breaks', function () {
    expect(Utils::returnUpToTwoLineBreaksIfPrecedingTextDoesNotEndWith('asdf' . PHP_EOL . PHP_EOL))->toBe('');

    expect(Utils::returnUpToTwoLineBreaksIfPrecedingTextDoesNotEndWith('asdf'))->toBe(PHP_EOL . PHP_EOL);

    expect(Utils::returnUpToTwoLineBreaksIfPrecedingTextDoesNotEndWith('asdf' . PHP_EOL . '-' . PHP_EOL))->toBe(PHP_EOL);
});

it('returns a particular number of space characters', function (int $number, string $expectedSpaces) {
    expect(Utils::getXSpaces($number))->toBe($expectedSpaces);
})->with([
    [0, ''],
    [3, '   '],
    [10, '          '],
]);
