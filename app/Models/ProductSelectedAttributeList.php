<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSelectedAttributeList extends Model
{
    use HasFactory;
   	protected $table = "product_selected_attribute_list";
    protected $primaryKey = "product_attribute_id";
    public $timestamps = false;
    

    protected $guarded = []; 

    public function product() {
	    return $this->hasOne(ProductModel::class,'id','product_id');
	}
	
}
