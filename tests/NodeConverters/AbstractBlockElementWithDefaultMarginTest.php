<?php

namespace tests\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\NodeConverters\AbstractBlockElementWithDefaultMarginConverter;

function helper_pNodeConverterUsingPrecedingTextAsItsOwn(): AbstractBlockElementWithDefaultMarginConverter
{
    return new class () extends AbstractBlockElementWithDefaultMarginConverter {
        public function nodeName(): string
        {
            return 'p';
        }

        public function convert(DomNodeAndPrecedingText $node): string
        {
            // just use $node->precedingText as text to add, so we have control from the outside of the class.
            return $this->addSpacingBeforeAndAfter($node->precedingText, $node->precedingText);
        }
    };
}

test('the isBlockElementWithDefaultMargin() method returns true, the others false', function () {
    $instance = new class () extends AbstractBlockElementWithDefaultMarginConverter {
        public function nodeName(): string
        {
            return 'test';
        }

        public function convert(DomNodeAndPrecedingText $node): string
        {
            return $this->addSpacingBeforeAndAfter('hi', $node->precedingText);
        }
    };

    expect($instance->isBlockElement())
        ->toBeFalse()
        ->and($instance->isBlockElementWithDefaultMargin())
        ->toBeTrue()
        ->and($instance->isInlineElement())
        ->toBeFalse();
});

it(
    'adds two line breaks before and after, when previous text and text to add do not end with line breaks',
    function () {
        $nodeConverter = helper_pNodeConverterUsingPrecedingTextAsItsOwn();

        $pEl = helper_getElementById('<p id="foo">asdf</p>', 'foo');

        $nodeAndPrecedingText = new DomNodeAndPrecedingText($pEl, 'Pew');

        expect($nodeConverter->convert($nodeAndPrecedingText))->toBe(PHP_EOL . PHP_EOL . 'Pew' . PHP_EOL . PHP_EOL);
    }
);

it('adds one line break before and after, when previous text and text to add, end with a line break', function () {
    $nodeConverter = helper_pNodeConverterUsingPrecedingTextAsItsOwn();

    $pEl = helper_getElementById('<p id="foo">asdf</p>', 'foo');

    $nodeAndPrecedingText = new DomNodeAndPrecedingText($pEl, 'Pew' . PHP_EOL);

    expect($nodeConverter->convert($nodeAndPrecedingText))->toBe(PHP_EOL . 'Pew' . PHP_EOL . PHP_EOL);
});

it('adds no line breaks before and after, when previous text and text to add, end with two line breaks', function () {
    $nodeConverter = helper_pNodeConverterUsingPrecedingTextAsItsOwn();

    $pEl = helper_getElementById('<p id="foo">asdf</p>', 'foo');

    $nodeAndPrecedingText = new DomNodeAndPrecedingText($pEl, 'Pew' . PHP_EOL . PHP_EOL);

    expect($nodeConverter->convert($nodeAndPrecedingText))->toBe('Pew' . PHP_EOL . PHP_EOL);
});
