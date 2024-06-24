<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    public $table = 'tables';

    public function indexs()
    {
        return $this->hasMany(TableIndex::class);
    }

    public function rows(): Attribute
    {
        return Attribute::make(get: fn () => $this->indexs->groupBy('row'));
    }

    public function columns(): Attribute
    {
        return Attribute::make(get: fn () => $this->indexs->groupBy('column'));
    }

    public function verticalColumns(): Attribute
    {
        return Attribute::make(get: fn () => $this->indexs->groupBy('vertical_column'));
    }
    public static function defineDefaultTableIndexs($tableId = null)
    {
        $columnsDefault = 8;
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
}
