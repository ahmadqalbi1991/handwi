<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorModel extends Model
{
    use HasFactory;
    protected $table = "users";
    public $timestamps = false;

    protected $fillable = ['name', 'email', 'dial_code','phone','role','first_name','last_name','user_image',
    'password','country_id','state_id','city_id','area','phone_verified','vendor','store','previllege','created_at','updated_at','designation_id','active','dob','wallet_amount','activity_id','minimum_order_amount','is_dinein','is_delivery','designation_name','delivery_charge'];

    public function getUserImageAttribute($value)
    {
        if($value)
        {
            return asset($value);
        }
        else
        {
            return asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg');
        }
    }
    public function vendordata() {
       return $this->hasMany(VendorDetailsModel::class,'user_id');
    }
    public function menu_images() {
       return $this->hasMany(VendorMenuImages::class,'vendor_id');
    }
    public function vendor_cuisines() {
       return $this->hasMany(VendorCuisines::class,'vendor_id');
    }
    public function rattings() {
       return $this->hasMany(Rating::class,'vendor_id');
    }
    public function stores() {
        return $this->hasMany('App\Models\Stores', 'vendor_id', 'id'); 
    }

    public function activity() {
        return $this->hasOne(ActivityType::class,'id','activity_id');
     }

    public function VendorTypes ( $id = 0 ) {

        if ( $id != 0 ) {
            switch($id) {
                case(1): $vendor_type = 'Pharmacy'; break;
                case(2): $vendor_type = 'Health Service'; break;
                case(3): $vendor_type = 'Both'; break;
                default: $vendor_type = 'Both';
            }
        } else {
            $vendor_type[] = array ("id" => 1, "name" => "Pharmacy");
            $vendor_type[] = array ("id" => 2, "name" => "Health Service");
            $vendor_type[] = array ("id" => 3, "name" => "Both");
        }

        return $vendor_type;
    }

}
