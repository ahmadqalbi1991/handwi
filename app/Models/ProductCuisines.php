<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCuisines extends Model
{
    use HasFactory;
   	protected $table = "product_cuisines";
    protected $guarded = []; 

    public function product() {
	    return $this->hasOne(ProductModel::class,'id','product_id');
	}
	public function cuisine() {
	    return $this->hasOne(Cuisine::class,'id','cuisine_id')->where(['deleted' => '0']);
	}
}
