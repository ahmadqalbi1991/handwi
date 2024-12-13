<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ProductAttribute extends Model
{
    //
    protected $table = "product_attribute";
    protected $primaryKey = "product_attribute_id";
    public $timestamps = false;
     public $fillable = [
        'product_attribute_id',
        'product_id',
        'manage_stock',
        'stock_quantity',
        'allow_back_order',
        'stock_status',
        'sold_individually',
        'sale_price',
        'regular_price',
        'shipping_class',
        'taxable',
        'image',
        'weight',
        'length',
        'width',
        'height',
        'shipping_note',
        'title',
        'pr_code','product_desc','product_full_descr','barcode'
    ];
}