<?php

namespace tests\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\NodeConverters\AbstractBlockElementConverter;

function helper_divNodeConverterUsingPrecedingTextAsItsOwn(): AbstractBlockElementConverter
{
    return new class extends AbstractBlockElementConverter {
        public function nodeName(): string
        {
            return 'div';
        }

        public function convert(DomNodeAndPrecedingText $node): string
        {
            // just use $node->precedingText as text to add, so we have control from the outside of the class.
            return $this->addSpacingBeforeAndAfter($node->precedingText, $node->precedingText);
        }
    };
}

test('the isBlockElement() method returns true, the others false', function () {
    $instance = new class extends AbstractBlockElementConverter {
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
        ->toBeTrue()
        ->and($instance->isBlockElementWithDefaultMargin())
        ->toBeFalse()
        ->and($instance->isInlineElement())
        ->toBeFalse();
});

it(
    'adds line breaks before and after the added text when preceding text and added text do not end with a line break',
    function () {
        $nodeConverter = helper_divNodeConverterUsingPrecedingTextAsItsOwn();

        $divEl = helper_getElementById('<div id="foo">Hi</div>', 'foo');

        $nodeAndPrecedingText = new DomNodeAndPrecedingText($divEl, 'Lorem ipsum');

        expect($nodeConverter->convert($nodeAndPrecedingText))->toBe(PHP_EOL . 'Lorem ipsum' . PHP_EOL);
    },
);

it(
    'does not add line breaks before and after the added text when preceding text and added text end with a line break',
    function () {
        $nodeConverter = helper_divNodeConverterUsingPrecedingTextAsItsOwn();

        $divEl = helper_getElementById('<div id="foo">Hi</div>', 'foo');

        $nodeAndPrecedingText = new DomNodeAndPrecedingText($divEl, 'Lorem ipsum' . PHP_EOL);

        // return value has a line break at the end, because the added text has. If the addSpacingBeforeAndAfter()
        // would have added a line break, there would be two.
        expect($nodeConverter->convert($nodeAndPrecedingText))->toBe('Lorem ipsum' . PHP_EOL);
    },
);
