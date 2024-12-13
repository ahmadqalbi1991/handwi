<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponProducts extends Model
{
    use HasFactory;
    protected $table = "coupon_products";
    public $timestamps = false;
    public static function insertproducts($id,$products){
         CouponProducts::where('coupon_id',$id)->delete();
         if(!empty($products))
         {
            foreach ($products as $value) {
             $couponcat = new CouponProducts();
             $couponcat->coupon_id   = $id;
             $couponcat->product_id = $value;
             $couponcat->save();
            }
         }
         
    } 
}
