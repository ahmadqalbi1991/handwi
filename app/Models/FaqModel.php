<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class FaqModel extends Model
{
    //
    protected $table = "faq";
    protected $primaryKey = "faq_id";
    public $timestamps = false;


    public $fillable = [
        'faq_title',
        'faq_description',
        'status'
    ];

    public static function get_faq_list($where=[],$params=[]){
        $faq = FaqModel::where($where)->orderBy('created_at','desc');  
        if( !empty($params) ){
            if(isset($params['search_key']) && $params['search_key'] != ''){
                $faq->Where('title','ilike','%'.$params['search_key'].'%');
            }
        }
        return $faq;
    } 
}
