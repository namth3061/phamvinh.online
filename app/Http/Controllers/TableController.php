<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TableController extends Controller
{
    public function index()
    {
        $tables = \App\Models\Table::latest()->first()?->html ?? '';
        return view('table', compact('tables'));
    }

    public function store(Request $request)
    {
        $tablesHtml = $request->input('tables_html');

        \App\Models\Table::create(['html' => $tablesHtml]);

        return response()->json(['message' => 'Tables saved successfully!']);
    }
}
