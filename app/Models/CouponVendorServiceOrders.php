<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponVendorServiceOrders extends Model
{
    use HasFactory;
    protected $table = "coupon_vendor_service_order";
    public $timestamps = false;
    
}
