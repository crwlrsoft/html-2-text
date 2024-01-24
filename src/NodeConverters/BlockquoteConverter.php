<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Exception;

class BlockquoteConverter extends AbstractBlockElementWithDefaultMarginConverter
{
    public function nodeName(): string
    {
        return 'blockquote';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        $addText = $this->indent($this->getNodeText($node));

        return $this->addSpacingBeforeAndAfter($addText, $node->precedingText);
    }
}
