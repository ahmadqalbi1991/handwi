<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Attribute extends Model
{
    //
    protected $table = "product_attribute";
    protected $primaryKey = "attribute_id";
    public $path = "/uploads/banners/";
    public $timestamps = false;


    public static function saveData($data)
    {
        if(isset($data['attribute_id']) && !empty($data['attribute_id'])) {
            $attribute = Attribute::find($data['attribute_id']);
        } else {
           $attribute = new Attribute(); 
        }
        
        $attribute->attribute_name = $data['attribute_name'];
        $attribute->attribute_name_arabic = $data['attribute_name_arabic'];
        $attribute->attribute_status = $data['attribute_status'];
        $attribute->attribute_type   = $data['attribute_type'];
        $status = $attribute->save();
        
        if($status ) {
            return TRUE;
        }
        return FALSE;
    }

    
    
}
