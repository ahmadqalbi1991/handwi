<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    protected $table = "product_category";
    
    public function product() {
	    return $this->hasOne(ProductModel::class,'id','product_id');
	}
	public function category() {
	    return $this->hasOne(Categories::class,'id','category_id')->where(['deleted' => '0']);
	}

   
}
