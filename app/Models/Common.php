<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Common extends Model
{
    // Common v1.0.3 
    // 2022-03-07
    // Updated 2022-03-17
    public static function check_already($table,$where){
        $data = DB::table($table)
                    ->where($where)
                    ->count();
        if ($data > 0)
        {
        return true;
        }
        else
        {
        return false;
        }
    }
    public static function check_already_edit($table,$where,$not,$notvalue){
        $data = DB::table($table)
                    ->where($where)
                    ->where($not,'!=',$notvalue)
                    ->count();
        if ($data > 0)
        {
        return true;
        }
        else
        {
        return false;
        }
    }
    public static function get_data_array($table,$where){
        $data = DB::table($table)
                    ->where($where)
                    ->get()
                    ->toArray();
                    return $data;
    }
    public static function get_data($table,$where){
        $data = DB::table($table)
                    ->where($where)
                    ->get();
                    return $data;
    }
    public static function insert_to_db($table,$set){
        $data = DB::table($table)
                    ->insert($set);
                    $id = DB::getPdo()->lastInsertId();
                    return $id;
    }
    public static function update_db($table,$where,$set){
        $data = DB::table($table)
                    ->where($where)
                    ->update($set);
    }
    public static function get_a_field($table,$select,$where,$retun="")
    {
             $data = DB::table($table)->where($where)->first($select);
             
             if(!empty($data->$select))
             {
              return $data->$select;  
             }
             else
             {
               return $retun; 
             }
    }
    public static function delete_where($table,$where){
        $data = DB::table($table)
             ->where($where)
             ->delete();
    }
    
}
