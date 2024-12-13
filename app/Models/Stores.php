<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stores extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function vendor()
    {
        return $this->belongsTo('App\Models\VendorModel', 'vendor_id', 'id');
    }
    //********don't change this function.. 
    public static function get_stores($where = [], $filter = [], $limit = '', $offset = 0)
    {
        $lat = $filter['lat'];
        $long = $filter['long'];
        $distance =
            "6371 * acos (
            cos ( radians( CAST (stores.latitude AS double precision) ) )
            * cos( radians( CAST ({$lat} AS double precision) ) )
            * cos( radians( CAST ({$long} AS double precision) ) - radians( CAST (stores.longitude AS double precision) ) )
            + sin ( radians( CAST (stores.latitude AS double precision) ) )
            * sin( radians ( CAST ({$lat} AS double precision) ) )
        )";
        $stores = DB::table('stores')->where($where)->select('id', 'store_name', 'location', 'logo', 'cover_image','latitude','longitude')->selectRaw("$distance as distance");

        if (isset($filter['distance']) && $filter['distance']) {
            $filter_distance = $filter['distance'];
            $stores->whereRaw("$distance<=$filter_distance");
        }
        if (isset($filter['search_text']) && $filter['search_text']) {
            $srch = $filter['search_text'];
            $stores->whereRaw("(store_name ilike '%$srch%')");
        }
        // $stores = DB::table('stores');
        if($limit !="") {
            $stores->limit($limit)->skip($offset);
        }
        $stores->orderBy('created_at','desc')->get();


        return $stores;
        
    }
}
