<?php

namespace Database\Seeders;

use App\Models\UserTable;
use Illuminate\Database\Seeder;

class insert_admin_user_in_user_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserTable::create([
            'user_first_name' => 'Ma7zouz',
            'user_last_name' => 'Admin',
            'user_email_id' => 'admin@ma7zouz.com',
            'user_password' => bcrypt('Hello@2255'),
            'user_status' => 1,
            'is_admin' => 1
        ]);
    }
}
