<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDetailsModel extends Model
{
    use HasFactory;
    protected $table = "vendor_details";
    public $timestamps = true;


    public function getLogoAttribute($value)
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
    public function industry_type(){
        return $this->hasOne('App\Models\IndustryTypes', 'id', 'industry_type');
    }
    public function country() {
       return $this->belongsTo(CountryModel::class,'country_id');
    }
    public function city() {
       return $this->belongsTo(Cities::class,'city_id');
    }
    public function state() {
       return $this->belongsTo(States::class,'state_id');
    }
    public function activity() {
       return $this->belongsTo(ActivityType::class,'activity_id');
    }
    public function products()
    {
        return $this->hasMany('App\Models\ProductModel','product_vender_id','id');
    }
    public static function get_stores($where = [], $filter = [], $limit = '', $offset = 0)
    {
        $radius = 20;
        $settings = SettingsModel::first();
        if($settings){
           $radius= $settings->store_radius;
        }
        $stores = VendorDetailsModel::withCount('products')->where($where)->select('vendor_locations.id as location_id','vendor_details.user_id as id', 'company_name', 'vendor_locations.location', 'logo','vendor_locations.latitude','vendor_locations.longitude','cover_image','open_time','close_time')
        ->leftjoin('product','product.product_vender_id','=','vendor_details.user_id')
        ->join('vendor_locations','vendor_locations.user_id','=','vendor_details.user_id')
        ->where(['product.product_status'=>1,'product.deleted'=>0])
        ->whereNotNull('vendor_locations.longitude');


        if (isset($filter['lat']) && $filter['long']) {
        $lat = $filter['lat'];
        $long = $filter['long'];
        $distance =
            "6371 * acos (
            cos ( radians( CAST (vendor_locations.latitude AS double precision) ) )
            * cos( radians( CAST ({$lat} AS double precision) ) )
            * cos( radians( CAST ({$long} AS double precision) ) - radians( CAST (vendor_locations.longitude AS double precision) ) )
            + sin ( radians( CAST (vendor_locations.latitude AS double precision) ) )
            * sin( radians ( CAST ({$lat} AS double precision) ) )
        )";
        $stores->selectRaw("$distance as distance")->orderBy('distance','asc');
        }

        // if (isset($filter['distance']) && $filter['distance']) {
        //     $filter_distance = $filter['distance'];
        //     $stores->whereRaw("$distance<=$filter_distance");
        // }
        $stores->whereRaw("$distance<=$radius");
        if (isset($filter['search_text']) && $filter['search_text']) {
            $srch = $filter['search_text'];
            $stores->whereRaw("(company_name ilike '%$srch%' OR location ilike '%$srch%' OR product.product_name ilike '%$srch%')");
        }
        // $stores = DB::table('stores');
        if($limit !="") {
            $stores->limit($limit)->skip($offset);
        }
        // if (isset($filter['distance']) && $filter['distance']) {
        // $stores->orderBy('distance','asc');
        // }

        if (isset($filter['master_product_id']) && $filter['master_product_id']) {
            $stores->where('master_product',$filter['master_product_id']);
        }
        if (isset($filter['activity_id']) && $filter['activity_id']) {
            $stores->where('product.activity_id',$filter['activity_id']);
        }
        if (isset($filter['category_id']) && $filter['category_id']) {
            $cat = Categories::find($filter['category_id']);
            if($cat && (strtolower(str_replace(' ', '', $cat->name)) == 'dinein' || strtolower(str_replace(' ', '', $cat->name)) == 'pickup') ){
                $stores->where('is_dinein',1);
            }else{
                $stores->whereRaw("product.id in (select product_id from product_category where category_id = '".$filter['category_id']."' or category_id in (select id from category where parent_id='".$filter['category_id']."')) ");
            }
        }

        if (isset($filter['featured']) && $filter['featured']) {
        $stores->where('featured_flag',$filter['featured']);
        }


        $stores->join('users','users.id','=','vendor_details.user_id')->where(['users.deleted'=>0,'verified'=>1]); 

        if (isset($filter['ignore_id']) && $filter['ignore_id']) {
           $stores->where('users.id','!=',$filter['ignore_id']);
        }

        $stores->groupBy('vendor_details.id','vendor_locations.id')->get();
        // $stores->distinct('vendor_details.user_id')->get();


        return $stores;
        
    }
    public static function bk_get_stores($where = [], $filter = [], $limit = '', $offset = 0)

    {
        
        $stores = VendorDetailsModel::withCount('products')->where($where)->select('vendor_details.user_id as id', 'company_name', 'location', 'logo','latitude','longitude','cover_image','open_time','close_time')
        ->leftjoin('product','product.product_vender_id','=','vendor_details.user_id')
        ->where(['product.product_status'=>1,'product.deleted'=>0])
        ->whereNotNull('longitude');


        if (isset($filter['lat']) && $filter['long']) {
        $lat = $filter['lat'];
        $long = $filter['long'];
        $distance =
            "6371 * acos (
            cos ( radians( CAST (vendor_details.latitude AS double precision) ) )
            * cos( radians( CAST ({$lat} AS double precision) ) )
            * cos( radians( CAST ({$long} AS double precision) ) - radians( CAST (vendor_details.longitude AS double precision) ) )
            + sin ( radians( CAST (vendor_details.latitude AS double precision) ) )
            * sin( radians ( CAST ({$lat} AS double precision) ) )
        )";
        $stores->selectRaw("$distance as distance")->orderBy('distance','asc');
        }

        if (isset($filter['distance']) && $filter['distance']) {
            $filter_distance = $filter['distance'];
            $stores->whereRaw("$distance<=$filter_distance");
        }
        if (isset($filter['search_text']) && $filter['search_text']) {
            $srch = $filter['search_text'];
            $stores->whereRaw("(company_name ilike '%$srch%' OR location ilike '%$srch%' OR product.product_name ilike '%$srch%')");
        }
        // $stores = DB::table('stores');
        if($limit !="") {
            $stores->limit($limit)->skip($offset);
        }
        // if (isset($filter['distance']) && $filter['distance']) {
        // $stores->orderBy('distance','asc');
        // }

        if (isset($filter['master_product_id']) && $filter['master_product_id']) {
         $stores->where('master_product',$filter['master_product_id']);
        }
        if (isset($filter['category_id']) && $filter['category_id']) {
            $cat = Categories::find($filter['category_id']);
            if($cat && (strtolower(str_replace(' ', '', $cat->name)) == 'dinein'  || strtolower(str_replace(' ', '', $cat->name)) == 'pickup')){
                $stores->where('is_dinein',1);
            }else{
                $stores->whereRaw("product.id in (select product_id from product_category where category_id = '".$filter['category_id']."' or category_id in (select id from category where parent_id='".$filter['category_id']."')) ");
            }
        }

        if (isset($filter['featured']) && $filter['featured']) {
        $stores->where('featured_flag',$filter['featured']);
        }


        $stores->join('users','users.id','=','vendor_details.user_id')->where(['users.deleted'=>0,'verified'=>1]); 

        if (isset($filter['ignore_id']) && $filter['ignore_id']) {
           $stores->where('users.id','!=',$filter['ignore_id']);
        }

        $stores->groupBy('vendor_details.id')->get();
        // $stores->distinct('vendor_details.user_id')->get();


        return $stores;
        
    }

    public static function get_stores_for_featured($where = [], $filter = [], $limit = '', $offset = 0)
    {
       
        $stores = VendorDetailsModel::withCount('products')->where($where)->select('vendor_details.user_id as id', 'company_name', 'location', 'logo','latitude','longitude','cover_image','product.id as product_id','product.default_attribute_id','product.product_name')
        ->leftjoin('product','product.product_vender_id','=','vendor_details.user_id')
        ->where(['product.product_status'=>1,'product.deleted'=>0]);

         if (isset($filter['lat']) && $filter['long']) {
        $lat = $filter['lat'];
        $long = $filter['long'];
        $distance =
            "6371 * acos (
            cos ( radians( CAST (vendor_details.latitude AS double precision) ) )
            * cos( radians( CAST ({$lat} AS double precision) ) )
            * cos( radians( CAST ({$long} AS double precision) ) - radians( CAST (vendor_details.longitude AS double precision) ) )
            + sin ( radians( CAST (vendor_details.latitude AS double precision) ) )
            * sin( radians ( CAST ({$lat} AS double precision) ) )
        )";
        $stores->selectRaw("$distance as distance");
        }



        if (isset($filter['distance']) && $filter['distance']) {
            $filter_distance = $filter['distance'];
            $stores->whereRaw("$distance<=$filter_distance");
        }
        if (isset($filter['search_text']) && $filter['search_text']) {
            $srch = $filter['search_text'];
            $stores->where('product.product_name', 'ilike', '%' . $srch . '%');
        }
        // $stores = DB::table('stores');
        if($limit !="") {
            $stores->limit($limit)->skip($offset);
        }
        // if (isset($filter['distance']) && $filter['distance']) {
        // $stores->orderBy('distance','asc');
        // }

        if (isset($filter['master_product_id']) && $filter['master_product_id']) {
         $stores->where('master_product',$filter['master_product_id']);
        }

        if (isset($filter['featured']) && $filter['featured']) {
        $stores->where('featured_flag',$filter['featured']);
        }

        $stores->join('users','users.id','=','vendor_details.user_id')->where(['users.deleted'=>0,'verified'=>1]); 

        $stores->distinct('vendor_details.user_id')->get();


        return $stores;
        
    }


}
