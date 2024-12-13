<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Admin',
            'phone' => '123456789',
            'role' => '1',
            'active' => '1',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);
    }
}
