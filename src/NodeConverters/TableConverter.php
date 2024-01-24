<?php

namespace Crwlr\Html2Text\NodeConverters;

use Crwlr\Html2Text\Aggregates\DomNodeAndPrecedingText;
use Crwlr\Html2Text\NodeConverters\Table\Table;
use Crwlr\Html2Text\NodeConverters\Table\TableCell;
use Crwlr\Html2Text\NodeConverters\Table\TableRow;
use Crwlr\Html2Text\Utils;
use DOMElement;
use DOMNode;
use Exception;

class TableConverter extends AbstractBlockElementConverter
{
    public function nodeName(): string
    {
        return 'table';
    }

    /**
     * @throws Exception
     */
    public function convert(DomNodeAndPrecedingText $node): string
    {
        $tableData = $this->readTable($node->node);

        return $this->addSpacingBeforeAndAfter($this->tableDataToText($tableData), $node->precedingText);
    }

    protected function tableDataToText(Table $table): string
    {
        $text = '';

        $prevRowWasHeadingRow = false;

        foreach ($table->rows() as $row) {
            if ($prevRowWasHeadingRow && $row->isHeadingRow === false) {
                $text .= '|';

                foreach ($table->allColumnLengths() as $length) {
                    $text .= ' ' . str_repeat('-', $length) . ' |';
                }

                $text .= PHP_EOL;

                $prevRowWasHeadingRow = false;
            }

            $text .= '|';

            foreach ($row->cells() as $columnIndex => $cellData) {
                $columnLength = $table->getColumnLength($columnIndex);

                if ($cellData->colspan > 1) {
                    $columnLength = $table->getCombinedColumnlength($columnIndex, $cellData->colspan);
                }

                $fillSpaceChars = $columnLength - $cellData->length;

                $text .= ' ' . $cellData->text . ($fillSpaceChars > 0 ? str_repeat(' ', $fillSpaceChars) : '') . ' |';
            }

            $text .= PHP_EOL;

            if ($row->isHeadingRow) {
                $prevRowWasHeadingRow = true;
            }
        }

        return $text;
    }

    /**
     * @throws Exception
     */
    protected function readTable(DOMNode $table): Table
    {
        $tableData = new Table();

        foreach ($table->childNodes as $childNode) {
            if (Utils::isEmptyTextNode($childNode)) {
                continue;
            } elseif ($childNode->nodeName === 'thead') {
                $this->addRowsFromThead($childNode, $tableData);
            } elseif ($childNode->nodeName === 'tbody') {
                $this->addRowsFromTbody($childNode, $tableData);
            } elseif ($childNode->nodeName === 'tr') {
                $tableData->addRow($this->getTableRow($childNode));
            }
        }

        $tableData->finalizeColumnLengths();

        return $tableData;
    }

    /**
     * @throws Exception
     */
    protected function addRowsFromThead(DOMNode $thead, Table $table): Table
    {
        foreach ($thead->childNodes as $childNode) {
            if (Utils::isEmptyTextNode($childNode)) {
                continue;
            } elseif ($childNode->nodeName === 'tr') {
                $table->addRow($this->getTableRow($childNode, true));
            }
        }

        return $table;
    }

    /**
     * @throws Exception
     */
    protected function addRowsFromTbody(DOMNode $tbody, Table $table): Table
    {
        foreach ($tbody->childNodes as $childNode) {
            if (Utils::isEmptyTextNode($childNode)) {
                continue;
            } elseif ($childNode->nodeName === 'tr') {
                $table->addRow($this->getTableRow($childNode));
            }
        }

        return $table;
    }

    /**
     * @throws Exception
     */
    protected function getTableRow(DOMNode $tr, bool $isHeadingRow = false): TableRow
    {
        $row = new TableRow($isHeadingRow);

        foreach ($tr->childNodes as $childNode) {
            if (Utils::isEmptyTextNode($childNode)) {
                continue;
            } elseif ($childNode->nodeName === 'td' || $childNode->nodeName === 'th') {
                $row->addCell($this->getTableCell($childNode));
            }
        }

        return $row;
    }

    /**
     * @throws Exception
     */
    protected function getTableCell(DOMElement|DOMNode $td): TableCell
    {
        if (!method_exists($td, 'getAttribute')) {
            $colspan = 1;
        } else {
            $colspan = trim($td->getAttribute('colspan'));

            $colspan = empty($colspan) ? 1 : (int) $colspan;
        }

        $node = new DomNodeAndPrecedingText($td, '');

        return new TableCell($colspan, $this->removeLineBreaksFromTdText($this->getNodeText($node)));
    }

    protected function removeLineBreaksFromTdText(string $text): string
    {
        return preg_replace('/\s+/', ' ', $text) ?? $text;
    }
}
