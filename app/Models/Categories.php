<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Categories extends Model
{
    //
    protected $table = "category";
    protected $primaryKey = "id";

    protected $guarded = [];


    public function children() {
        return $this->hasMany('App\Models\Categories', 'parent_id', 'id'); 
    }
    public function parent()
    {
        return $this->hasOne('App\Models\Categories', 'id', 'parent_id');
    }
    public function product_categories() {
        return $this->hasMany(ProductCategory::class,'category_id','id');
    }

    public function activity() {
        return $this->belongsTo('App\Models\ActivityType', 'activity_id', 'id'); 
    }
    // public function getBannerImageAttribute($value){
    //     if($value){
    //         return $this->path.$value;
    //     }else{
    //         return $this->path.'placeholder.png';
    //     }

    // }
    public function getImageAttribute($value)
    {
        if($value)
        {
            return get_uploaded_image_url($value);
        }
        else
        {
            return '';//asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg');
        }
    }
    public function getBannerImageAttribute($value)
    {
        if($value)
        {
            return get_uploaded_image_url($value);
        }
        else
        {
            return '';//asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg');
        }
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
