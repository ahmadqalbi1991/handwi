<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class faq_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('faq')->insert([
            [
                'faq_id' => 1,
                'faq_title' => 'Test Faq1',
                'faq_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent venenatis ...',
                'status' => 1,
                'country_id' => 108,
            ],
            [
                'faq_id' => 2,
                'faq_title' => 'Test Faq2',
                'faq_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent venenatis ...',
                'status' => 1,
                'country_id' => 108,
            ],
            [
                'faq_id' => 3,
                'faq_title' => 'Test Faq3',
                'faq_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent venenatis ...',
                'status' => 1,
                'country_id' => 108,
            ],
        ]);
    }
}
