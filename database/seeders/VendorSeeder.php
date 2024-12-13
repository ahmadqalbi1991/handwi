<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'id' => '1200',
            'name' => 'Vendor',
            'phone' => '123456789',
            'active' => '1',
            'vendor' => '3',
            'verified' => '1',
            'role' => '3',
            'email' => 'vendor@gmail.com',
            'password' => bcrypt('password'),
        ]);

        DB::table('vendor_details')->insert([
            'user_id' => '1200',
            'industry_type' => '3',
        ]);
    }
}
