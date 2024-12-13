<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class OrderServiceItemsModel extends Model
{
    protected $table = "orders_services_items";
    protected $primaryKey = "id";
 
    public function service(){
        return $this->hasOne(Service::class, 'id', 'service_id');
   }
   public function vendor(){
        return $this->hasOne(VendorModel::class, 'id', 'accepted_vendor');
   }
   public function customer(){
    return $this->hasOne(User::class, 'id', 'user_id');
   }
   static function tickets($order_id){
    return DB::table('tickets')->where('order_id',$order_id)->get();
   }
   public static function order_list($vendor_id){

    $list=OrderModel::select('order_products.*','orders.*','user_address.address','user_address.phone',DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as customer_name"))->join('order_products','order_products.order_id','=','orders.order_id')
              ->join('res_users','res_users.id','=','orders.user_id')
              ->leftjoin('user_address','user_address.user_id','=','orders.user_id')
              ->orderBy('orders.order_id','desc')
              ->distinct('orders.order_id')
               ->where('vendor_id',$vendor_id)->paginate(10);
               if($list->total()){     
                foreach($list->items() as $key=>$row){
               
                    $list->items()[$key]->tickets=OrderModel::tickets($row->id);
                    $list->items()[$key]->product_name=OrderProductsModel::product_name($row->product_id,$row->product_type);
                    $list->items()[$key]->vendor_total = DB::table('order_products')->where('vendor_id',$vendor_id)->where('order_id',$row->order_id)->sum('total');
                    
                }
              }
      return $list;
   }
   public static function order_details($vendor_id,$order_id){

    $list=OrderModel::select('order_products.*','orders.*','user_address.address','user_address.phone',DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as customer_name"))->join('order_products','order_products.order_id','=','orders.order_id')
              ->join('res_users','res_users.id','=','orders.user_id')
              ->leftjoin('user_address','user_address.user_id','=','orders.user_id')
              ->where('orders.order_id',$order_id)
              ->distinct('orders.order_id')
               ->where('vendor_id',$vendor_id)->paginate(10);
               if($list->total()){     
                foreach($list->items() as $key=>$row){
                    $filter = ['order_id'=>$order_id,'order_products.vendor_id'=>$vendor_id];
                    $list->items()[$key]->tickets=OrderModel::tickets($row->id);
                    $list->items()[$key]->vendor_total = DB::table('order_products')->where('vendor_id',$vendor_id)->where('order_id',$row->order_id)->sum('total');
                   
                    $order_products = OrderProductsModel::product_details($filter);
                    $order_events = OrderProductsModel::events_details($filter);
                    $order_services = OrderProductsModel::services_details($filter);
                    $order_packages = OrderProductsModel::packages_details($filter);
                    $list->items()[$key]->products = process_product_data($order_products);
                    $list->items()[$key]->events = process_events_data($order_events);
                    $list->items()[$key]->services = process_service_data($order_services);
                    $list->items()[$key]->packages = process_package_data($order_packages);

                    
                }
              }
      return $list;
   }
   public static function order_details_email($order_id){

    $list=OrderModel::select('order_products.*','orders.*','user_address.address','user_address.phone',DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as customer_name"),'res_users.firebase_user_key','res_users.fcm_token')->join('order_products','order_products.order_id','=','orders.order_id')
              ->join('res_users','res_users.id','=','orders.user_id')
              ->leftjoin('user_address','user_address.id','=','orders.address_id')
              ->where('orders.order_id',$order_id)
              ->distinct('orders.order_id')
               ->paginate(10);
               if($list->total()){     
                foreach($list->items() as $key=>$row){
                    $filter = ['order_id'=>$order_id];
                    $list->items()[$key]->tickets=OrderModel::tickets($row->id);
                    $list->items()[$key]->vendor_total = DB::table('order_products')->where('order_id',$row->order_id)->sum('total');
                   
                    $order_products = OrderProductsModel::product_details($filter);
                    $order_events = OrderProductsModel::events_details($filter);
                    $order_services = OrderProductsModel::services_details($filter);
                    $order_packages = OrderProductsModel::packages_details($filter);
                    $list->items()[$key]->products = process_product_data($order_products);
                    $list->items()[$key]->events = process_events_data($order_events);
                    $list->items()[$key]->services = process_service_data($order_services);
                    $list->items()[$key]->packages = process_package_data($order_packages);

                    
                }
              }
      return $list;
   }
   public static function get_orders($filter=[]){
        $data = DB::table('orders')
                ->orderBy('orders.order_id','desc')
                ->select('orders.*');
                if(!empty($filter['user_id']))
                {
                 $data->where('orders.user_id',$filter['user_id']);
                }
                if(!empty($filter['status']))
                {
                 $data->where('orders.status',$filter['status']);
                }
                if(!empty($filter['type']))
                {
                  $data->whereIn('orders.oder_type',$filter['type']); 
                }
        return $data;
    }
    public static function get_order_details($filter=[]){
        $data = DB::table('orders')
                ->select('orders.*');
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
}
