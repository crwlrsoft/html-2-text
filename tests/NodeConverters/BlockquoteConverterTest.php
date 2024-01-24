<?php

namespace tests\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;
use Crwlr\Html2Text\NodeConverters\BlockquoteConverter;

it('correctly converts a blockquote element', function () {
    $nodeConverter = new BlockquoteConverter();

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(
        helper_getElementById('<blockquote id="a">foo<br>bar<br>baz</blockquote>', 'a'),
        'hi',
    );

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT


          foo
          bar
          baz


        TEXT);
});

it('applies the indentation size set in the converter instance (Html2Text)', function () {
    $nodeConverter = new BlockquoteConverter();

    $nodeConverter->setConverter(new Html2Text(6));

    $node = new DomNodeAndPrecedingText(
        helper_getElementById('<blockquote id="a">foo<br>bar<br>baz</blockquote>', 'a'),
        'hi',
    );

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT


              foo
              bar
              baz


        TEXT);
});
