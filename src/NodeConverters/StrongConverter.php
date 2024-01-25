<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Exception;

class StrongConverter extends AbstractInlineElementConverter
{
    public function nodeName(): string
    {
        return 'strong';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        $addText = strtoupper($this->getNodeText($node));

        return $this->addSpacingBeforeAndAfter($addText, $node->precedingText);
    }
}
