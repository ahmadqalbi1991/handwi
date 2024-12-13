<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CategoriesCoupons extends Model
{
    //
    protected $table = "coupons_category";
    protected $primaryKey = "id";

    protected $guarded = [];  

    public function getBannerImageAttribute($value)
    {
        if($value)
        {
            // return get_uploaded_image_url($value,'banner_image_upload_dir');
            return asset($value);
        }
        else
        {
            return '';
        }
    }
    public function getImageAttribute($value)
    {
        if($value)
        {
            // return get_uploaded_image_url($value,'banner_image_upload_dir');
            return asset($value);
        }
        else
        {
            return '';
        }
    }

    public function children() {
        return $this->hasMany('App\Models\CategoriesCoupons', 'parent_id', 'id'); 
    }

    public static function sort_item($item=[]){
        if( !empty($item) ){
            DB::beginTransaction();
            try {
                    $i=0;
                    foreach( $item as $key ){
                        Categories::where('id', $key)
                            ->update(['sort_order' => $i]);
                        $i++;
                    }
                    DB::commit();
                return 1;
            } catch (\Exception $e) {
                DB::rollback();
                return 0;
            }
        }else{
            return 0;
        }
    }


}
