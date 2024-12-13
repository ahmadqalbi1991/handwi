<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavouriteModel extends Model
{
    use HasFactory;

    protected $table = 'favourate';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'product_id',
        'product_attribute_id',
        'favourate_added_time'
    ];
}
