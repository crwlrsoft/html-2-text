<?php

namespace Crwlr\Html2Text\NodeConverters;

class OrderedListConverter extends ListConverter
{
    public function nodeName(): string
    {
        return 'ol';
    }

    protected function getListItemStart(int $listItemNumber): string
    {
        return $listItemNumber . '. ';
    }
}
