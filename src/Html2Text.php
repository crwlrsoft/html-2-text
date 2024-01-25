<?php

namespace Crwlr\Html2Text;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\Exceptions\InvalidHtmlException;
use Crwlr\Html2Text\NodeConverters\AbstractNodeConverter;
use Crwlr\Html2Text\NodeConverters\BlockquoteConverter;
use Crwlr\Html2Text\NodeConverters\BrConverter;
use Crwlr\Html2Text\NodeConverters\DescriptionListConverter;
use Crwlr\Html2Text\NodeConverters\FallbackBlockElementConverter;
use Crwlr\Html2Text\NodeConverters\FallbackBlockElementWithDefaultMarginConverter;
use Crwlr\Html2Text\NodeConverters\FallbackInlineElementConverter;
use Crwlr\Html2Text\NodeConverters\HeadlineConverter;
use Crwlr\Html2Text\NodeConverters\LinkConverter;
use Crwlr\Html2Text\NodeConverters\OrderedListConverter;
use Crwlr\Html2Text\NodeConverters\StrongConverter;
use Crwlr\Html2Text\NodeConverters\TableConverter;
use Crwlr\Html2Text\NodeConverters\UnorderedListConverter;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use Exception;
use InvalidArgumentException;

class Html2Text
{
    public const DEFAULT_INDENTATION_SIZE = 2;

    /**
     * @var string[]
     */
    private array $skipElements = [
        'head',
        'script',
        'style',
        'canvas',
        'svg',
        'img',
        'video',
    ];

    /**
     * @var string[]
     */
    private array $blockElements = [
        'address',
        'article',
        'aside',
        'blockquote',
        'canvas',
        'dd',
        'div',
        'dl',
        'dt',
        'fieldset',
        'figcaption',
        'figure',
        'footer',
        'form',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'header',
        'hr',
        'li',
        'main',
        'nav',
        'noscript',
        'ol',
        'p',
        'pre',
        'section',
        'table',
        'tfoot',
        'ul',
        'video',
    ];

    /**
     * @var array<string, string>
     */
    protected array $converters = [];

    /**
     * @var array<string, AbstractNodeConverter>
     */
    protected array $converterInstances = [];

    public function __construct(readonly public int $indentationSize = self::DEFAULT_INDENTATION_SIZE)
    {
        $this->converters = [
            'ul' => UnorderedListConverter::class,
            'ol' => OrderedListConverter::class,
            'br' => BrConverter::class,
            'blockquote' => BlockquoteConverter::class,
            'table' => TableConverter::class,
            'dl' => DescriptionListConverter::class,
            'strong' => StrongConverter::class,
            'b' => StrongConverter::class,
            'a' => LinkConverter::class,
            'h1' => HeadlineConverter::class,
            'h2' => HeadlineConverter::class,
            'h3' => HeadlineConverter::class,
            'h4' => HeadlineConverter::class,
            'h5' => HeadlineConverter::class,
            'h6' => HeadlineConverter::class,
        ];
    }

    /**
     * @param DOMDocument|DOMNodeList<DOMNode>|string $html
     * @param int $indentationSize
     * @return string
     * @throws InvalidHtmlException
     */
    public static function convert(
        DOMDocument|DOMNodeList|string $html,
        int $indentationSize = self::DEFAULT_INDENTATION_SIZE
    ): string {
        return (new self($indentationSize))->convertHtmlToText($html);
    }

    /**
     * @param DOMDocument|DOMNodeList<DOMNode>|string $html
     * @return string
     * @throws InvalidHtmlException
     * @throws Exception
     */
    public function convertHtmlToText(DOMDocument|DOMNodeList|string $html): string
    {
        if (is_string($html)) {
            $html = DomDocumentFactory::make($html);
        }

        $text = $this->getTextFrom($html instanceof DOMDocument ? $html->childNodes : $html);

        return $this->normalizeWhitespace($text);
    }

    public function addConverter(
        string|AbstractNodeConverter $nodeNameOrConverterInstance,
        string|AbstractNodeConverter $converterClassNameOrInstance = null
    ): self {
        if (is_string($nodeNameOrConverterInstance) && $converterClassNameOrInstance === null) {
            throw new InvalidArgumentException(
                'When the first argument to this Html2Text::addConverter() is a node name, the second one must ' .
                'eiter be a class name or a AbstractNodeConverter instance.'
            );
        }

        if (is_string($nodeNameOrConverterInstance)) {
            if (is_string($converterClassNameOrInstance)) {
                $this->converters[$nodeNameOrConverterInstance] = $converterClassNameOrInstance;

                if (isset($this->converterInstances[$nodeNameOrConverterInstance])) {
                    unset($this->converterInstances[$nodeNameOrConverterInstance]);
                }
            } else {
                $this->converters[$nodeNameOrConverterInstance] = $converterClassNameOrInstance::class;

                $converterClassNameOrInstance->setConverter($this);

                $this->converterInstances[$nodeNameOrConverterInstance] = $converterClassNameOrInstance;
            }
        } else {
            $this->converters[$nodeNameOrConverterInstance->nodeName()] = $nodeNameOrConverterInstance::class;

            $nodeNameOrConverterInstance->setConverter($this);

            $this->converterInstances[$nodeNameOrConverterInstance->nodeName()] = $nodeNameOrConverterInstance;
        }

        return $this;
    }

    public function removeConverter(string $nodeName): self
    {
        if (array_key_exists($nodeName, $this->converters)) {
            unset($this->converters[$nodeName]);
        }

        if (array_key_exists($nodeName, $this->converterInstances)) {
            unset($this->converterInstances[$nodeName]);
        }

        return $this;
    }

    public function skipElement(string $nodeName): self
    {
        if (!in_array($nodeName, $this->skipElements, true)) {
            $this->skipElements[] = $nodeName;
        }

        return $this;
    }

    public function dontSkipElement(string $nodeName): self
    {
        if (in_array($nodeName, $this->skipElements, true)) {
            $key = array_search($nodeName, $this->skipElements, true);

            unset($this->skipElements[$key]);
        }

        return $this;
    }

    /**
     * @param DOMNodeList<DOMNode>|DOMNode $nodeOrNodeList
     * @param string $precedingText
     * @return string
     * @throws Exception
     */
    public function getTextFrom(DOMNode|DOMNodeList $nodeOrNodeList, string $precedingText = ''): string
    {
        $text = '';

        if ($nodeOrNodeList instanceof DOMNodeList) {
            foreach ($nodeOrNodeList as $node) {
                if ($this->isSkipElement($node)) {
                    continue;
                }

                $converter = $this->getConverter($node);

                $text .= $converter->convert(new DomNodeAndPrecedingText($node, empty($text) ? $precedingText : $text));
            }
        } elseif (!$this->isSkipElement($nodeOrNodeList)) {
            $converter = $this->getConverter($nodeOrNodeList);

            $text .= $converter->convert(new DomNodeAndPrecedingText($nodeOrNodeList, $precedingText));
        }

        return $text;
    }

    private function isSkipElement(DOMNode $node): bool
    {
        return $node->nodeType === XML_COMMENT_NODE ||
            Utils::isEmptyTextNode($node) ||
            in_array($node->nodeName, $this->skipElements, true);
    }

    /**
     * @throws Exception
     */
    private function getConverter(DOMNode $node): AbstractNodeConverter
    {
        if (isset($this->converters[$node->nodeName])) {
            if (isset($this->converterInstances[$node->nodeName])) {
                return $this->converterInstances[$node->nodeName];
            }

            $converter = new $this->converters[$node->nodeName]();

            if (!$converter instanceof AbstractNodeConverter) {
                throw new Exception('Invalid converter class name. Class must be an instance of AbstractNodeConverter');
            }

            $converter->setConverter($this);

            $this->converterInstances[$node->nodeName] = $converter;

            return $converter;
        }

        if ($this->isBlockElementWithMargin($node)) {
            $converter = new FallbackBlockElementWithDefaultMarginConverter();
        } elseif ($this->isBlockElement($node)) {
            $converter = new FallbackBlockElementConverter();
        } else {
            $converter = new FallbackInlineElementConverter();
        }

        $converter->setConverter($this);

        return $converter;
    }

    private function isBlockElement(DOMNode $node): bool
    {
        return in_array($node->nodeName, $this->blockElements, true);
    }

    private function isBlockElementWithMargin(DOMNode $node): bool
    {
        return in_array($node->nodeName, ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true);
    }

    private function normalizeWhitespace(string $text): string
    {
        // Regex description: match sequences with more than two consecutive line breaks.
        // Between them there can be spaces, but nothing else.
        // And the sequence has to end with a line break, so we don't remove indentation.
        $newLineFixedText = preg_replace('/(?:\n\s*){2}\n+/m', "\n\n", $text);

        if ($newLineFixedText === null) {
            return $text;
        }

        $fixedText = '';

        foreach (explode(PHP_EOL, $newLineFixedText) as $line) {
            if ($line !== null) {
                $fixedText .= rtrim($line) . PHP_EOL;
            }
        }

        return trim($fixedText);
    }
}
