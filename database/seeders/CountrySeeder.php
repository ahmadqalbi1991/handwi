<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $jsonData = json_decode(file_get_contents('https://gist.githubusercontent.com/anubhavshrimal/75f6183458db8c453306f93521e93d37/raw/f77e7598a8503f1f70528ae1cbf9f66755698a16/CountryCodes.json'));
        $data = [];
        foreach ($jsonData as $key ) {
          $data[] =[
            'name' => $key->name,
            'prefix'=> $key->code,
            'dial_code'=>str_replace('+','',$key->dial_code),
            'created_at'=>gmdate('Y-m-d H:i:s'),
            'updated_at'=>gmdate('Y-m-d H:i:s')
          ];
        }

        if(!empty($data)){
          DB::table('country')->truncate();
          //$statement = "ALTER TABLE country AUTO_INCREMENT = 1;";
          //DB::unprepared($statement);
          DB::table('country')->insert($data);
        }
    }
}
