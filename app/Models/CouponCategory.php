<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponCategory extends Model
{
    use HasFactory;
    protected $table = "coupon_category";
    public $timestamps = false;
    public static function insertcategory($id,$category){
         CouponCategory::where('coupon_id',$id)->delete();
         if(!empty($category))
         {
            foreach ($category as $value) {
             $couponcat = new CouponCategory();
             $couponcat->coupon_id   = $id;
             $couponcat->category_id = $value;
             $couponcat->save();
            }
         }
         
    } 
}
