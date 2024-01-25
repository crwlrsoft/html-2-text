<?php

namespace tests;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;
use Crwlr\Html2Text\NodeConverters\AbstractBlockElementWithDefaultMarginConverter;
use Exception;

class SpanConverter extends AbstractBlockElementWithDefaultMarginConverter
{
    public function nodeName(): string
    {
        return 'span';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        return $this->addSpacingBeforeAndAfter($this->getNodeText($node), $node->precedingText);
    }
}

it('correctly converts the all known tags example HTML file', function () {
    $fileContent = file_get_contents(__DIR__ . '/html/all-known-tags.html');

    if ($fileContent === false) {
        $fileContent = '';
    }

    $text = Html2Text::convert($fileContent);

    expect($text)
        ->not()
        ->toContain('Example website title')
        ->not()
        ->toContain('console.log(\'test\');')
        ->not()
        ->toContain('#app { background-color: #fff; }')
        ->not()
        ->toContain('SVG Title')
        ->not()
        ->toContain('SVG Text')
        ->not()
        ->toContain(PHP_EOL . PHP_EOL . PHP_EOL) // more than two consecutive line breaks
        ->toContain('# Hello World!')
        ->toContain('## And hello all the other planets!')
        ->toContain('John Doe' . PHP_EOL . 'Box 564, Disneyland' . PHP_EOL . 'USA' . PHP_EOL . 'email')
        ->toContain(<<<TEXT
        ## Article Headline

        some article text

        ### A Subheading

        TEXT)
        ->toContain(<<<TEXT

        * list item
        * another list item
        * and one more list item
        * test nesting
          * second level
          * asdf
            * third level
          test

        TEXT)
        ->toContain(<<<TEXT

        1. ordered list item
        2. item
        3. another item
          1. ol item
          2. ol item two
          3. ol item three
            1. one
            2. two
            3. three

        TEXT)
        ->toContain(PHP_EOL . '  This is text inside' . PHP_EOL . '  a blockquote tag')
        ->toContain(<<<TABLE
        | column 1 | column 2 | column 3             | column 4 | column 5 | column 6 |
        | -------- | -------- | -------------------- | -------- | -------- | -------- |
        | value 1  | value 2 + 3 plus some more text | value 4  | value 5  | value 6  |
        | value 1  | value 2  | value 3              | value 4 + 5 + 6                |
        | value 1  | value 2  | value 3              | value 4  | value 5  | value 6  |
        TABLE)
        ->toContain(<<<DL
        Beast of Bodmin
          A large feline inhabiting Bodmin Moor.
        Morgawr
          A sea serpent.
        Owlman
          A giant owl-like creature.
        DL)
        ->toContain('Some paragraph with STRONG and B tags and [a link](https://www.example.com).');
});

test('you can add a custom converter for a particular tag', function () {
    $nodeConverter = new class () extends AbstractBlockElementWithDefaultMarginConverter {
        public function nodeName(): string
        {
            return 'span';
        }

        public function convert(DomNodeAndPrecedingText $node): string
        {
            return $this->addSpacingBeforeAndAfter($this->getNodeText($node), $node->precedingText);
        }
    };

    $html = '<p>Hello <span>World</span>!</p>';

    $converter = new Html2Text();

    $converter->addConverter($nodeConverter);

    expect($converter->convertHtmlToText($html))->toBe(<<<TEXT
    Hello

    World

    !
    TEXT);
});

test('you can add a converter by just passing a class name', function () {
    $html = '<p>Hello <span>World</span>!</p>';

    $converter = new Html2Text();

    $converter->addConverter('span', SpanConverter::class);

    expect($converter->convertHtmlToText($html))->toBe(<<<TEXT
    Hello

    World

    !
    TEXT);
});

it('removes a node converter for a particular tag', function () {
    $converter = new Html2Text();

    $converter->removeConverter('br');

    $html = '<div>Lorem <br> ipsum</div>';

    expect($converter->convertHtmlToText($html))->toBe('Lorem ipsum');
});

test('you can add elements to skip', function () {
    $converter = new Html2Text();

    $converter->skipElement('div');

    $html = '<p>foo</p><div>bar</div><p>baz</p>';

    expect($converter->convertHtmlToText($html))->toBe('foo' . PHP_EOL . PHP_EOL . 'baz');
});

test('you can remove elements from the list of elements to be skipped', function () {
    $converter = new Html2Text();

    $converter->dontSkipElement('style');

    $html = '<div>Hello World!</div><style>body { background-color: #f7f7f7; }</style>';

    expect($converter->convertHtmlToText($html))
        ->toBe('Hello World!' . PHP_EOL . 'body { background-color: #f7f7f7; }');
});
