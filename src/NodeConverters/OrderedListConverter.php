<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Exception;

class OrderedListConverter extends AbstractBlockElementWithDefaultMarginConverter
{
    public function nodeName(): string
    {
        return 'ol';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        return $this->addSpacingBeforeAndAfter($this->getListText($node), $node->precedingText);
    }

    /**
     * @param DomNodeAndPrecedingText $olNode
     * @param int $indentationLevel
     * @return string
     * @throws Exception
     */
    private function getListText(DomNodeAndPrecedingText $olNode, int $indentationLevel = 0): string
    {
        $text = '';

        $number = 1;

        foreach ($olNode->node->childNodes as $bulletPoint) {
            if ($bulletPoint->nodeName === 'li' || $bulletPoint->nodeName === 'ol') {
                $precedingText = empty($text) ? $olNode->precedingText : $text;

                $bulletPointNode = new DomNodeAndPrecedingText($bulletPoint, $precedingText);

                if ($bulletPoint->nodeName === 'li') {
                    $textToAdd = $this->indentMultiLineTextInListElement($this->getNodeText($bulletPointNode), $number);

                    $text .= $this->indent($number . '. ' . $textToAdd . PHP_EOL, $indentationLevel);

                    $number++;
                } elseif ($bulletPoint->nodeName === 'ol') {
                    $text .= $this->getListText($bulletPointNode, $indentationLevel + 1);
                }
            }
        }

        return $text;
    }

    protected function indentMultiLineTextInListElement(string $text, int $listNumber): string
    {
        if (!str_contains($text, PHP_EOL)) {
            return $text;
        }

        $additionalIndentSize = strlen($listNumber . '. ');

        $lines = explode(PHP_EOL, $text);

        $lineCount = count($lines);

        foreach ($lines as $index => $line) {
            if ($index === $lineCount - 1 && trim($line) === '') {
                unset($lines[$index]);
            } elseif ($index > 0) {
                $lines[$index] = str_repeat(' ', $additionalIndentSize) . $line;
            }
        }

        return implode(PHP_EOL, $lines);
    }
}
