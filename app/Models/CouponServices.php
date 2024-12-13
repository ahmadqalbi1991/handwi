<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponServices extends Model
{
    use HasFactory;
    protected $table = "coupon_services";
    public $timestamps = false;
    public static function insertservices($id,$services){
         CouponServices::where('coupon_id',$id)->delete();
         if(!empty($services))
         {
            foreach ($services as $value) {
             $couponcat = new CouponServices();
             $couponcat->coupon_id   = $id;
             $couponcat->service_id = $value;
             $couponcat->save();
            }
         }
         
    } 
}
