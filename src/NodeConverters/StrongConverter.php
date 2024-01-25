<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;

class StrongConverter extends AbstractInlineElementConverter
{
    public function nodeName(): string
    {
        return 'strong';
    }

    public function convert(DomNodeAndPrecedingText $node): string
    {
        $addText = strtoupper($this->getNodeText($node));

        return $this->addSpacingBeforeAndAfter($addText, $node->precedingText);
    }
}
