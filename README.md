<p align="center"><a href="https://www.crwlr.software" target="_blank"><img src="https://github.com/crwlrsoft/graphics/blob/eee6cf48ee491b538d11b9acd7ee71fbcdbe3a09/crwlr-logo.png" alt="crwlr.software logo" width="260"></a></p>

# HTML to formatted Plain Text

This easy-to-use package helps you to convert HTML to formatted plain text.

## Demo

```php
use Crwlr\Html2Text\Html2Text;

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><title>Example Website Title</title></head>
<body>
    <script>console.log('test');</script>
    <style>#app { background-color: #fff; }</style>
    <article>
        <h1>Article Headline</h1>
        <h2>A Subheading</h2>

        <p>Some text containing <a href="https://www.crwl.io">a link</a>.</p>

        <ul>
            <li>list item</li>
            <li>another list item</li>
            <li>and one more
                <ul>
                    <li>second level
                        <ul>
                            <li>third level</li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>

        <table>
            <thead>
            <tr><th>column 1</th><th>column 2</th><th>column 3</th></tr>
            </thead>
            <tbody>
            <tr><td>value 1</td><td>value 2</td><td>value 3</td></tr>
            <tr><td>value 1</td><td colspan="2">value 2 + 3</td></tr>
            <tr><td colspan="2">value 1 and 2</td><td>value 3</td></tr>
            <tr><td>value 1</td><td>value 2</td><td>value 3</td></tr>
            </tbody>
        </table>
    </article>
</body>
</html>
HTML;

$text = Html2Text::convert($html);
```

__The resulting text:__
```bash
# Article Headline

## A Subheading

Some text containing [a link](https://www.crwl.io).

* list item
* another list item
* and one more
  * second level
    * third level

| column 1 | column 2 | column 3 |
| -------- | -------- | -------- |
| value 1  | value 2  | value 3  |
| value 1  | value 2 + 3         |
| value 1 and 2       | value 3  |
| value 1  | value 2  | value 3  |
```

## Documentation

You can find the full documentation at [crwlr.software](https://www.crwlr.software/packages/html-2-text/getting-started).

## Contributing

If you consider contributing something to this package, read the [contribution guide (CONTRIBUTING.md)](CONTRIBUTING.md).
