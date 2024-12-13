<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderProductsModel;
use App\Models\CouponVendorServiceOrders;
use App\Models\OrderServiceModel;
use App\Models\ServiceOrderRejected;
use App\Models\OrderModel;
use App\Models\ServiceAssignedVendors;
use Auth;
use DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        
        $page_heading = "Vendor Dashboard";

        $vendor_id = Auth::user()->id;
        $pending = 0;
        $accepted = 0;
        $delivered = 0;
        $rejected = 0;
        $ready_for_delivery = 0;
        $dispatched = 0;
        $all = 0;
        $ongoing = 0;
        $latest_orders = [];
        if(Auth::user()->activity_id == 5 && !Auth::user()->is_dinein && !Auth::user()->is_delivery){
            return redirect('portal/my_profile');
        }

        if(Auth::user()->activity_id == 7 || Auth::user()->activity_id == 5 || Auth::user()->activity_id == 3){
            $all = OrderModel::where('vendor_id',Auth::user()->id)->count();
            $pending = OrderModel::where('vendor_id',Auth::user()->id)->where('status',0)->count();
            $accepted = OrderModel::where('vendor_id',Auth::user()->id)->where('status',1)->count();
            $ready_for_delivery = OrderModel::where('vendor_id',Auth::user()->id)->where('status',2)->count();
            $dispatched = OrderModel::where('vendor_id',Auth::user()->id)->where('status',3)->count();
            $delivered = OrderModel::where('vendor_id',Auth::user()->id)->where('status',4)->count();
            $rejected = OrderModel::where('vendor_id',Auth::user()->id)->where('status',10)->count();

            $latest_orders =  OrderModel::where('orders.vendor_id',Auth::user()->id)->select('orders.*','users.name',DB::raw("CONCAT(users.first_name,' ',users.last_name) as customer_name"))->leftjoin('users','users.id','orders.user_id')->with(['customer'])->orderBy('orders.order_id','DESC')->limit(6)->get();
            foreach($latest_orders as $key => $val){
                $lowest_order_prd_status = OrderProductsModel::where('order_id', $val->order_id)->orderby('order_status', 'asc')->first();
                    if (isset($lowest_order_prd_status->order_status)) {
                        $latest_orders[$key]->status = $lowest_order_prd_status->order_status;
                        $latest_orders[$key]->status_text = order_status($lowest_order_prd_status->order_status);
                    } else {
                        $latest_orders[$key]->status_text = order_status($val->status);
                    }
            }


        }else if(Auth::user()->activity_id == 6 || Auth::user()->activity_id == 4 || Auth::user()->activity_id == 1){
            $rejected = $this->service_orders_count(10);
           
           $pending = $this->service_orders_count(0);
           $accepted = $this->service_orders_count(1);
           
           
           $ongoing = $this->service_orders_count(3);
           $all = $this->service_orders_count();
        }


        //service order list end
        

        $order = OrderProductsModel::where('vendor_id',$vendor_id)->get()->count();
        return view('portal.dashboard', compact('page_heading','order','pending','ongoing','accepted','rejected','all','delivered','ready_for_delivery','dispatched','latest_orders'));
    }
    public function service_orders_count($status="")
    {
        $notrelated_orders = [];
        $vendor_id = Auth::user()->id;
        $vendor_coupon_orders  = CouponVendorServiceOrders::pluck('order_id')->toArray();
        if(!empty($vendor_coupon_orders))
        {
        $vendor_coupon_orders  = array_unique($vendor_coupon_orders);   
        $notrelated_orders = []; 
        foreach ($vendor_coupon_orders as $key => $value) {
           //$check_vendor = CouponVendorServiceOrders::where(['vendor_id'=>$vendor_id,'order_id'=>$value])->get()->count();
           $check_vendor = CouponVendorServiceOrders::where(['vendor_id'=>$vendor_id,'order_id'=>$value])->get()->count();
           if($check_vendor == 0)
           {
             $notrelated_orders[] = $value;
           }

        }
        
        }

        
        
        $vendor_services = DB::table('vendor_services')->where('vendor_id',$vendor_id)->get()->toArray();
        $vendor_services = $serar = array_column($vendor_services, 'service_id');
        $vendor_services = implode(",",$vendor_services);
        if(empty($vendor_services))
        {
            $vendor_services = 0;
        }
         $rejected = ServiceOrderRejected::select('service_order_item_id')->where('vendor_id',$vendor_id)->pluck('service_order_item_id')->toArray();

        $list =  OrderServiceModel::select('*','orders_services.created_at','orders_services_items.id as item_id','orders_services_items.hourly_rate','orders_services_items.qty')->leftjoin('users','users.id','=','orders_services.user_id')
        ->join('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')
        ->whereNotIn('orders_services_items.id',$rejected)
    //     ->leftjoin('orders_services_rejected', function($join) use ($vendor_id)
    //     {
    //   // $join->on('orders_services_rejected.service_order_id', '!=', 'orders_services_items.order_id');
    //     $join->on('orders_services_rejected.vendor_id', '!=', DB::raw("'".$vendor_id."'"));
    //     })
        
        ->whereIn('orders_services_items.service_id',explode(",", $vendor_services))
        ->whereNotIn('orders_services.order_id',$notrelated_orders)
        ->where(function($query) use($vendor_id)
            {
            $query->where('orders_services_items.accepted_vendor','=','0')
           ->orWhere('orders_services_items.accepted_vendor',$vendor_id);
            });
        
       
        //->leftjoin('user_address','user_address.user_id','=','orders.user_id')
        //->leftjoin('users','users.id','orders.user_id')->with(['customer'=>function($q) use($name){
        //    $q->where('display_name','like','%'.$name.'%');
        // }]);
        if(!empty($name))
        {
             $list =$list->whereRaw("concat(first_name, ' ', last_name) like '%" .$name. "%' ");
        }
        if(!empty($order_id)){
            $list=$list->where(function ($query) use ($order_id) {
            $query->where('orders_services.order_id','like','%'.$order_id.'%' );
            $query->orWhere('orders_services.order_no', "like", "%" . $order_id . "%");
        });
        }
        if(!empty($from)){
            $list=$list->whereDate('orders_services.created_at','>=',$from.' 00:00:00');
        }
        if(!empty($to)){
            $list=$list->where('orders_services.created_at','<=',$to.' 23:59:59');
        }
 
        $created = Auth::user()->created_at;
        
        if(is_numeric($status))
        {
            $list=$list->where('orders_services_items.order_status',$status); 
        }
        $list = $list->whereIn('orders_services.order_id',ServiceAssignedVendors::where(['vendor_user_id'=>$vendor_id])->whereIn('service_status',[0,1])->select('order_id'));
        $list=$list->orderBy('orders_services_items.id','DESC')
        ->where('orders_services_items.created_at','>=',$created)
        ->distinct('orders_services_items.id');
        return $list->get()->count();
        
    }

}
