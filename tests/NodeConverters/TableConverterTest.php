<?php

namespace tests\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Html2Text;
use Crwlr\Html2Text\NodeConverters\TableConverter;

it('correctly converts a table', function () {
    $nodeConverter = new TableConverter();

    $nodeConverter->setConverter(new Html2Text());

    $html = <<<HTML
        <table id="a">
        <tr><th>Column1</th><th>Column2</th><th>Column3</th><th>Column4</th><th>Column5</th></tr>
        <tr><td>value1</td><td>value2</td><td>value3</td><td>value4</td><td>value5</td></tr>
        <tr><td>value1</td><td>value2</td><td>value3</td><td>value4</td><td>value5</td></tr>
        <tr><td>value1</td><td>value2</td><td>value3</td><td>value4</td><td>value5</td></tr>
        </table>
        HTML;

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT

        | Column1 | Column2 | Column3 | Column4 | Column5 |
        | value1  | value2  | value3  | value4  | value5  |
        | value1  | value2  | value3  | value4  | value5  |
        | value1  | value2  | value3  | value4  | value5  |

        TEXT);
});

it('correctly converts a table with thead and tbody sections', function () {
    $nodeConverter = new TableConverter();

    $nodeConverter->setConverter(new Html2Text());

    $html = <<<HTML
        <table id="a">
        <thead>
        <tr><th>Column1</th><th>Column2</th><th>Column3</th><th>Column4</th><th>Column5</th></tr>
        </thead>
        <tbody>
        <tr><td>value1</td><td>value2</td><td>value3</td><td>value4</td><td>value5</td></tr>
        <tr><td>value1</td><td>value2</td><td>value3</td><td>value4</td><td>value5</td></tr>
        <tr><td>value1</td><td>value2</td><td>value3</td><td>value4</td><td>value5</td></tr>
        </tbody>
        </table>
        HTML;

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT

        | Column1 | Column2 | Column3 | Column4 | Column5 |
        | ------- | ------- | ------- | ------- | ------- |
        | value1  | value2  | value3  | value4  | value5  |
        | value1  | value2  | value3  | value4  | value5  |
        | value1  | value2  | value3  | value4  | value5  |

        TEXT);
});

it('correctly sizes the columns', function () {
    $nodeConverter = new TableConverter();

    $nodeConverter->setConverter(new Html2Text());

    $html = <<<HTML
        <table id="a">
        <thead>
        <tr><th>Column1</th><th>Column2</th><th>Column3</th><th>Column4</th><th>Column5</th></tr>
        </thead>
        <tbody>
        <tr><td>short</td><td>the longest</td><td>short</td><td>a pretty long value</td><td>hello world</td></tr>
        <tr><td>longer</td><td>short</td><td>not so short</td><td>lorem ipsum</td><td>this library is awesome</td></tr>
        <tr><td>this is the longest</td><td>short</td><td>this is very, very long</td><td>hi</td><td>yeah</td></tr>
        </tbody>
        </table>
        HTML;

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT

        | Column1             | Column2     | Column3                 | Column4             | Column5                 |
        | ------------------- | ----------- | ----------------------- | ------------------- | ----------------------- |
        | short               | the longest | short                   | a pretty long value | hello world             |
        | longer              | short       | not so short            | lorem ipsum         | this library is awesome |
        | this is the longest | short       | this is very, very long | hi                  | yeah                    |

        TEXT);
});

it('correctly handles cells with a colspan attribute', function () {
    $nodeConverter = new TableConverter();

    $nodeConverter->setConverter(new Html2Text());

    $html = <<<HTML
        <table id="a">
        <thead>
        <tr><th>Column1</th><th>Column2</th><th>Column3</th><th>Column4</th><th>Column5</th></tr>
        </thead>
        <tbody>
        <tr><td>value1</td><td colspan="2">value2+3</td><td>value4</td><td>value5</td></tr>
        <tr><td>value1</td><td>value2</td><td colspan="3">value3+4+5</td></tr>
        <tr><td colspan="4">value1+2+3+4</td><td>value5</td></tr>
        </tbody>
        </table>
        HTML;

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT

        | Column1 | Column2 | Column3 | Column4 | Column5 |
        | ------- | ------- | ------- | ------- | ------- |
        | value1  | value2+3          | value4  | value5  |
        | value1  | value2  | value3+4+5                  |
        | value1+2+3+4                          | value5  |

        TEXT);
});

it('correctly handles colspan columns, when their content is longer than the separate columns combined', function () {
    $nodeConverter = new TableConverter();

    $nodeConverter->setConverter(new Html2Text());

    $html = <<<HTML
        <table id="a">
        <thead>
        <tr><th>Column1</th><th>Column2</th><th>Column3</th><th>Column4</th><th>Column5</th></tr>
        </thead>
        <tbody>
        <tr><td>value1</td><td>value2</td><td>value3</td><td>value4</td><td>value5</td></tr>
        <tr><td>value1</td><td colspan="2">value2 and value 3 combined</td><td>value4</td><td>value5</td></tr>
        <tr><td>value1</td><td>value2</td><td colspan="3">value 3 and value 4 and value 5 in one column</td></tr>
        <tr><td colspan="2">value 1 and value 2</td><td>value3</td><td>value4</td><td>value5</td></tr>
        </tbody>
        </table>
        HTML;

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT

        | Column1 | Column2   | Column3           | Column4 | Column5         |
        | ------- | --------- | ----------------- | ------- | --------------- |
        | value1  | value2    | value3            | value4  | value5          |
        | value1  | value2 and value 3 combined   | value4  | value5          |
        | value1  | value2    | value 3 and value 4 and value 5 in one column |
        | value 1 and value 2 | value3            | value4  | value5          |

        TEXT);
});

it('handles columns containing line breaks, by removing the line breaks', function () {
    $nodeConverter = new TableConverter();

    $nodeConverter->setConverter(new Html2Text());

    $html = <<<HTML
        <table id="a">
        <tr><th>Column1</th><th>Column2</th><th>Column3</th><th>Column4</th><th>Column5</th></tr>
        <tr><td>value1</td><td>value2</td><td>value3<br>foo<br>bar</td><td>value4</td><td>value5</td></tr>
        <tr><td>value1</td><td>value2</td><td>value3</td><td>value4</td><td>value5</td></tr>
        </table>
        HTML;

    $node = new DomNodeAndPrecedingText(helper_getElementById($html, 'a'), 'hi');

    expect($nodeConverter->convert($node))
        ->toBe(<<<TEXT

        | Column1 | Column2 | Column3        | Column4 | Column5 |
        | value1  | value2  | value3 foo bar | value4  | value5  |
        | value1  | value2  | value3         | value4  | value5  |

        TEXT);
});
