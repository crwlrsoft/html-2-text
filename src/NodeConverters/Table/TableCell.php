<?php

namespace Crwlr\Html2Text\NodeConverters\Table;

class TableCell
{
    public readonly int $length;

    public function __construct(
        public readonly int $colspan,
        public readonly string $text
    ) {
        $this->length = mb_strlen($this->text);
    }
}
