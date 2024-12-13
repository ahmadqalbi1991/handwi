<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class AccountType extends Model
{
    protected $fillable = [
        'status'
    ];
    use HasFactory;

    const COMMERCIAL_CENTER = 1;
    const RESERVATIONS = 2;
    const INDIVIDUALS = 3;
    const SERVICE_PROVIDERS = 4;
    const WHOLE_SELLERS = 5;
    const DELIVERY_REPRESENTATIVES = 6;

    const IMAGELIST = [
        1 => 'https://livemarketdxb.s3.ap-southeast-1.amazonaws.com/account_types/commercial.gif',
        2 => 'https://livemarketdxb.s3.ap-southeast-1.amazonaws.com/account_types/reservation.gif',
        3 => 'https://livemarketdxb.s3.ap-southeast-1.amazonaws.com/account_types/Individual.gif',
        4 => 'https://livemarketdxb.s3.ap-southeast-1.amazonaws.com/account_types/service-pro.gif',
        5 => 'https://livemarketdxb.s3.ap-southeast-1.amazonaws.com/account_types/whol-sel.png',
        6 => 'https://livemarketdxb.s3.ap-southeast-1.amazonaws.com/account_types/imgpsh_fullsize_anim.gif',
    ];

    protected $table = "account_type";
    protected $primaryKey = "id";

    protected $guarded = [];

    public $appends = ['capitalized_name'];

    public function reservation_products()
    {
        return $this->hasMany(ReservationProduct::class);
    }

    // public function getImageAttribute()
    // {
    //     return AccountType::IMAGELIST[$this->id];
    // }
    public function getCapitalizedNameAttribute(){
        return ucwords(strtolower($this->name));
    }

    public static function sort_item($item=[]){
        if( !empty($item) ){
            DB::beginTransaction();
            try {
                $i=0;
                foreach( $item as $key ){
                    AccountType::where('id', $key)
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
