<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchTableIndex extends Model
{
    use HasFactory;

    public $table = 'search_table_indices';

    public $fillable = [
        'table_id',
        'symbol',
        'color',
        'row',
        'index',
        'vertical_column',
    ];
}
