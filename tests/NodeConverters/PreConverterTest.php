<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;

it('keeps line breaks an spaces in text inside a pre tag', function () {
    $nodeConverter = new PreConverter();

    $html = <<<HTML
        <pre id="a">

            test
                foo

            <div>
                <strong>test</strong>
            </div>

            <span>  bar  </span>

            quz<br>
            lorem ipsum
            <a href="/foo">link</a>

            <p>

                baz

            </p>
        </pre>
        HTML;

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    // This is because PhpStorm removes trailing spaces inside heredoc :/
    $expectedText = PHP_EOL .
        PHP_EOL .
        PHP_EOL .
        PHP_EOL .
        '    test' . PHP_EOL .
        '        foo' . PHP_EOL .
        PHP_EOL .
        '    ' . PHP_EOL .
        '        TEST' . PHP_EOL .
        '    ' . PHP_EOL .
        PHP_EOL .
        '      bar  ' . PHP_EOL .
        PHP_EOL .
        '    quz' . PHP_EOL .
        PHP_EOL .
        '    lorem ipsum' . PHP_EOL .
        '    [link](/foo)' . PHP_EOL .
        PHP_EOL .
        '    ' . PHP_EOL .
        PHP_EOL .
        PHP_EOL .
        PHP_EOL .
        '        baz' . PHP_EOL .
        PHP_EOL .
        '    ' . PHP_EOL .
        PHP_EOL .
        PHP_EOL;

    expect($nodeConverter->convert($node))->toBe($expectedText);
});
