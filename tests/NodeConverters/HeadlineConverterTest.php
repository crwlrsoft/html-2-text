<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;

it('converts all the different headline levels', function (string $level, string $hashes) {
    $nodeConverter = new HeadlineConverter();

    $nodeConverter->setConverter(new Html2Text());

    $node = new DomNodeAndPrecedingText(
        helper_getElementById('<' . $level . ' id="a">Headline</' . $level . '>', 'a'),
        'hi',
    );

    expect($nodeConverter->convert($node))
        ->toBe(PHP_EOL . PHP_EOL . $hashes . 'Headline' . PHP_EOL . PHP_EOL);
})->with([
    ['h1', '# '],
    ['h2', '## '],
    ['h3', '### '],
    ['h4', '#### '],
    ['h5', '##### '],
    ['h6', '###### '],
]);
