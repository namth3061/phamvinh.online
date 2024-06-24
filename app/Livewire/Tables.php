<?php

namespace App\Livewire;

use App\Models\SearchTable;
use App\Models\SearchTableIndex;
use App\Models\Table;
use App\Models\TableIndex;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use function array_flip;
use function array_is_list;
use function array_unshift;
use function collect;
use function count;
use function implode;
use function optional;
use function strpos;

class Tables extends Component
{
    use HasSearchTab;

    public $maxRow = 6;

    public $columnsIndex = '';

    public $searchIds = [];

    public $selectedTable = ['id' => 0, 'color' => null, 'row' => 0, 'column' => 0, 'verical_column' => 1];

    public function mount()
    {
        $this->resetSearchTable();

    }

    public function resetTable()
    {
        if ($this->selectedTable['id'] !== 0) {
            TableIndex::where('table_id', $this->selectedTable['id'])
                ->update([
                    'symbol' => null,
                    'color' => null,
                    'vertical_column' => null,
                ]);
            $this->reset('selectedTable');
        }
    }
    public function render()
    {
        $tables = Table::with('indexs')
            ->select(['id'])
            ->when($this->searchIds || $this->stringSearch, function ($query) {
                $query->whereIn('id', $this->searchIds);
            })
            ->latest()->paginate(10);

        if ($tables->isNotEmpty() && $this->selectedTable['id'] !== $tables[0]->id) {
            $this->selectedTable = [
                'id' => $tables[0]->id,
                'column' => 0,
                'row' => 0,
                'color' => null,
                'verical_column' => 1
            ];
        }

        $searchTable = SearchTable::with('indexs')
            ->select(['id'])
            ->first();
        return view('livewire.tables', ['tables' => $tables, 'searchTable' => $searchTable]);
    }

    public function fillColor($color): void
    {
        $symbol = in_array($color, ['red', 'blue']) ? 'O' : 'X';
        if (is_null($color)) {
            $symbol = null;
        }

        // The most recent row, column filled
        $rowIndex = $this->selectedTable['row'] + 1;
        $columnIndex = $this->selectedTable['column'] ?: 1;
        $tableId = $this->selectedTable['id'];

        if ($this->shouldNextColumn($color)) {
            $this->selectedTable['verical_column'] = $this->selectedTable['verical_column'] + 1;
            $columnIndex = $this->selectedTable['verical_column'];
            $rowIndex = 1;
        }else if ($this->nextRowBlocked()) {
            $columnIndex = $columnIndex + 1;
            if ($this->isFillHorizon($columnIndex)) {
                $verticalColumn = $this->selectedTable['verical_column'];
                $rowIndex = $this->selectedTable['row'];
            }
        }

        $table = TableIndex::where([
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

            $this->updateSelectedTable($rowIndex, $columnIndex, $color);
        }
    }

    private function updateSelectedTable($rowIndex, $columnIndex, $color): void
    {
        $this->selectedTable['row'] = $rowIndex > $this->maxRow ? $this->maxRow : $rowIndex;
        $this->selectedTable['column'] = $columnIndex;
        $this->selectedTable['color'] = $color !== 'green' ? $color :  $this->selectedTable['color'];
    }

    private function isFillHorizon($columnIndex): bool
    {
        return $columnIndex !== $this->selectedTable['verical_column'];
    }
    private function nextRowBlocked()
    {
        $rowIndex = ($this->selectedTable['row'] + 1);
        $columnIndex = $this->selectedTable['column'] ?: 1;
        $tableId = $this->selectedTable['id'];

        if ($rowIndex > 6) {
            return true;
        }

        if ($this->isFillHorizon($columnIndex)) {
            // is fill color is horizon
            return true;
        }

        return TableIndex::where([
                'table_id' => $tableId,
                'row' => $rowIndex,
                'column' => $columnIndex,
            ])
            ->whereNotNull(['symbol', 'color'])
            ->first();
    }

    private function shouldNextColumn($color)
    {
        return $this->selectedTable['color'] && $this->selectedTable['color'] !== $color && $color !== 'green';
    }

    public function addTable(): void
    {
        $table = Table::create();
        $table->indexs()->insert(Table::defineDefaultTableIndexs($table->id));
        array_unshift($this->searchIds, $table->id);
    }

    public function addSearchTable(): void
    {
        $table = SearchTable::create();
        $table->indexs()->insert(SearchTable::defineDefaultTableIndexs($table->id));
    }
    public function search()
    {
        $this->searchIds = [];

        $tables = Table::with('indexs')->select(['id'])
        ->latest()
        ->get();

        $searchs = $this->collectDataSearchTable();

        if ($searchs === '') {
            return;
        }

        foreach ($tables as $table)
        {
            $this->collectDataTable($table);
        }

        $tables->each(function ($item) use ($searchs) {
//            if (strpos($item->stringSearch, $searchs) === false) {
            if (count($this->searchIds) === 10) {
                return false;
            }
            if ($this->tryMatchAgain($item->stringSearch, $searchs)) {
                $this->searchIds[] = $item->id;
            }
//            }
        });
    }

    public function tryMatchAgain($tableString, $searchs)
    {
        $searchArr = explode('__', $searchs);
        $tableStringArr = explode('__', $tableString);

        if (count($searchArr) > count($tableStringArr)) {
            return false;
        }
        $matchResult = [];
        foreach ($searchArr as $key => $search) {
            $matchResult[$key] = [];
            foreach ($tableStringArr as $colIndex => $tableString) {
//                if (strpos($tableString, $search) === 0) {
                if ($tableString === $search) {
                    $matchResult[$key][] = ($colIndex + 1);
                }
            }
            if (count($matchResult[$key]) < 1) {
                break;
            }
        }

        if (count($matchResult) !== count($searchArr) || count($matchResult[0]) <= 0) {
            return false;
        }
        $firstMatch = $matchResult[0];
        unset($matchResult[0]);
        if (count($matchResult) === 0) {
            return true;
        }
        foreach ($firstMatch as $key => $column) {
            $isList = $this->isList($matchResult, $column);
            if ($isList) {
                return true;
            }
        }
        return false;
    }

    public function isList($matchResult, $column)
    {
        $beginValue = $column;
        foreach ($matchResult as $index => $value) {
            foreach ($value as $item) {
                if ($beginValue === ($item - 1)) {
                    $temp = $matchResult;
                    unset($temp[$index]);
                   if ($temp) {
                       return $this->isList($temp, $item);
                   } else {
                       return true;
                   }
                }
            }
        }
        return false;
    }
    public function collectDataTable(Table $table)
    {
        $indexs =$table->indexs
            ->where('symbol', '!=', null)
            ->groupBy('column');
        $string = '';
        foreach ($indexs as $columnIndex => $column) {
            if ($column->first()->row !== 1) {
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

            $string .= $this->collectHorizon($table ,$cell->row, $cell->column, $cell->color).'__';
        }
        $table->stringSearch = trim($string, '__');
    }

    public function getTrueColor($column, $cell, $index)
    {
        if ($cell->color !== 'green') {
            return $cell->color;
        }
        if (isset($column[$index - 1])) {
            return $this->getTrueColor($column, $column[$index - 1], ($index - 1));
        }
        throw new \Exception('can\'t get true color');
    }
    public function shouldStopCollect($cell, $prevCell)
    {
        return ($cell->row !== ($prevCell->row + 1)
            || ($cell->color !== $prevCell->color && $cell->color !== 'green' && $prevCell->color !== 'green'))
            || $cell->vertical_column !== null;
    }
    public function collectHorizon($table, $rowIndex, $beginColumn, $color)
    {
        $string = '';
        $data = $table->indexs
            ->where('row', $rowIndex)
            ->where('column', '>', $beginColumn)
            ->where('vertical_column', '=', $beginColumn)
            ->filter(function ($item) use ($color) {
                return ($item->symbol === 'O' && $item->color === $color) || ($item->symbol === 'X');
            })
            ->sortBy('column');

        if (!$data->isEmpty() && ($data->first()->column - 1) !== $beginColumn ) {
            return '';
        }

        foreach ($data->groupBy('column') as $columnIndex => $column) {
            foreach ($column as $index => $cell) {
                $string .= $cell->symbol . $cell->color;
            }
        }
        return $string;
    }

    public function expandTable($tableId)
    {
        $table = TableIndex::where('table_id', $tableId)
        ->first([
            DB::raw('(max(`column`)) as `max_column`')
        ]);

        if ($table->max_column === 15) {
            return;
        }

        $data = Table::defineExpandDefaultTable($tableId);

        TableIndex::insert($data);
    }

    public function collapseTable($tableId)
    {
        $table = TableIndex::where('table_id', $tableId)
            ->first([
                DB::raw('(max(`column`)) as `max_column`')
            ]);

        if ($table->max_column === 8) {
            return;
        }

        TableIndex::where('table_id', $tableId)
            ->where('column', '>', 8)
            ->delete();
    }


    public function expandSearchTable($tableId)
    {
        $table = SearchTableIndex::where('table_id', $tableId)
            ->first([
                DB::raw('(max(`column`)) as `max_column`')
            ]);

        if ($table->max_column === 15) {
            return;
        }

        $data = SearchTable::defineExpandDefaultTable($tableId);

        SearchTableIndex::insert($data);
    }

    public function collapseSearchTable($tableId)
    {
        $table = SearchTableIndex::where('table_id', $tableId)
            ->first([
                DB::raw('(max(`column`)) as `max_column`')
            ]);

        if ($table->max_column === 8) {
            return;
        }

        SearchTableIndex::where('table_id', $tableId)
            ->where('column', '>', 8)
            ->delete();
    }

    public function deleteTable($tableId)
    {
        Table::where('id', $tableId)
            ->delete();
        TableIndex::where('table_id', $tableId)
            ->delete();
    }
}
