<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\TableIndex;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::with('indexs')->get();
        // $table = Table::create();
        // TableIndex::insert(Table::defineDefaultTableIndexs($table->id));
        return view('table.index', compact('tables'));
    }
}
