<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedProductsImg extends Model
{
    use HasFactory;
    protected $table = "featured_products_img";
    protected $guarded = []; 
}
