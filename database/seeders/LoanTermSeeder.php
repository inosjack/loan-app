<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('loan_terms')->insert([
            [
                'name' => '4-week',
                'tenure' => 3,
                'frequency' => 'week',
                'interest_rate' => 3.00,
            ],
            [
                'name' => '6-week',
                'tenure' => 4,
                'frequency' => 'week',
                'interest_rate' => 4.00,
            ],
            [
                'name' => '12-week',
                'tenure' => 5,
                'frequency' => 'week',
                'interest_rate' => 5.00,
            ],
        ]);
    }
}
