# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.1] - 2024-02-21
### Fixed
- An issue that occurred when the HTML contains something that looks like a charset definition within a `<script>` block.

## [0.1.0] - 2024-01-26
### Added
- `Html2Text` class that converts HTML to formatted plain text.
- `DomDocumentFactory` to get a `DOMDocument` from a string.
- The concept of node converters: if you want to change how a certain element is converted to text, you can build a custom node converter for that element and add it to the `Html2Text` class (`Html2Text::addConverter()`). This will also replace an existing converter for that element type. You can also just remove an existing node converter without providing a new one, by calling `Html2Text::removeConverter()`.
- Functionality to control which elements are skipped (`Html2Text::skipElement()`, `Html2Text::dontSkipElement()`).

Not every element has its own converter yet. For example form elements was definitely not a priority for the first development release. The goal is to have all standard HTML elements covered until v1.0. Until then, elements without a special converter are just handled according to their text flow behavior: block (with or without default margin in the browser) or inline.
