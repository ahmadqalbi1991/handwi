<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class HelpModel extends Model
{
    //
    protected $table = "help";
    protected $primaryKey = "id";
    public $timestamps = false;


    public $fillable = [
        'title',
        'description',
        'created_by',
        'created_on',
        'updated_by',
        'updated_on',
        'active'
    ];

    public static function get_help_list($where=[],$params=[]){
        $help = HelpModel::where($where)->orderBy('created_at','desc');  
        if( !empty($params) ){
            if(isset($params['search_key']) && $params['search_key'] != ''){
                $help->Where('title','ilike','%'.$params['search_key'].'%');
            }
        }
        return $help;
    } 
}
