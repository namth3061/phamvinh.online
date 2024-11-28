<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportXocDiaData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-xoc-dia-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = storage_path('data.txt');
        $chunkSize = 1000;

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File does not exist'], 404);
        }

        $file = fopen($filePath, 'r');
        $batch = [];
        $rowCount = 0;

        try {
            while (($line = fgets($file)) !== false) {
                $batch[] = [
                    'numbers' => trim($line),
                ];

                $rowCount++;

                // Insert the batch when the chunk size is reached
                if ($rowCount % $chunkSize === 0) {
                    DB::table('xocdia_number_lists')->insert($batch);
                    $batch = [];
                }
            }

            // Insert any remaining rows
            if (!empty($batch)) {
                DB::table('xocdia_number_lists')->insert($batch);
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        fclose($file);
//        dd(DB::table('xocdia_number_lists')->count());
        return response()->json(['message' => 'File processed successfully']);
    }

}
