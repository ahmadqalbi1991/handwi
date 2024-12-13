<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomBanner extends Model
{
    use HasFactory;
    protected $table = 'banner_images_app';
    protected $primaryKey = 'id';
    protected $fillable = [
        'bi_name',
        'bi_image',
        'product_id',
        'product_attr_id',
        'bi_status'
    ];
    
    public $timestamps = false;
}
