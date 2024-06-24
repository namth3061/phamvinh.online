<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableIndex extends Model
{
    use HasFactory;

    public $table = 'table_index';

    public $fillable = [
        'table_id',
        'symbol',
        'color',
        'row',
        'index',
        'vertical_column'
    ];

}
