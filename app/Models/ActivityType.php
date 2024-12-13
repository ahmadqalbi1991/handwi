<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class ActivityType extends Model
{
    use HasFactory;

    const SPORTS_CLUBS = 1;
    const WEDDING_HALLS = 2;
    // const PLAYGROUNDS = 3; // this is a duplicate (deleted)
    const HOTEL = 4;
    const APPARTMENT = 5;
    const CHALET = 6;
    const CAR_REPAIR = 7;
    const RESTAURANTS = 8; //(account_id = 6)
    const FRUIT_VEGETABLE = 9;
    const TOYS = 10;
    const CLOTHING = 11;
    const RESTAURANT = 12; // this is a duplicate  (account id = 1)
    const MEDICINE = 13;
    const SUPER_MARKET = 14;
    const PLUMBER = 15;
    const GYM = 16;
    const PLAYGROUND = 17;
    const ELECTRICIAN = 18;
    const PAINTER = 19;
    const CAR_MAINTENANCE = 20;
    const MOBILE_MAINTENANCE = 21;
    const AIR_CONDITION = 22;
    const CAFE = 23;

    protected $table = "activity_type";
    protected $primaryKey = "id";

    public $appends = ['activity_type_image','capitalized_name'];

    protected $guarded = [];

    public function getActivityTypeImageAttribute()
    {
        if($this->logo)
        {
            return asset($this->logo);
        }
        else
        {
            return '';//asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg');
        }
        // return 'https://1805025482.rsc.cdn77.org/'.$this->logo;
    }
    public function getBannerImageAttribute($value)
    {
        return get_uploaded_image_url($value, 'service_image_upload_dir');
    }
    public function getIndvidualLogoAttribute($value)
    {
        return get_uploaded_image_url($value, 'service_image_upload_dir');
    }
    public function getAllImageAttribute($value)
    {
        return get_uploaded_image_url($value, 'service_image_upload_dir');
    }
    public function getCapitalizedNameAttribute(){
        return ucwords(strtolower($this->name));
    }

    // public function getLogoAttribute($value){
    //     return (string)$value;
    // }

    public function account()
    {
        return $this->belongsTo(AccountType::class, 'account_id')->withDefault([
            'name' => 'No Account',
        ]);
    }

    public function reservation_products()
    {
        return $this->hasMany(ReservationProduct::class);
    }  
    public function categories() {
        return $this->hasMany('App\Models\Categories', 'activity_id', 'id'); 
    }

    public function getLogoAttribute($value)
    {
        if($value)
        {
            return asset($value);
        }
        else
        {
            return '';//asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg');
        }
    }

    public function reservation_bookings()
    {
        return $this->hasMany(ReservationBooking::class);
    }

    public static function sort_item($item=[]){
        if( !empty($item) ){
            DB::beginTransaction();
            try {
                $i=0;
                foreach( $item as $key ){
                    ActivityType::where('id', $key)
                        ->update(['sort_order' => $i]);
                    $i++;
                }
                DB::commit();
                return 1;
            } catch (\Exception $e) {
                DB::rollback();
                return 0;
            }
        }else{
            return 0;
        }
    }
}
