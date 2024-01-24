<?php

namespace Crwlr\Html2Text\Aggregates;

use DOMNode;

class DomNodeAndPrecedingText
{
    public function __construct(public readonly DOMNode $node, public readonly string $precedingText) {}
}
