<?php

namespace tests\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;
use Crwlr\Html2Text\NodeConverters\DescriptionListConverter;

it('correctly converts a description list element', function () {
    $nodeConverter = new DescriptionListConverter();

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(
        helper_getElementById('<dl id="a"><dt>foo</dt><dd>bar</dd><dt>baz</dt><dd>quz</dd></dl>', 'a'),
        'hi',
    );

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT


        foo
          bar
        baz
          quz


        TEXT);
});

it('applies the indentation size set in the converter instance (Html2Text)', function () {
    $nodeConverter = new DescriptionListConverter();

    $nodeConverter->setConverter(new Html2Text(4));

    $node = new DomNodeAndPrecedingText(
        helper_getElementById('<dl id="a"><dt>foo</dt><dd>bar</dd><dt>baz</dt><dd>quz</dd></dl>', 'a'),
        'hi',
    );

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT


        foo
            bar
        baz
            quz


        TEXT);
});
