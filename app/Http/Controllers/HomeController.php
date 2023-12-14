<?php

namespace App\Http\Controllers;

use App\Models\NumberList;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $totalRecords = NumberList::count();

        return view('welcome', compact('totalRecords'));
    }

    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $numbers = NumberList::select(['id', 'numbers'])->orderBy('id', 'DESC');
        $totalRecords = NumberList::count();

        $data = DataTables::eloquent($numbers)
            ->filter(function ($query) use ($request) {
                // Apply search filter
                if ($request->has('search') && !empty($request->input('search'))) {
                    $searchValue = $request->input('search');
                    $query->where(function ($subQuery) use ($searchValue) {
                        $subQuery->where('numbers', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->toArray();
        $data['recordsTotal'] = $totalRecords;
        $data['recordsSearched'] = $this->getFilteredRecordCount($numbers, $request);

        return response()->json($data);
    }


    private function getFilteredRecordCount($query, $request)
    {
        if ($request->has('search') && !empty($request->input('search'))) {
            $searchValue = $request->input('search');
            $filteredQuery = clone $query;
            $filteredQuery->where('numbers', 'like', "%{$searchValue}%")->count();
            return $filteredQuery->where('numbers', 'like', "%{$searchValue}%")->count();
        } else {
            return $query->count();
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $string = trim(preg_replace('/\s\s+/', ' ', $request->input('numbers')));

        $data = explode(' ', $string);

        $arr = [];
        foreach ($data as $datum) {
            if (empty($datum)) continue;

            $bool = str_contains($datum, '.');

            $arr[] = [
                'numbers' => $bool ? $datum : implode(".", str_split($datum)),
            ];
        }

        DB::table('number_lists')->upsert($arr, 'numbers');

        return true;
    }

    public function delete($id)
    {
        DB::table('number_lists')
            ->whereId($id)
            ->delete();

        return true;
    }

}
