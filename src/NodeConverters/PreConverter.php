<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Utils;
use DOMNode;
use Exception;

class PreConverter extends AbstractBlockElementWithDefaultMarginConverter
{
    public function nodeName(): string
    {
        return 'pre';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        return $this->addSpacingBeforeAndAfter($this->getTextFromChildNodes($node->node), $node->precedingText);
    }

    /**
     * @throws Exception
     */
    private function getTextFromChildNodes(DOMNode $node): string
    {
        $text = '';

        foreach ($node->childNodes as $childNode) {
            /** @var DOMNode $childNode */
            if (Utils::isTextNode($childNode)) {
                $text .= $childNode->textContent;
            } elseif (Utils::hasOnlyTextNodeChildren($childNode)) {
                $converter = $this->getConverter()->getNodeConverter($childNode);

                $converter->isChildOfPreTag = true;

                $text .= $converter->convert(new DomNodeAndPrecedingText($childNode, $text));
            } else {
                $text .= $this->getTextFromChildNodes($childNode);
            }
        }

        return $text;
    }
}
