<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Number extends Seeder
{
    /**
     * Run the database seeds.
     * php artisan db:seed --class=Number

     */
    public function run(): void
    {
        \App\Models\NumberList::factory(10000)->create();

    }
}
