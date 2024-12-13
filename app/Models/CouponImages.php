<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CouponImages extends Model
{
    //
    protected $table = "coupon_images";
    protected $primaryKey = "id";

    protected $guarded = [];

    public function getImageAttribute($value)
    {
        return get_uploaded_image_url_multiple($value,'coupon_image_upload_dir');
    }


}
