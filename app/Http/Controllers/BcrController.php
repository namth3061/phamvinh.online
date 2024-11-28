<?php

namespace App\Http\Controllers;

use App\Models\NumberListBcr;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;

class BcrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $totalRecords = NumberListBcr::count();

        return view('bcr', compact('totalRecords'));
    }

    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $numbers = NumberListBcr::select(['id', 'numbers'])->orderBy('id', 'DESC');
        $totalRecords = NumberListBcr::count();

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

    public function download()
    {
        $numbers = NumberListBcr::select(['numbers'])
            ->pluck('numbers')
            ->toArray();
        $content = '';
        foreach ($numbers as $number) {
            $content .= $number;
            $content .= "\n";
        }

        $fileName = "bcr.txt";
        $headers = [
            'Content-type' => 'text/plain',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
        ];

        return Response::make($content, 200, $headers);
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

            $arr[] = [
                'numbers' => $datum,
            ];
        }

        DB::table('number_lists_bcr')->upsert($arr, 'numbers');

        return true;
    }

    public function delete($id)
    {
        DB::table('number_lists_bcr')
            ->whereId($id)
            ->delete();

        return true;
    }

}
