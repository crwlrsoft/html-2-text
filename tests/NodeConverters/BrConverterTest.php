<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;

it('converts a br element', function () {
    $nodeConverter = new BrConverter();

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(helper_getElementById('<br id="a">', 'a'), 'hi');

    expect($nodeConverter->convert($node))->toBe(PHP_EOL);
});
