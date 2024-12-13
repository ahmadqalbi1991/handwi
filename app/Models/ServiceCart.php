<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCart extends Model
{
    use HasFactory;
    protected $table = "cart_service";
    protected $guarded = [];
    public static function get_user_cart($where)
    {
        return ServiceCart::where($where)->orderby("id", "asc")->get();
    }

    public static function update_cart($data, $condition)
    {
        return ServiceCart::where($condition)->update($data);
    }
    public static function create_cart($data)
    {
        $cart = ServiceCart::create($data);
        if ($cart) {
            return $cart->id;
        } else {
            return 0;
        }
    }
    public function getDocAttribute($value)
    {
        if($value)
        {
            return asset($value);
        }
        else
        {
            return "";
        }
    }

   
}
