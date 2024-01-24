<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Utils;

abstract class AbstractBlockElementConverter extends AbstractNodeConverter
{
    public function isBlockElement(): bool
    {
        return true;
    }

    public function isBlockElementWithDefaultMargin(): bool
    {
        return false;
    }

    public function isInlineElement(): bool
    {
        return false;
    }

    protected function addSpacingBeforeAndAfter(string $textToAdd, string $precedingText): string
    {
        return Utils::returnLinebreakIfPrecedingTextDoesNotEndWith($precedingText) .
            $textToAdd .
            Utils::returnLinebreakIfPrecedingTextDoesNotEndWith($textToAdd);
    }
}
