<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use DOMElement;
use Exception;

class LinkConverter extends AbstractInlineElementConverter
{
    public function nodeName(): string
    {
        return 'a';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        $href = '';

        if ($node->node instanceof DOMElement) {
            $href = trim($node->node->getAttribute('href'));
        }

        if (!empty($href) && !str_starts_with($href, 'mailto:') && !str_starts_with($href, 'tel:')) {
            $addText = '[' . $this->getNodeText($node) . '](' . $href . ')';
        } else {
            $addText = $this->getNodeText($node);
        }

        return $this->addSpacingBeforeAndAfter($addText, $node->precedingText);
    }
}
