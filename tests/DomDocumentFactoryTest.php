<?php

namespace tests;

use Crwlr\Html2Text\DomDocumentFactory;
use DOMDocument;

it('creates a DOMDocument object from an HTML string', function () {
    $html = <<<HTML
        <!DOCTYPE html>
        <html lang="de"><head>
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
