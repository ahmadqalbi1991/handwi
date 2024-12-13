<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorCuisines extends Model
{
    use HasFactory;
   	protected $table = "vendor_cuisines";
    protected $guarded = []; 

    public function vendor() {
	    return $this->hasOne(VendorModel::class,'id','vendor_id');
	}
	public function cuisine() {
	    return $this->hasOne(Cuisine::class,'id','cuisine_id')->where(['deleted' => '0']);
	}
}
