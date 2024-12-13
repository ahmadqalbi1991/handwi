<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ContactUsModel extends Model
{
    //
    protected $table = "contact_us";
    protected $primaryKey = "id";
    public $timestamps = false;


    public $fillable = [
        'name',
        'email',
        'mobile_number',
        'message',
        'date',
    ];

    public static function get_list($where=[],$params=[]){
        $contact = ContactUsModel::where($where)->orderBy('created_at','desc');  
        if( !empty($params) ){
            if(isset($params['search_key']) && $params['search_key'] != ''){
                $search_key = $params['search_key'];
                $contact->whereRaw("(name ilike '%$search_key%' OR email ilike '%$search_key%' OR phone ilike '%$search_key%' OR message ilike '%$search_key%')");
            }
        }
        return $contact;
    } 
}
