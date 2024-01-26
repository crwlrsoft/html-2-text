<?php

namespace tests\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;
use Crwlr\Html2Text\NodeConverters\AbstractBlockElementConverter;
use Crwlr\Html2Text\NodeConverters\AbstractBlockElementWithDefaultMarginConverter;
use Crwlr\Html2Text\NodeConverters\AbstractNodeConverter;

test(
    'when a converter (Html2Text instance) is not set, the getNodeText() method creates a default Html2Text instance' .
    'to get an element\'s text',
    function () {
        $nodeConverter = new class () extends AbstractNodeConverter {
            public function nodeName(): string
            {
                return 'div';
            }

            public function isBlockElement(): bool
            {
                return true;
            }

            public function isBlockElementWithDefaultMargin(): bool
            {
                return false;
            }

            public function isInlineElement(): bool
            {
                return false;
            }

            public function convert(DomNodeAndPrecedingText $node): string
            {
                return $this->getNodeText($node);
            }

            protected function addSpacingBeforeAndAfter(string $textToAdd, string $precedingText): string
            {
                return $textToAdd;
            }
        };

        $node = helper_getElementById('<div id="a">text<ul><li>item</li><li>item2</li></ul><p>par</p></div>', 'a');

        $node = new DomNodeAndPrecedingText($node, '');

        expect($nodeConverter->convert($node))
            ->toBe(<<<TEXT
            text

            * item
            * item2

            par


            TEXT);
    }
);

test(
    'when a converter (Html2Text instance) is set, the getNodeText() method uses it to get an element\'s text',
    function () {
        $nodeConverter = new class () extends AbstractBlockElementConverter {
            public function nodeName(): string
            {
                return 'article';
            }

            public function isBlockElement(): bool
            {
                return true;
            }

            public function isBlockElementWithDefaultMargin(): bool
            {
                return false;
            }

            public function isInlineElement(): bool
            {
                return false;
            }

            public function convert(DomNodeAndPrecedingText $node): string
            {
                return $this->getNodeText($node);
            }

            protected function addSpacingBeforeAndAfter(string $textToAdd, string $precedingText): string
            {
                return $textToAdd;
            }
        };

        $nodeConverter->setConverter(new Html2Text());

        $node = helper_getElementById(
            '<article id="c">text<ul><li>item</li><li>item2</li></ul>text after</article>',
            'c',
        );

        $node = new DomNodeAndPrecedingText($node, '');

        expect($nodeConverter->convert($node))
            ->toBe(<<<TEXT
            text

            * item
            * item2

            text after
            TEXT);
    }
);

it('indents text', function () {
    $nodeConverter = new class () extends AbstractBlockElementWithDefaultMarginConverter {
        private int $indentationLevel = 1;

        public function nodeName(): string
        {
            return 'blockquote';
        }

        public function isBlockElement(): bool
        {
            return false;
        }

        public function isBlockElementWithDefaultMargin(): bool
        {
            return true;
        }

        public function isInlineElement(): bool
        {
            return false;
        }

        public function setIndentationLevel(int $indentationLevel): void
        {
            $this->indentationLevel = $indentationLevel;
        }

        public function convert(DomNodeAndPrecedingText $node): string
        {
            $text = <<<TEXT
                foo
                bar
                baz
                TEXT;

            return $this->indent($text, $this->indentationLevel);
        }

        protected function addSpacingBeforeAndAfter(string $textToAdd, string $precedingText): string
        {
            return $textToAdd;
        }
    };

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(helper_getElementById('<div id="a">b</div>', 'a'), '');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT
          foo
          bar
          baz
        TEXT);

    $nodeConverter->setIndentationLevel(2);

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT
            foo
            bar
            baz
        TEXT);

    $nodeConverter->setConverter(new Html2Text(4));

    $nodeConverter->setIndentationLevel(3);

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT
                    foo
                    bar
                    baz
        TEXT);
});

test(
    'when the isChildOfPreTag property is set to true, the getNodeText() method does not trim and reduce spaces',
    function () {
        $nodeConverter = new class () extends AbstractBlockElementWithDefaultMarginConverter {
            protected bool $isChildOfPreTag = true;

            public function nodeName(): string
            {
                return 'div';
            }

            public function isBlockElement(): bool
            {
                return true;
            }

            public function isBlockElementWithDefaultMargin(): bool
            {
                return false;
            }

            public function isInlineElement(): bool
            {
                return false;
            }

            public function convert(DomNodeAndPrecedingText $node): string
            {
                return $this->addSpacingBeforeAndAfter($this->getNodeText($node), $node->precedingText);
            }
        };

        $node = new DomNodeAndPrecedingText(
            helper_getElementById('<div id="a">' . PHP_EOL . '   b   ' . PHP_EOL . '  </div>', 'a'),
            '',
        );

        expect($nodeConverter->convert($node))
            ->toBe(PHP_EOL . PHP_EOL . PHP_EOL . '   b   ' . PHP_EOL . '  ' . PHP_EOL . PHP_EOL);
    }
);
