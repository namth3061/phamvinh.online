<?php

namespace App\Livewire;

use App\Models\SearchTable;
use App\Models\SearchTableIndex;
use App\Models\Table;
use App\Models\TableIndex;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use function collect;
use function implode;
use function strpos;

class Tables extends Component
{
    use HasSearchTab;

    public $maxRow = 6;

    public $columnsIndex = '';

    public $ignoreIds = [];

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
                ]);
            $this->reset('selectedTable');
        }
    }
    public function render()
    {
        $tables = Table::with('indexs')->select(['id'])
        ->whereNotIn('id', $this->ignoreIds)->latest()->paginate(10);

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
                $rowIndex = $this->selectedTable['row'];
            }
        }

        $table = TableIndex::where([
            'table_id' => $tableId,
            'row' => $rowIndex > $this->maxRow ? $this->maxRow : $rowIndex,
            'column' => $columnIndex
        ])->first();

        if ($table !== null) {
            $table->update([
                'symbol' => $symbol,
                'color' => $color,
            ]);

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
    }

    public function addSearchTable(): void
    {
        $table = SearchTable::create();
        $table->indexs()->insert(SearchTable::defineDefaultTableIndexs($table->id));
    }
    public function search()
    {
        $this->ignoreIds = [];

        $tables = Table::with('indexs')->select(['id'])
        ->get();

        $searchs = $this->collectDataSearchTable();
        foreach ($tables as $table)
        {
            $this->collectDataTable($table);
        }

        $tables->each(function ($item) use ($searchs) {
            if (strpos($item->stringSearch, $searchs) === false) {
                $this->ignoreIds[] = $item->id;
            }
        });
    }

    public function collectDataTable(Table $table)
    {
        $indexs =$table->indexs
            ->where('symbol', '!=', null)
            ->groupBy('column');
        $string = '';
        foreach ($indexs as $columnIndex => $column) {
            foreach ($column as $index => $cell) {
                $string .= $cell->symbol . $cell->color;
            }
            // $this->collectHorizon($table ,$cell->row, $cell->column, $cell->color).
            $string .= '__';
        }
        $table->stringSearch = trim($string, '__');
    }

    public function collectHorizon($table, $rowIndex, $beginColumn, $color)
    {
        $string = '';
        $data = $table->indexs
            ->where('row', $rowIndex)
            ->where('column', '>', $beginColumn)
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
}
