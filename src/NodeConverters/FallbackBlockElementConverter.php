<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Exception;

class FallbackBlockElementConverter extends AbstractBlockElementConverter
{
    public function nodeName(): string
    {
        return '*';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        return $this->addSpacingBeforeAndAfter($this->getNodeText($node), $node->precedingText);
    }
}
