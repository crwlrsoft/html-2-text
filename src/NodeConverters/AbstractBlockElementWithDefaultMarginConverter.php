<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Utils;

abstract class AbstractBlockElementWithDefaultMarginConverter extends AbstractNodeConverter
{
    public function isBlockElement(): bool
    {
        return false;
    }

    public function isBlockElementWithDefaultMargin(): bool
    {
        return true;
    }

    public function isInlineElement(): bool
    {
        return false;
    }

    protected function addSpacingBeforeAndAfter(string $textToAdd, string $precedingText): string
    {
        return Utils::returnUpToTwoLineBreaksIfPrecedingTextDoesNotEndWith($precedingText) .
            $textToAdd .
            Utils::returnUpToTwoLineBreaksIfPrecedingTextDoesNotEndWith($textToAdd);
    }
}
