<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Attribute_values extends Model
{
    //
    protected $table = "product_attribute_values";
    protected $primaryKey = "attribute_values_id";
    public $path = "/uploads/banners/";
    public $timestamps = false;


    public static function attributeValues($data)
    {
        
       if(isset($data['attribute_values_id']) && !empty($data['attribute_values_id'])) {
            $attribute = Attribute_values::find($data['attribute_values_id']);
        } else {
           $attribute = new Attribute_values(); 
        }
        $attribute->attribute_id            = $data['txt_attr_name'];
        $attribute->attribute_values        = $data['txt_attr_val_name'];
        $attribute->attribute_values_arabic = $data['txt_attr_val_name_arabic'];
        $attribute->attribute_value_in      = $data['txt_attr_value_in'];
        $attribute->attribute_color         = $data['txt_attr_color'];
        
        $status = $attribute->save();
        
        if($status ) {
            return TRUE;
        }
        return FALSE;
    
    }
    public static function get_data($attr_id){
        $data = DB::table('product_attribute_values as attribute_value')
                    ->join('product_attribute as attribute','attribute.attribute_id','=','attribute_value.attribute_id')
                    ->where('attribute_value.attribute_id','=',$attr_id)
                    ->where(['attribute_value.is_deleted'=>0])
                    ->orderBy('attribute_value.attribute_values_id','desc')
                    ->paginate(5);
        return $data;
    }

   
    
}
