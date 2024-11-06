<?php

namespace tests\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\NodeConverters\AbstractInlineElementConverter;

test('the isInlineElement() method returns true, the others false', function () {
    $instance = new class extends AbstractInlineElementConverter {
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
        ->toBeFalse()
        ->and($instance->isInlineElement())
        ->toBeTrue();
});

it('trims the text on the left side when the preceding text is empty', function () {
    $nodeConverter = new class extends AbstractInlineElementConverter {
        public function nodeName(): string
        {
            return 'span';
        }

        public function convert(DomNodeAndPrecedingText $node): string
        {
            return $this->addSpacingBeforeAndAfter($this->getNodeText($node), $node->precedingText);
        }
    };

    $spanEl = helper_getElementById('<span id="hi"> test </span>', 'hi');

    $nodeAndPrecedingText = new DomNodeAndPrecedingText($spanEl, '');

    expect($nodeConverter->convert($nodeAndPrecedingText))->toBe('test ');
});

it('trims the text on the left side when the preceding text ends with a line break', function () {
    $nodeConverter = new class extends AbstractInlineElementConverter {
        public function nodeName(): string
        {
            return 'span';
        }

        public function convert(DomNodeAndPrecedingText $node): string
        {
            return $this->addSpacingBeforeAndAfter($this->getNodeText($node), $node->precedingText);
        }
    };

    $spanEl = helper_getElementById('<span id="hi"> test </span>', 'hi');

    $nodeAndPrecedingText = new DomNodeAndPrecedingText($spanEl, 'foo' . PHP_EOL);

    expect($nodeConverter->convert($nodeAndPrecedingText))->toBe('test ');
});

it('trims the text on the left side when the preceding text ends with a space', function () {
    $nodeConverter = new class extends AbstractInlineElementConverter {
        public function nodeName(): string
        {
            return 'span';
        }

        public function convert(DomNodeAndPrecedingText $node): string
        {
            return $this->addSpacingBeforeAndAfter($this->getNodeText($node), $node->precedingText);
        }
    };

    $spanEl = helper_getElementById('<span id="hi"> yo </span>', 'hi');

    $nodeAndPrecedingText = new DomNodeAndPrecedingText($spanEl, 'bar ');

    expect($nodeConverter->convert($nodeAndPrecedingText))->toBe('yo ');
});

it('does not trim the text on the left side when the preceding text ends with something else', function () {
    $nodeConverter = new class extends AbstractInlineElementConverter {
        public function nodeName(): string
        {
            return 'span';
        }

        public function convert(DomNodeAndPrecedingText $node): string
        {
            return $this->addSpacingBeforeAndAfter($this->getNodeText($node), $node->precedingText);
        }
    };

    $spanEl = helper_getElementById('<span id="hi"> yep </span>', 'hi');

    $nodeAndPrecedingText = new DomNodeAndPrecedingText($spanEl, 'barbara');

    expect($nodeConverter->convert($nodeAndPrecedingText))->toBe(' yep ');
});

it('does not trim the text when the isChildOfPreTag property is true', function () {
    $nodeConverter = new class extends AbstractInlineElementConverter {
        protected bool $isChildOfPreTag = true;

        public function nodeName(): string
        {
            return 'span';
        }

        public function convert(DomNodeAndPrecedingText $node): string
        {
            return $this->addSpacingBeforeAndAfter($this->getNodeText($node), $node->precedingText);
        }
    };

    $spanEl = helper_getElementById('<span id="hi">   hey   </span>', 'hi');

    $nodeAndPrecedingText = new DomNodeAndPrecedingText($spanEl, '-');

    expect($nodeConverter->convert($nodeAndPrecedingText))->toBe('   hey   ');
});
