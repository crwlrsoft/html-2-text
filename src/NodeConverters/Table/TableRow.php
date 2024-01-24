<?php

namespace Crwlr\Html2Text\NodeConverters\Table;

class TableRow
{
    /**
     * @param bool $isHeadingRow
     * @param TableCell[] $cells
     * @param int $nextCellIndex
     * @param int $columnCount      Actual number of columns in this row, ignoring colspan attributes.
     * @param int $realColumnCount  Number of columns considering colspan.
     */
    public function __construct(
        public readonly bool $isHeadingRow,
        private array $cells = [],
        private int $nextCellIndex = 0,
        private int $columnCount = 0,
        private int $realColumnCount = 0,
    ) {}

    public function addCell(TableCell $cell): void
    {
        $this->cells[$this->nextCellIndex] = $cell;

        $this->nextCellIndex += $cell->colspan;

        $this->columnCount += 1;

        $this->realColumnCount += $cell->colspan;
    }

    /**
     * @return TableCell[]
     */
    public function cells(): array
    {
        return $this->cells;
    }

    public function columnCount(): int
    {
        return $this->columnCount;
    }

    public function realColumnCount(): int
    {
        return $this->realColumnCount;
    }
}
