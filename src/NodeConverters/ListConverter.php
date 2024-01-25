<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Utils;
use DOMNode;
use Exception;

abstract class ListConverter extends AbstractBlockElementWithDefaultMarginConverter
{
    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        return $this->addSpacingBeforeAndAfter($this->getListText($node), $node->precedingText);
    }

    abstract protected function getListItemStart(int $listItemNumber): string;

    /**
     * @param DomNodeAndPrecedingText $ulNode
     * @param int $indentationLevel
     * @return string
     * @throws Exception
     */
    private function getListText(DomNodeAndPrecedingText $ulNode, int $indentationLevel = 0): string
    {
        $text = '';

        $listItemNumber = 1;

        foreach ($ulNode->node->childNodes as $bulletPoint) {
            if ($bulletPoint->nodeName === 'li' || $bulletPoint->nodeName === $this->nodeName()) {
                $precedingText = empty($text) ? $ulNode->precedingText : $text;

                $bulletPointNode = new DomNodeAndPrecedingText($bulletPoint, $precedingText);

                if ($bulletPoint->nodeName === 'li') {
                    if ($this->containsNestedList($bulletPoint)) {
                        $text .= $this->indent($this->getNestedListingText($bulletPoint, $listItemNumber), $indentationLevel);
                    } else {
                        $textToAdd = $this->indentMultiLineTextInListElement(
                            $this->getNodeText($bulletPointNode),
                            $listItemNumber,
                        );

                        $text .= $this->indent(
                            $this->getListItemStart($listItemNumber) . ltrim($textToAdd) . PHP_EOL,
                            $indentationLevel,
                        );
                    }

                    $listItemNumber++;
                } elseif ($bulletPoint->nodeName === $this->nodeName()) {
                    $text .= $this->getListText($bulletPointNode, $indentationLevel + 1);
                }
            }
        }

        return $text;
    }

    protected function containsNestedList(DOMNode $bulletPoint): bool
    {
        foreach ($bulletPoint->childNodes as $childNode) {
            if ($childNode->nodeName === $this->nodeName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    protected function getNestedListingText(DOMNode $bulletPoint, int $listItemNumber): string
    {
        $listItemStart = $this->getListItemStart($listItemNumber);

        $multiLineIndentation = strlen($listItemStart);

        $nestedListingText = $listItemStart;

        foreach ($bulletPoint->childNodes as $childNode) {
            if (Utils::isEmptyTextNode($childNode)) {
                continue;
            }

            $childNodeAndPrecedingText = new DomNodeAndPrecedingText($childNode, $nestedListingText);

            if ($childNode->nodeName === $this->nodeName()) {
                $nestedListingText .= rtrim($this->getListText($childNodeAndPrecedingText, 1)) . PHP_EOL;
            } else {
                $nodeText = $this->getConverter()->getTextFrom($childNode, $nestedListingText);

                if ($nestedListingText !== $listItemStart) {
                    $nestedListingText .= str_repeat(' ', $multiLineIndentation);
                }

                $nestedListingText .= ltrim($nodeText) . PHP_EOL;
            }
        }

        return $nestedListingText;
    }

    protected function indentMultiLineTextInListElement(string $text, int $listItemNumber): string
    {
        if (!str_contains($text, PHP_EOL)) {
            return $text;
        }

        $multiLineIndentation = strlen($this->getListItemStart($listItemNumber));

        $lines = explode(PHP_EOL, $text);

        $lineCount = count($lines);

        foreach ($lines as $index => $line) {
            if ($index === $lineCount - 1 && trim($line) === '') {
                unset($lines[$index]);
            } elseif ($index > 0) {
                $lines[$index] = str_repeat(' ', $multiLineIndentation) . $line;
            }
        }

        return implode(PHP_EOL, $lines);
    }
}
