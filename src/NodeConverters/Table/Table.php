<?php

namespace Crwlr\Html2Text\NodeConverters\Table;

class Table
{
    /**
     * @var TableRow[]
     */
    private array $rows = [];

    /**
     * @var array<int, int>
     */
    private array $columnLengths = [];

    /**
     * @var array<string, array{ startIndex: int, endIndex: int, length: int }>
     */
    private array $combinedColumnLengths = [];

    public function addRow(TableRow $row): void
    {
        $this->rows[] = $row;

        foreach ($row->cells() as $columnIndex => $cell) {
            if ($cell->colspan > 1) {
                $endIndex = ($columnIndex + $cell->colspan) - 1;

                if (
                    !isset($this->combinedColumnLengths[$columnIndex . '-' . $endIndex]) ||
                    $cell->length > $this->combinedColumnLengths[$columnIndex . '-' . $endIndex]['length']
                ) {
                    $this->combinedColumnLengths[$columnIndex . '-' . $endIndex] = [
                        'startIndex' => $columnIndex,
                        'endIndex' => $endIndex,
                        'length' => $cell->length,
                    ];
                }
            } elseif (
                !isset($this->columnLengths[$columnIndex]) ||
                $cell->length > $this->columnLengths[$columnIndex]
            ) {
                $this->columnLengths[$columnIndex] = $cell->length;
            }
        }
    }

    /**
     * Cells stretching across multiple columns, can have longer content, than the longest contents of all the
     * separate columns combined. This method handles adapting all the provisional column lengths for the
     * final table data.
     *
     * @return void
     */
    public function finalizeColumnLengths(): void
    {
        // First iterate over all the combined columns (colspan > 1), check if some separate columns need to be
        // expanded, so the content of the combined columns fit and add the needed diff to the last separate column
        // in the range of columns.
        foreach ($this->combinedColumnLengths as $data) {
            $actualCombinedLength = $this->getActualCombinedLengthOfColumns($data['startIndex'], $data['endIndex']);

            $lastExistingColumnIndex = $this->getLastExistingColumnIndex($data['startIndex'], $data['endIndex']);

            $borderWidthBetweenColumns = ($data['endIndex'] - $data['startIndex']) * 3;

            if ($actualCombinedLength < $data['length'] && $lastExistingColumnIndex !== null) {
                $diff = ($data['length'] - $actualCombinedLength) - $borderWidthBetweenColumns;

                $this->columnLengths[$lastExistingColumnIndex] += $diff;
            }
        }

        // And then iterate over all the combined columns again, to potentially increase the length of those combined
        // columns, based on the now final lengths of the separate columns.
        foreach ($this->combinedColumnLengths as $key => $data) {
            $actualCombinedLength = $this->getActualCombinedLengthOfColumns($data['startIndex'], $data['endIndex']);

            $borderWidthBetweenColumns = ($data['endIndex'] - $data['startIndex']) * 3;

            if ($actualCombinedLength + $borderWidthBetweenColumns > $data['length']) {
                $this->combinedColumnLengths[$key]['length'] = $actualCombinedLength + $borderWidthBetweenColumns;
            }
        }
    }

    private function getActualCombinedLengthOfColumns(int $startIndex, int $endIndex): int
    {
        $length = 0;

        for ($i = $startIndex; $i <= $endIndex; $i++) {
            if (isset($this->columnLengths[$i])) {
                $length += $this->columnLengths[$i];
            }
        }

        return $length;
    }

    private function getLastExistingColumnIndex(int $startIndex, int $endIndex): ?int
    {
        $lastExistingColumnIndex = null;

        for ($i = $startIndex; $i <= $endIndex; $i++) {
            if (isset($this->columnLengths[$i])) {
                $lastExistingColumnIndex = $i;
            }
        }

        return $lastExistingColumnIndex;
    }

    /**
     * @return TableRow[]
     */
    public function rows(): array
    {
        return $this->rows;
    }

    public function getColumnLength(int $columnIndex): ?int
    {
        return $this->columnLengths[$columnIndex] ?? null;
    }

    public function getCombinedColumnlength(int $startIndex, int $colspan): int
    {
        return $this->combinedColumnLengths[$startIndex . '-' . (($startIndex + $colspan) - 1)]['length'];
    }

    /**
     * @return int[]
     */
    public function allColumnLengths(): array
    {
        return $this->columnLengths;
    }
}
