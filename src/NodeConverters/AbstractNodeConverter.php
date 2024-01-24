<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;
use Crwlr\Html2Text\Utils;
use Exception;

abstract class AbstractNodeConverter
{
    protected ?Html2Text $converter = null;

    abstract public function nodeName(): string;

    abstract public function isBlockElement(): bool;

    abstract public function isBlockElementWithDefaultMargin(): bool;

    abstract public function isInlineElement(): bool;

    abstract public function convert(DomNodeAndPrecedingText $node): string;

    abstract protected function addSpacingBeforeAndAfter(string $textToAdd, string $precedingText): string;

    public function setConverter(Html2Text $converter): void
    {
        $this->converter = $converter;
    }

    /**
     * @throws Exception
     */
    protected function getNodeText(DomNodeAndPrecedingText $node): string
    {
        if (Utils::hasOnlyTextNodeChildren($node->node)) {
            return Utils::getNodeText($node->node);
        }

        return $this->converter?->getTextFrom($node->node->childNodes, $node->precedingText) ??
            Utils::getNodeText($node->node);
    }

    protected function getIndendationSize(): int
    {
        return $this->converter?->indentationSize ?? Html2Text::DEFAULT_INDENTATION_SIZE;
    }

    protected function indent(string $text, int $indentationLevel = 1): string
    {
        if ($indentationLevel === 0) {
            return $text;
        }

        $indentationChars = $this->getIndendationSize() * $indentationLevel;

        $lines = explode(PHP_EOL, $text);

        $lineCount = count($lines);

        if ($lineCount === 1) {
            return str_repeat(' ', $indentationChars) . $text;
        }

        foreach ($lines as $index => $line) {
            if ($index !== $lineCount - 1 || trim($line) !== '') {
                $lines[$index] = str_repeat(' ', $indentationChars) . $line;
            }
        }

        return implode(PHP_EOL, $lines);
    }
}
