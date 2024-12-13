<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\User;
use App\Models\Coupon;
use App\Models\FavoriteBrand;

class Transport extends Model
{
    //
    protected $table = "transport";
    protected $primaryKey = "id";

    protected $guarded = [];
    //protected $appends = ['is_favorite'];   


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

    public function category(){
        return $this->belongsTo(Categories::class);
    }

    public function coupon(){
        $coupon = Coupon::where('brand_id',$this->id)->count();
        return $coupon;
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

   
}
