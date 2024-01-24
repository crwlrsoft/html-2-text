<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Exception;

class UnorderedListConverter extends AbstractBlockElementWithDefaultMarginConverter
{
    public function nodeName(): string
    {
        return 'ul';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        return $this->addSpacingBeforeAndAfter($this->getListText($node), $node->precedingText);
    }

    /**
     * @param DomNodeAndPrecedingText $ulNode
     * @param int $indentationLevel
     * @return string
     * @throws Exception
     */
    private function getListText(DomNodeAndPrecedingText $ulNode, int $indentationLevel = 0): string
    {
        $text = '';

        foreach ($ulNode->node->childNodes as $bulletPoint) {
            if ($bulletPoint->nodeName === 'li' || $bulletPoint->nodeName === 'ul') {
                $precedingText = empty($text) ? $ulNode->precedingText : $text;

                $bulletPointNode = new DomNodeAndPrecedingText($bulletPoint, $precedingText);

                if ($bulletPoint->nodeName === 'li') {
                    $textToAdd = $this->indentMultiLineTextInListElement($this->getNodeText($bulletPointNode));

                    $text .= $this->indent('* ' . $textToAdd . PHP_EOL, $indentationLevel);
                } elseif ($bulletPoint->nodeName === 'ul') {
                    $text .= $this->getListText($bulletPointNode, $indentationLevel + 1);
                }
            }
        }

        return $text;
    }

    protected function indentMultiLineTextInListElement(string $text): string
    {
        if (!str_contains($text, PHP_EOL)) {
            return $text;
        }

        $lines = explode(PHP_EOL, $text);

        $lineCount = count($lines);

        foreach ($lines as $index => $line) {
            if ($index === $lineCount - 1 && trim($line) === '') {
                unset($lines[$index]);
            } elseif ($index > 0) {
                $lines[$index] = '  ' . $line;
            }
        }

        return implode(PHP_EOL, $lines);
    }
}
