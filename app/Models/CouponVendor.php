<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponVendor extends Model
{
    use HasFactory;
    protected $table = "coupon_vendor";
    public $timestamps = false;
    public static function insertvendors($id,$vendors){
         CouponVendor::where('coupon_id',$id)->delete();
         if(!empty($vendors))
         {
            foreach ($vendors as $value) {
             $couponcat = new CouponVendor();
             $couponcat->coupon_id   = $id;
             $couponcat->vendor = $value;
             $couponcat->save();
            }
         }
         $datamain = CouponVendor::where('coupon_id',$id)->get();
         
    } 
}
