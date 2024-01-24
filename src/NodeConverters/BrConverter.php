<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;

class BrConverter extends AbstractInlineElementConverter
{
    public function nodeName(): string
    {
        return 'br';
    }

    public function convert(DomNodeAndPrecedingText $node): string
    {
        return PHP_EOL;
    }
}
