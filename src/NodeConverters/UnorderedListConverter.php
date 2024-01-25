<?php

namespace Crwlr\Html2Text\NodeConverters;

class UnorderedListConverter extends ListConverter
{
    public function nodeName(): string
    {
        return 'ul';
    }

    protected function getListItemStart(int $listItemNumber): string
    {
        return '* ';
    }
}
