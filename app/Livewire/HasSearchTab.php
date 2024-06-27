<?php
namespace App\Livewire;

use App\Models\SearchTable;
use App\Models\SearchTableIndex;
use App\Models\TableIndex;
use function in_array;
use function is_null;
use function optional;
use function trim;

trait HasSearchTab
{
    public $searchTableInfo = ['id' => 0, 'color' => null, 'row' => 0, 'column' => 0, 'verical_column' => 1];

    public $stringSearch = '';

    public function collectDataSearchTable()
    {
        $this->stringSearch = '';

        if ($this->searchTableInfo['id'] === 0) {
            $this->__loadSearchTable();
        }

        $table = SearchTable::where('id', $this->searchTableInfo['id'])
            ->with('indexs')->first();

        $indexs = $table->indexs
            ->whereNotNull('symbol')
            ->groupBy('column');
        $string = '';
        foreach ($indexs as $columnIndex => $column) {
            if ($column->first()->row != 1) {
                continue;
            }
            foreach ($column as $index => $cell) {
                $prevCell = $column[$index - 1] ?? optional();
                if ($index !== 0 && $this->shouldStopCollect($cell, $prevCell)) {
                    $cell = $prevCell;
                    $cell->color = $this->getTrueColor($column, $cell, $index);
                    break 1;
                }
                $string .= $cell->symbol . $cell->color;
                $cell->color = $this->getTrueColor($column, $cell, $index);
            }
            $string .= $this->collectHorizonSearchData($table ,$cell->row, $cell->column, $cell->color).'__';
        }
        return $this->stringSearch = trim($string, '__');
    }

    public function collectHorizonSearchData(SearchTable $table, $rowIndex, $beginColumn, $color)
    {
        $string = '';
        $data = $table->indexs
            ->where('row', $rowIndex)
            ->where('column', '>', $beginColumn)
            ->where('vertical_column', '=', $beginColumn)
            ->filter(function ($item) use ($color) {
                return ($item->symbol == 'O' && $item->color == $color) || ($item->symbol == 'X');
            })
            ->sortBy('column');

        if (!$data->isEmpty() && ($data->first()->column - 1) != $beginColumn ) {
            return '';
        }

        foreach ($data->groupBy('column') as $columnIndex => $column) {
            foreach ($column as $index => $cell) {
                $string .= $cell->symbol . $cell->color;
            }
        }
        return $string;
    }

    public function fillSearchColor($color): void
    {
        $symbol = in_array($color, ['red', 'blue']) ? 'O' : 'X';
        if ($this->searchTableInfo['id'] == 0) {
            $this->__loadSearchTable();
        }

        if (is_null($color)) {
            $symbol = null;
        }

        // The most recent row, column filled
        $rowIndex = $this->searchTableInfo['row'] + 1;
        $columnIndex = $this->searchTableInfo['column'] ?: 1;
        $tableId = $this->searchTableInfo['id'];

        if ($this->__shouldNextColumn($color)) {
            $this->searchTableInfo['verical_column'] = $this->searchTableInfo['verical_column'] + 1;
            $columnIndex = $this->searchTableInfo['verical_column'];
            $rowIndex = 1;
        }else if ($this->__nextRowBlocked()) {
            $columnIndex = $columnIndex + 1;
            if ($this->__isFillHorizon($columnIndex)) {
                $verticalColumn = $this->searchTableInfo['verical_column'];
                $rowIndex = $this->searchTableInfo['row'];
            }
        }

        $table = SearchTableIndex::where([
            'table_id' => $tableId,
            'row' => $rowIndex > $this->maxRow ? $this->maxRow : $rowIndex,
            'column' => $columnIndex
        ])->first();

        if ($table !== null) {
            $dataUpdate = [
                'symbol' => $symbol,
                'color' => $color,
            ];
            if (isset($verticalColumn)) {
                $dataUpdate['vertical_column'] = $verticalColumn;
            }
            $table->update($dataUpdate);

            $this->__updateSearchTable($rowIndex, $columnIndex, $color);
        }
    }

    private function __updateSearchTable($rowIndex, $columnIndex, $color): void
    {
        $this->searchTableInfo['row'] = $rowIndex > $this->maxRow ? $this->maxRow : $rowIndex;
        $this->searchTableInfo['column'] = $columnIndex;
        $this->searchTableInfo['color'] = $color !== 'green' ? $color :  $this->searchTableInfo['color'];
    }

    private function __isFillHorizon($columnIndex): bool
    {
        return $columnIndex !== $this->searchTableInfo['verical_column'];
    }
    private function __nextRowBlocked()
    {
        $rowIndex = ($this->searchTableInfo['row'] + 1);
        $columnIndex = $this->searchTableInfo['column'] ?: 1;
        $tableId = $this->searchTableInfo['id'];

        if ($rowIndex > 6) {
            return true;
        }

        if ($this->__isFillHorizon($columnIndex)) {
            // is fill color is horizon
            return true;
        }

        return SearchTableIndex::where([
            'table_id' => $tableId,
            'row' => $rowIndex,
            'column' => $columnIndex,
        ])
            ->whereNotNull(['symbol', 'color'])
            ->first();
    }

    private function __shouldNextColumn($color)
    {
        return $this->searchTableInfo['color'] && $this->searchTableInfo['color'] !== $color && $color !== 'green';
    }

    private function __loadSearchTable()
    {
        $searchTable = SearchTable::with('indexs')
            ->select(['id'])
            ->first();
       if ($searchTable) {
           $this->searchTableInfo['id'] = $searchTable->id;
       }
    }

    public function resetSearchTable()
    {
        if ($this->searchTableInfo['id'] === 0) {
            $this->__loadSearchTable();
        }

        SearchTableIndex::where('table_id', $this->searchTableInfo['id'])
            ->update([
                'symbol' => null,
                'color' => null,
                'vertical_column' => null,
            ]);

        $this->reset('searchTableInfo');
    }

}

