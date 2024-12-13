<?php

namespace App\Models;
use DB;

use Illuminate\Database\Eloquent\Model;

class OrderProductsModel extends Model
{
    //
    protected $table = "order_products";
    protected $primaryKey = "id";

    public $timestamps=false;

    public function order()
    {
        return $this->hasOne(OrderModel::class,'order_id','id');
    }
    public function vendor()
    {
        return $this->hasOne(User::class,'id','vendor_id');
    }
    public function product()
    {
        return $this->hasOne(Product::class,'id','product_id');
    }
    public function variant()
    {
        return $this->hasOne(ProductSelectedAttributeList::class,'product_attribute_id','product_attribute_id');
    }

    static function product_name($id,$type){
        if($type==1){ //event
            $data=Event::find($id);
            $name=isset($data->event_title)?$data->event_title:'';
        }else{
            $name='';
        }
        return $name;
    }
    public static function get_order_details($filter=[]){
        $data = DB::table('orders')
                ->leftjoin('users','users.id','=','orders.user_id')
                ->select('orders.*',DB::raw("CONCAT(users.first_name,' ',users.last_name) as customer_name"));
                if(!empty($filter['user_id']))
                {
                 $data->where('orders.user_id',$filter['user_id']);
                }
                if(!empty($filter['order_id']))
                {
                 $data->where('orders.order_id',$filter['order_id']);
                }
       
        return $data;
    }
    static function product_details($filter)
    {
          $data = DB::table('order_products')
          ->select('order_products.*','order_products.quantity as order_qty','order_products.price as order_price','order_products.total as order_total','order_products.discount as order_discount','product.product_name','product_selected_attribute_list.image',DB::raw("CONCAT(users.name) as name"))
          ->where($filter)
          ->join("product","product.id","=","order_products.product_id") 
          ->leftjoin("product_category","product_category.product_id","=","product.id")
          ->leftjoin('users','users.id','=','product.product_vender_id')
          ->leftjoin('product_selected_attribute_list','product_selected_attribute_list.product_attribute_id','order_products.product_attribute_id')
          ->get();
         

        // $data = DB::table('order_products')
        // ->select('order_products.*','order_products.quantity as order_qty','order_products.price as order_price','order_products.total as order_total','order_products.discount as order_discount','product.product_name','product_selected_attribute_list.image',DB::raw("CONCAT(users.first_name,' ',users.last_name) as vendor_name"))
        // ->where($filter)
        // ->join("product","product.id","=","order_products.product_id") 
        // ->leftjoin("product_category","product_category.product_id","=","product.id")
        // ->leftjoin("category","category.id","=","product_category.category_id")
        // ->leftjoin('users','users.id','=','product.product_vender_id')
        // ->leftjoin('product_selected_attribute_list','product_selected_attribute_list.product_attribute_id','order_products.product_attribute_id')
        // ->where('order_products.product_type','1')
        // ->get();


        return $data;
    }
    
    static function events_details($filter)
    { 
        $data = DB::table('order_products')
        ->select('order_products.*','event.*','order_products.quantity as order_qty','order_products.price as order_price','order_products.total as order_total','order_products.discount as order_discount','event_selected_category.event_category_id','event_category.name as event_category',DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as vendor_name"))
        ->where($filter)
        ->join("event","event.event_id","=","order_products.product_id") 
        ->leftJoin('event_selected_category','event_selected_category.event_id','=','event.event_id')
        ->leftJoin('event_category','event_category.id','=','event_selected_category.event_category_id')
        ->leftjoin('res_users','res_users.id','=','event.vendor_id')
        ->where('order_products.product_type','2')
        ->distinct('order_products.id')
        ->get();
        
        return $data;
    }
    static function services_details($filter)
    {
        $data = DB::table('order_products')
        ->select('order_products.*','services.*','services_time_slot.date as booking_date','services_time_slot.time as booking_time','order_products.quantity as order_qty','order_products.price as order_price','order_products.total as order_total','order_products.discount as order_discount',DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as vendor_name"))
        ->where($filter)
        ->join("services","services.service_id","=","order_products.product_id")
        ->leftjoin("services_time_slot","services_time_slot.order_products_id","=","order_products.id")
        ->leftjoin('res_users','res_users.id','=','services.service_vendor_user_id')
        ->whereColumn('services_time_slot.service_id', '=', 'order_products.product_id')
        ->where('order_products.product_type','3')
        ->get();
        
        return $data;
    }
    static function packages_details($filter)
    {
        $data = DB::table('order_products')
        ->select('order_products.*','packages.*','order_products.quantity as order_qty','order_products.price as order_price','order_products.total as order_total','order_products.discount as order_discount',DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as vendor_name"),'order_products.id as id')
        ->where($filter)
        ->join("packages","packages.id","=","order_products.product_id") 
        ->leftjoin('res_users','res_users.id','=','packages.vendor_id')
        ->where('order_products.product_type','4')
        ->get();
        
        return $data;
    }
    static function check_purchase($filter)
    {
        $data = DB::table('order_products')
           ->where($filter)
           ->leftjoin('orders','orders.order_id','=','order_products.order_id')
           ->count();
        if ($data > 0)
        {
        return true;
        }
        else
        {
        return false;
        }
    }
}
