<?php

namespace App\Livewire;

use App\Models\Table;
use App\Models\TableIndex;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use function implode;
use function strpos;

class Tables extends Component
{

    public $columnsIndex = '';

    public $ignoreIds = [];

    public function render()
    {
        $tables = Table::with('indexs')->select(['id'])
        ->whereNotIn('id', $this->ignoreIds)->latest()->paginate(10);

        return view('livewire.tables', ['tables' => $tables]);
    }

    public function fillColor($tableId, $color, $rowIndex, $columnIndex): void
    {
        $symbol = in_array($color, ['red', 'blue']) ? 'O' : 'X';
        if (is_null($color)) {
            $symbol = null;
        }

        $table = TableIndex::where([
            'table_id' => $tableId,
            'row' => $rowIndex,
            'column' => $columnIndex
        ])->update([
            'symbol' => $symbol,
            'color' => $color,
        ]);
    }

    public function addTable(): void
    {
        $table = Table::create();
        $table->indexs()->insert(Table::defineDefaultTableIndexs($table->id));
    }

    public function search()
    {
        $this->ignoreIds = [];
        $searchs = explode(' ', $this->columnsIndex, 15);
        if (!$searchs) {
            return;
        }
        $tables = Table::with('indexs')->select(['id'])
        ->get();

        foreach ($tables as $table)
        {
            $table->indexString = '';
            for ($i = 1; $i <= $table->indexs->max('column') ; $i++) {
                $totalSymbol = $this->countVertical($table, $i);
                $table->indexString.=$totalSymbol;
            }
        }

        $tables->each(function ($item) use ($searchs) {
            if (strpos($item->indexString, implode('', $searchs)) === false) {
                $this->ignoreIds[] = $item->id;
            }
        });
    }

    public function countVertical(Table $table, int $columnIndex)
    {
        $tableIndexs = $table->indexs
            ->whereIn('symbol', ['O', 'X'])
            ->where('column', $columnIndex)
            ->values()
            ->sortBy('row');

        if ($tableIndexs->where('symbol', 'O')->isEmpty()) {
            // done have any symbol
            return 0;
        }
        // find the init color
        $initColor = $tableIndexs->firstWhere('color', '!=', 'green')->color;

        $tableIndexs = $tableIndexs->filter(function ($item) use ($initColor) {
            return $item->color === $initColor || $item->color === 'green';
        });

        // find the max row filled
        $mostRecentRow = null;
        $tableIndexs = $tableIndexs->filter(function ($item, $index) use ($tableIndexs, &$mostRecentRow) {
            if ($index === 0) {
                $mostRecentRow = $item->row;
                return true;
            }

            if (is_null($mostRecentRow) || $mostRecentRow  === ($item->row - 1)) {
                $mostRecentRow = $item->row;
                return true;
            }
        });

        if ($tableIndexs->where('symbol', 'O')->isEmpty()) {
            // done have any symbol
            return 0;
        }

        $beginRow = $tableIndexs->max('row');
        if ($beginRow === 1) {
            return $tableIndexs->where('symbol', 'O')->count();
        }

        // count external column
        $countHorizon = $this->countHorizon($table, $beginRow, $columnIndex, $initColor);

        return $tableIndexs->where('symbol', 'O')->count() + $countHorizon->count();
    }

    public function countHorizon(Table $table, int $rowIndex, int $beginColumn, string $color)
    {
        $data = $table->indexs
        // ->whereIn('symbol', ['O', 'X'])
        ->where('row', $rowIndex)
        ->where('column', '>', $beginColumn)
        ->filter(function ($item) use ($color) {
            return ($item->symbol === 'O' && $item->color === $color) || ($item->symbol === 'X');
        })
        ->sortBy('column');

        if (!$data->isEmpty() && ($data->first()->column - 1) !== $beginColumn ) {
            return collect();
        }

        return $data->where('symbol', 'O')->where('color', $color);
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
}
