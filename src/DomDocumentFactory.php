<?php

namespace Crwlr\Html2Text;

use Crwlr\Html2Text\Exceptions\InvalidHtmlException;
use DOMDocument;
use Exception;
use Masterminds\HTML5;
use ValueError;

/**
 * The code in this class was mainly copied from the symfony DomCrawler component.
 * Thank you for the great work!
 *
 * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Component/DomCrawler/Crawler.php#L131
 */

class DomDocumentFactory
{
    /**
     * @throws InvalidHtmlException
     */
    public static function make(string $content): DOMDocument
    {
        return (new self())->makeFromHtmlString($content);
    }

    /**
     * @throws InvalidHtmlException
     */
    public function makeFromHtmlString(string $content): DOMDocument
    {
        try {
            $charset = $this->getCharset($content);

            $content = $this->fixCharsetInContent($content, $charset);

            if ($this->canParseHtml5String($content)) {
                return $this->parseHtml5($content, $charset);
            }

            return $this->parseXhtml($content, $charset);
        } catch (Exception $exception) {
            throw new InvalidHtmlException('Invalid HTML: ' . $exception->getMessage(), previous: $exception);
        }
    }

    private function getCharset(string $content): string
    {
        return preg_match('//u', $content) ? 'UTF-8' : 'ISO-8859-1';
    }

    /**
     * http://www.w3.org/TR/encoding/#encodings
     * http://www.w3.org/TR/REC-xml/#NT-EncName
     *
     * @param string $content
     * @param string $charset
     * @return string
     * @throws Exception
     */
    private function fixCharsetInContent(string $content, string &$charset): string
    {
        $fixedContent = preg_replace_callback(
            '/(charset *= *["\']?)([a-zA-Z\-0-9_:.]+)/i',
            function ($match) use (&$charset) {
                if ('charset=' === $this->convertToHtmlEntities('charset=', $match[2])) {
                    $charset = $match[2];
                }

                return $match[1] . $charset;
            },
            $content,
            1,
        );

        if (!is_string($fixedContent)) {
            return $content;
        }

        return $fixedContent;
    }

    private function canParseHtml5String(string $content): bool
    {
        if (false === ($pos = stripos($content, '<!doctype html>'))) {
            return false;
        }

        $header = substr($content, 0, $pos);

        return '' === $header || $this->isValidHtml5Heading($header);
    }

    private function isValidHtml5Heading(string $heading): bool
    {
        return 1 === preg_match('/^\x{FEFF}?\s*(<!--[^>]*?-->\s*)*$/u', $heading);
    }

    /**
     * @throws Exception
     */
    private function parseHtml5(string $htmlContent, string $charset = 'UTF-8'): DOMDocument
    {
        $parser = new HTML5(['disable_html_ns' => true]);

        return $parser->parse($this->convertToHtmlEntities($htmlContent, $charset));
    }

    /**
     * @throws Exception
     */
    private function parseXhtml(string $htmlContent, string $charset = 'UTF-8'): DOMDocument
    {
        $htmlContent = $this->convertToHtmlEntities($htmlContent, $charset);

        $internalErrors = libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', $charset);

        $dom->validateOnParse = true;

        if (trim($htmlContent) !== '') {
            @$dom->loadHTML($htmlContent);
        }

        libxml_use_internal_errors($internalErrors);

        return $dom;
    }

    /**
     * @throws Exception
     */
    private function convertToHtmlEntities(string $htmlContent, string $charset = 'UTF-8'): string
    {
        set_error_handler(static fn() => throw new Exception());

        try {
            return mb_encode_numericentity($htmlContent, [0x80, 0x10FFFF, 0, 0x1FFFFF], $charset);
        } catch (Exception|ValueError) {
            $htmlContent = iconv($charset, 'UTF-8', $htmlContent);

            if ($htmlContent === false) {
                throw new Exception('Charset problem');
            }

            return mb_encode_numericentity($htmlContent, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');
        } finally {
            restore_error_handler();
        }
    }
}
