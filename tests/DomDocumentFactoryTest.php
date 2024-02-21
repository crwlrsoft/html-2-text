<?php

namespace tests;

use Crwlr\Html2Text\DomDocumentFactory;
use DOMDocument;

it('creates a DOMDocument object from an HTML string', function () {
    $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en"><head>
        <title>test</title>
        </head>
        <body id="page">
        <h1>Hello World!</h1>
        <p>Lorem ipsum</p>
        </body>
        </html>
        HTML;

    $document = DomDocumentFactory::make($html);

    expect($document)->toBeInstanceOf(DOMDocument::class)
        ->and($document->getElementById('page')?->textContent)
        ->toContain('Hello World!' . PHP_EOL . 'Lorem ipsum');
});

it(
    'correctly parses documents containing something that can be interpreted as a charset definition within a ' .
    'script block',
    function () {
        $html = <<<HTML
<body>
    <div>bla bla bla asdf test foo</div>

    <script>
        var someVar = ['foo', 'bar'];
        var someObject = {};
        someObject.charset = someVar[1];
    </script>
</body>
HTML;

        $document = DomDocumentFactory::make($html);

        expect($document)->toBeInstanceOf(DOMDocument::class);
    }
);
