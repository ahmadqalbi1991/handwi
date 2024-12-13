<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = ['title','title_ar','brand_id','category_id','coupon_code','active','sort_order','trending','description','description_ar','hot_deal','start_date','coupon_end_date'];

    protected $appends = ['image'];

    public function getImageAttribute()
    {
        $brand = Brands::whereId($this->brand_id)->first();
        if($brand != null){
            return $brand->image;
        }
        return '';
    }

    public function category(){
        return $this->hasOne(CouponCategory::class,'category_id','id');
    }

    public function brand(){
        return $this->belongsTo(Brands::class);
    }

    public static function sort_item($item=[]){
        if( !empty($item) ){
            DB::beginTransaction();
            try {
                    $i=0;
                    foreach( $item as $key ){
                        Coupon::where('id', $key)
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

    // function countries()
    // {
    //     return $this->belongsToMany(CountryModel::class, 'coupon_countries', 'coupon_id', 'country_id');
    // }

    // public function coupon_contry()
    // {
    //     return $this->hasMany(CouponCountry::class, 'coupon_id');
    // }
}
