<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;

it('converts a link with non empty href to markdown notation link', function () {
    $nodeConverter = new LinkConverter();

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(
        helper_getElementById('<a id="a" href="https://www.crwlr.software/packages">hi <strong>test</strong></a>', 'a'),
        'hi',
    );

    expect($nodeConverter->convert($node))->toBe('[hi TEST](https://www.crwlr.software/packages)');
});

it('does not convert an empty link', function () {
    $nodeConverter = new LinkConverter();

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(helper_getElementById('<a id="a">hi <strong>test</strong></a>', 'a'), 'hi');

    expect($nodeConverter->convert($node))->toBe('hi TEST');
});

it('does not convert a link when href is starting with mailto:', function () {
    $nodeConverter = new LinkConverter();

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(
        helper_getElementById('<a id="a" href="mailto:someone@example.com">email</a>', 'a'),
        'foo',
    );

    expect($nodeConverter->convert($node))->toBe('email');
});

it('does not convert a link when href is starting with tel:', function () {
    $nodeConverter = new LinkConverter();

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(
        helper_getElementById('<a id="a" href="tel:+499123456789">09123 456789</a>', 'a'),
        'foo',
    );

    expect($nodeConverter->convert($node))->toBe('09123 456789');
});
