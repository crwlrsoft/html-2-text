<?php

namespace Crwlr\Html2Text;

use DOMNode;

class Utils
{
    /**
     * @var string[]
     */
    protected static array $blockElementsWithDefaultMargin = [
        'dl',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'ol',
        'p',
        'ul',
    ];

    /**
     * @var string[]
     */
    protected static array $blockElements = [
        'address',
        'article',
        'aside',
        'blockquote',
        'canvas',
        'dd',
        'div',
        'dt',
        'fieldset',
        'figcaption',
        'figure',
        'footer',
        'form',
        'header',
        'hr',
        'li',
        'main',
        'nav',
        'noscript',
        'pre',
        'section',
        'table',
        'tfoot',
        'video',
    ];

    public static function isBlockElementWithDefaultMargin(DOMNode $node): bool
    {
        return in_array($node->nodeName, self::$blockElementsWithDefaultMargin, true);
    }

    public static function isBlockElement(DOMNode $node): bool
    {
        return self::isBlockElementWithDefaultMargin($node) || in_array($node->nodeName, self::$blockElements, true);
    }

    public static function isInlineElement(DOMNode $node): bool
    {
        return !self::isBlockElementWithDefaultMargin($node) && !self::isBlockElement($node);
    }

    public static function getNodeText(DOMNode $node): string
    {
        $text = preg_replace('/\s+/', ' ', $node->textContent);

        if (!is_string($text)) {
            return '';
        }

        return $text;
    }

    public static function hasOnlyTextNodeChildren(DOMNode $node): bool
    {
        foreach ($node->childNodes as $childNode) {
            if (!self::isTextNode($childNode)) {
                return false;
            }
        }

        return true;
    }

    public static function isTextNode(DOMNode $node): bool
    {
        return $node->nodeType === XML_TEXT_NODE;
    }

    public static function isEmptyTextNode(DOMNode $node): bool
    {
        return self::isTextNode($node) && trim($node->textContent) === '';
    }

    public static function isNonEmptyTextNode(DOMNode $node): bool
    {
        return self::isTextNode($node) && trim($node->textContent) !== '';
    }

    public static function returnLinebreakIfPrecedingTextDoesNotEndWith(string $text): string
    {
        return strlen($text) === 0 || substr($text, -1, 1) === PHP_EOL ? '' : PHP_EOL;
    }

    public static function returnUpToTwoLineBreaksIfPrecedingTextDoesNotEndWith(string $text): string
    {
        $strlen = strlen($text);

        if ($strlen === 0) {
            return PHP_EOL . PHP_EOL;
        } elseif ($strlen === 1) {
            return $text === PHP_EOL ? PHP_EOL : PHP_EOL . PHP_EOL;
        }

        $lastChar = substr($text, -1, 1);

        if ($lastChar === PHP_EOL && substr($text, -2, 1) === PHP_EOL) {
            return '';
        } elseif ($lastChar === PHP_EOL) {
            return PHP_EOL;
        }

        return PHP_EOL . PHP_EOL;
    }

    public static function getXSpaces(int $number = 0): string
    {
        if ($number === 0) {
            return '';
        }

        return str_repeat(' ', $number);
    }
}
