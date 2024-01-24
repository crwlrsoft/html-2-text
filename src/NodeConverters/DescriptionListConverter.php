<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Utils;
use Exception;

class DescriptionListConverter extends AbstractBlockElementWithDefaultMarginConverter
{
    public function nodeName(): string
    {
        return 'dl';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        $text = '';

        foreach ($node->node->childNodes as $childNode) {
            if (Utils::isEmptyTextNode($childNode)) {
                continue;
            } elseif ($childNode->nodeName === 'dt') {
                $precedingText = empty($text) ? $node->precedingText : $text;

                $text .= $this->getNodeText(new DomNodeAndPrecedingText($childNode, $precedingText)) . PHP_EOL;
            } elseif ($childNode->nodeName === 'dd') {
                $precedingText = empty($text) ? $node->precedingText : $text;

                $domNodeAndPrecedingText = new DomNodeAndPrecedingText($childNode, $precedingText);

                $text .= $this->indent($this->getNodeText($domNodeAndPrecedingText)) . PHP_EOL;
            }
        }

        return $this->addSpacingBeforeAndAfter($text, $node->precedingText);
    }
}
