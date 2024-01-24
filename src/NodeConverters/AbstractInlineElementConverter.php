<?php

namespace Crwlr\Html2Text\NodeConverters;

abstract class AbstractInlineElementConverter extends AbstractNodeConverter
{
    public function isBlockElement(): bool
    {
        return false;
    }

    public function isBlockElementWithDefaultMargin(): bool
    {
        return false;
    }

    public function isInlineElement(): bool
    {
        return true;
    }

    protected function addSpacingBeforeAndAfter(string $textToAdd, string $precedingText): string
    {
        if ($precedingText === '') {
            return ltrim($textToAdd);
        }

        $lastCharBefore = substr($precedingText, -1, 1);

        if ($lastCharBefore === PHP_EOL || $lastCharBefore === ' ') {
            return ltrim($textToAdd);
        }

        return $textToAdd;
    }
}
