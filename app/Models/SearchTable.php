<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function array_merge;
use function now;

class SearchTable extends Model
{
    use HasFactory;


    public $table = 'search_tables';

    public function indexs()
    {
        return $this->hasMany(SearchTableIndex::class, 'table_id');
    }

    public function rows(): Attribute
    {
        return Attribute::make(get: fn () => $this->indexs->groupBy('row'));
    }

    public static function defineDefaultTableIndexs($tableId = null)
    {
        $columnsDefault = 20;
        $rowsDefault = 6;
        $data = [];
        for ($i=1; $i <= $columnsDefault; $i++) {
            $temp = [
                'column' => $i,
                'table_id' => $tableId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            for ($j=1; $j <= $rowsDefault; $j++) {
                $data[] = array_merge($temp, ['row' => $j]);
            }
        }
        return $data;
    }

    public static function defineExpandDefaultTable($tableId = null)
    {
        $columnsDefault = 15;
        $rowsDefault = 6;
        $data = [];
        for ($i=9; $i <= $columnsDefault; $i++) {
            $temp = [
                'column' => $i,
                'table_id' => $tableId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            for ($j=1; $j <= $rowsDefault; $j++) {
                $data[] = array_merge($temp, ['row' => $j]);
            }
        }
        return $data;
    }

    public function verticalColumns(): Attribute
    {
        return Attribute::make(get: fn () => $this->indexs->groupBy('vertical_column'));
    }
}
