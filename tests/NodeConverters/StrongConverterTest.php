<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;

it('converts text in a strong tag to uppercase', function () {
    $nodeConverter = new StrongConverter();

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(helper_getElementById('<strong id="a">test</strong>', 'a'), 'hi');

    expect($nodeConverter->convert($node))->toBe('TEST');
});

it('converts text in a b tag to uppercase', function () {
    $nodeConverter = new StrongConverter();

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(helper_getElementById('<b id="a">foo</b>', 'a'), 'hi');

    expect($nodeConverter->convert($node))->toBe('FOO');
});
