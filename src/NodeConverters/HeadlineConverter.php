<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Exception;

class HeadlineConverter extends AbstractBlockElementWithDefaultMarginConverter
{
    public function nodeName(): string
    {
        return 'h1';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        $addText = $this->getHashes($node) . $this->getNodeText($node);

        return $this->addSpacingBeforeAndAfter($addText, $node->precedingText);
    }

    protected function getHashes(DomNodeAndPrecedingText $node): string
    {
        return match ($node->node->nodeName) {
            'h1' => '# ',
            'h2' => '## ',
            'h3' => '### ',
            'h4' => '#### ',
            'h5' => '##### ',
            'h6' => '###### ',
            default => '',
        };
    }
}
