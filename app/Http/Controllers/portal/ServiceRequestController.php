<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Auth;
use Kreait\Firebase\Database;
use App\Models\OrderServiceModel;
use App\Models\OrderServiceItemsModel;
use App\Models\User;
use App\Models\OrderRequestViews;
use App\Models\VendorDetailsModel; 
use App\Models\ServiceOrderStatusHistory;
use App\Models\ServiceOrderRejected;
use App\Models\SettingsModel;
use App\Models\ServiceOrderMuted;
use App\Models\ServiceAssignedVendors;
use App\Models\CouponVendorServiceOrders;
use App\Exports\ExportReports;
use Maatwebsite\Excel\Facades\Excel;
class ServiceRequestController extends Controller
{

    public function index(Request $request)
    { 

        $vendor_id = Auth::user()->id;
        $muted = 1;
        $audio = 0;
        $notrelated_orders = [];
        
        $page_heading = "Service Request"; 
        $order_id = $_GET['order_id'] ?? '';
        $name = $_GET['name'] ?? '';
        $status = $_GET['status'] ?? '';
        $from = !empty($_GET['from'])?date('Y-m-d',strtotime($_GET['from'])): '';
        $to = !empty($_GET['to']) ?date('Y-m-d',strtotime($_GET['to'])): '';

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

        $list =  OrderServiceModel::select('*','orders_services.created_at as created_at','orders_services_items.id as item_id','orders_services_items.hourly_rate','orders_services_items.qty','orders_services.vat','orders_services.discount','orders_services.service_charge')->leftjoin('users','users.id','=','orders_services.user_id')
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
        if($name)
        {
             $list =$list->whereRaw("concat(first_name, ' ', last_name) like '%" .$name. "%' ");
        }
        if($order_id){
            $list=$list->where(function ($query) use ($order_id) {
            $query->where('orders_services.order_id','like','%'.$order_id.'%' );
            $query->orWhere('orders_services.order_no', "like", "%" . $order_id . "%");
        });
        }
        if($from){
            $list=$list->whereDate('orders_services.created_at','>=',$from.' 00:00:00');
        }
        if($to){
            $list=$list->where('orders_services.created_at','<=',$to.' 23:59:59');
        }
 
        $created = Auth::user()->created_at;
        
        if(is_numeric($status))
        {
            $list=$list->where('orders_services_items.order_status',$status); 
        }
        //$list = $list->whereIn('orders_services.order_id',ServiceAssignedVendors::where(['vendor_user_id'=>$vendor_id])->whereIn('service_status',[0,1])->select('order_id'));
        $list=$list->orderBy('orders_services_items.order_id','DESC')
        ->where('orders_services_items.created_at','>=',$created)
        ->distinct('orders_services_items.order_id');
        
        if (isset($_GET['from'])) {
        $list = $list->paginate(500);
        }
        else
        {
          $list=$list->paginate(10);  
        }

       
        foreach ($list as $key => $value) {
            $list[$key]->rejected = ServiceOrderRejected::where(['vendor_id'=>$vendor_id,'service_order_id'=>$value->order_id])->get()->count();
            $list[$key]->read_vendor = OrderRequestViews::where(['vendor'=>$vendor_id,'service_order'=>$value->order_id])->get()->count();
            $list[$key]->admin_commission = OrderServiceItemsModel::where('order_id',$value->order_id)->sum('admin_commission');
            if($list[$key]->rejected > 0)
            {
            $list[$key]->read_vendor = 1;
            }
            $list[$key]->muted = ServiceOrderMuted::where(['vendor_id'=>$vendor_id,'service_order_id'=>$value->order_id])->get()->count();
            if($list[$key]->muted == 0)
            {
                $muted = 0;
            }
            if($value->created_at >= date('Y-m-d 00:00:00'))
            {
                if($muted == 0)
                {
                    $audio = 1;
                }
            }
        }
        
       
        return view('portal.service_request.list',compact('page_heading','list','order_id','name','from','to','muted','audio','status'));
    }
    public function commission(Request $request)
    {
        $page_heading = "Commission Report"; 
        $order_id = $_GET['order_id'] ?? '';
        $name = $_GET['name'] ?? '';
        $from = !empty($_GET['from'])?date('Y-m-d',strtotime($_GET['from'])): '';
        $to = !empty($_GET['to']) ?date('Y-m-d',strtotime($_GET['to'])): '';
        $list =  OrderModel::select('orders.*',DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as vendor_name"),'order_products.admin_commission as ad_comm','order_products.vendor_commission as vd_comm','order_products.total as subtot')
           ->leftjoin('order_products','order_products.order_id','orders.order_id')
           ->leftjoin('res_users','res_users.id','order_products.vendor_id');
        $list->orderBy('vendor.order_id','desc');
        
        if($order_id){
            $list=$list->where('orders.order_id',$order_id);
        }
        if($from){
            $list=$list->whereDate('orders.created_at','>=',$from.' 00:00:00');
        }
        if($to){
            $list=$list->where('orders.created_at','<=',$to.' 23:59:59');
        }
        $list=$list->where('order_products.order_status',config("global.order_status_delivered"));
        if($request->submit != "export")
        {
        $list=$list->paginate(10);    
        }
        else
        {
        $list=$list->paginate(1000);    
        }
        
        
        if($request->submit == "export")
        {
            //export
            

            $rows = array();
            $i = 1;
            foreach ($list as $key => $val) {
                
            if($val->payment_mode==1)
            {
            $payment = "COD";
            }
            else
            {
            $payment = "CARD";
            }
            
            $rows[$key]['i'] = $i;
            $rows[$key]['order_id'] = $val->order_id;
            $rows[$key]['invoice_id'] = ($val->invoice_id)??'-';
            $rows[$key]['vendor'] = ($val->vendor_name)??'-';
            $rows[$key]['admin_commission'] = ($val->ad_comm)??'0';
            $rows[$key]['vendor_earning'] = $val->vd_comm;
            $rows[$key]['total'] = $val->subtot;
            $rows[$key]['payment_mode'] = $payment;
            $rows[$key]['order_date'] = get_date_in_timezone($val->created_at, 'd-M-y H:i A');
            $i++;
            }
            $headings = [
            "#",
            "Order ID",
            "Invoice ID",
            "Vendor",
            "Admin Commission",
            "Vendor Earning",
            "Total",
            "Payment Mode",
            "Order Date",
             ];
            $coll = new ExportReports([$rows], $headings);
            $ex = Excel::download($coll, 'products_' . date('d_m_Y_h_i_s') . '.xlsx');
            if (ob_get_length()) ob_end_clean();
            return $ex;
            //export end
        }
        else
        {
        return view('portal.orders.commission',compact('page_heading','list','order_id','name','from','to'));
        }
    }
    
    
   public function details(Request $request,$id,$item_id="")
    {
        $userid = Auth::user()->id;
        //check order rjected
        $chek = ServiceOrderRejected::where(['vendor_id'=>$userid,'service_order_id'=>$id])->get()->count();

        if($chek == 1)
        {
            return redirect()->to('portal/service_request');
        }

        $page_heading = "Service Request Details"; 
        $list =  OrderServiceModel::select("orders_services.*","users.name as customer_name","user_address.*","users.email","users.dial_code",'users.phone','orders_services.status as status')->leftjoin('users','users.id','orders_services.user_id')->where(['order_id'=>$id])
        ->leftjoin('user_address','user_address.id','=','orders_services.address_id')
        ->first();
            
        if(!empty($list))
        {
         $list->service_details=OrderServiceItemsModel::select('orders_services_items.*','orders_services_items.id as id','service.name','service.image','description','vendor.first_name','vendor.last_name')
         ->leftjoin('service','service.id','=','orders_services_items.service_id')
         ->leftjoin('users as vendor','vendor.id','=','orders_services_items.accepted_vendor')
         ->where('order_id',$list->order_id)->get();   
        // ->where('orders_services_items.id',$item_id)
        }

        foreach ($list as $key => $value) {
            foreach ($list->service_details as $key_sub => $value_details) {
                //$list->grand_total = ($value_details->hourly_rate * $value_details->qty) - $value_details->discount + $value_details->vat + $list->service_charge;
                //$list->vat = $value_details->vat;
                //$list->discount = $value_details->discount;
            }
            

        }
        
        $orderview = OrderRequestViews::where(['service_order'=>$id,'vendor'=>$userid])->first();
        if(empty($orderview))
        {
        $orderview = new OrderRequestViews();
        }
        $orderview->vendor = $userid;
        $orderview->service_order = $id;
        $orderview->save();

      
        return view('portal.service_request.details',compact('page_heading','list'));
    }

    public function edit_order(Request $request,$id)
    {
        $page_heading = "Orders Details Edit"; 
        //$list =  OrderProductsModel::select('orders.*',DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as customer_name"))->->leftjoin('res_users','res_users.id','orders.user_id')->with('vendor')->where(['order_id'=>$id])->paginate(10);
        //if($list->total()){     
        //foreach($list->items() as $key=>$row){
       
            //$list->items()[$key]->tickets=OrderModel::tickets($row->id);
            //$list->items()[$key]->product_name=OrderProductsModel::product_name($row->product_id,$row->product_type);
        //}
        // }
            $filter['order_id']  = $id;
            
            $page = (int)$request->page??1;
            $limit= 10;
            $offset = ($page - 1) * $limit;
            $list = OrderProductsModel::get_order_details($filter)->skip($offset)->take($limit)->get();
            $list = process_order($list);

            
         
        return view('portal.orders.details_edit',compact('page_heading','list'));
    }
    public function change_status(Request $request)
    {
      
         $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        // update on order table
        $userid = Auth::user()->id;
        $user = User::find($userid);
        $name = $users->name ?? $user->first_name . ' ' . $user->last_name;

        $store_id = $user->id;
         
        $order_service = OrderServiceModel::where('order_id',$request->order_id)->where('accepted_vendor','=',0)->orWhere('accepted_vendor','=',$userid)->first();
        $order = OrderServiceItemsModel::where(['order_id'=>$request->order_id,'id'=>$request->id])->where('accepted_vendor','=',0)->orWhere('accepted_vendor','=',$userid)->first();
        $order = OrderServiceItemsModel::find($request->id);
        
        if(!$order){
            return response()->json([
                'status' => "0",
                'message' => 'Invalid order_id',
                'oData' => (object)[],
                'errors' => (object) [],
            ], 401);
        }
        if($request->order_status == 4) 
        {
        $order_accepted = OrderServiceItemsModel::where(['order_id'=>$request->order_id,'id'=>$request->id,'accepted_vendor'=>$userid])->get()->count();
        
        }
        else
        {
            if($request->order_status == 1)
            {
                if($order->order_status == 0)
                {
            }
            else
            {
            $status = "0";
            $message = "This order already accepetd by another vendor!";
            echo json_encode(['status'=>$status,'message'=>$message]); die;
            }
            
            }
            
            $order_accepted = 1;
        }
        if($order_accepted == 1) 
        {
        $order->order_status = $request->order_status;
       
        
        $order->accepted_vendor = $userid;
        $order->accepted_date   = gmdate('Y-m-d H:i:s');

         
        //save vendor commission
        $vendor_data = VendorDetailsModel::where('user_id',$userid)->first();

        
        $vendor_commission = 0;
        $t_amount = ($order->hourly_rate * $order->qty) - $order->discount;
       
       
        $order->save();

        //update order main
        $orderproducts = OrderServiceItemsModel::where('order_id',$request->order_id)->orderBy('order_status','desc')->first();
        OrderServiceModel::where('order_id',$orderproducts->order_id)->update(['status'=>$orderproducts->order_status]);
        OrderServiceItemsModel::where('order_id',$request->order_id)->update(['order_status'=>$orderproducts->order_status,'accepted_vendor'=>$orderproducts->accepted_vendor]);

        if($request->order_status == 4)
        {    
            $ad_admin_commission = 0;
            $ad_vendor_commission = 0;
            
             $dataitems = OrderServiceItemsModel::find($request->id);
             $serviceitems = OrderServiceItemsModel::where('order_id',$request->order_id)->get();
             foreach ($serviceitems as $key => $value) {
                    $settings  = SettingsModel::first();
                    $vendor_data = VendorDetailsModel::where('user_id',$value->accepted_vendor)->first();
                    $vendor_commission = 0;
                    $t_amount = ($value->hourly_rate * $value->qty) - $value->discount;
                    //$t_amount = ($order->hourly_rate * $order->qty) - $order->discount;
                    if(!empty($vendor_data->servicecommission && !empty($t_amount)))
                    {

                      $vendor_commission = $t_amount * $vendor_data->servicecommission/100;
                    }
                    else
                    { 
                       if(!empty($settings->admin_commission) && !empty($t_amount))
                       {
                         $vendor_commission  = $t_amount * $settings->admin_commission/100;
                       }else{
                           $vendor_commission  = $t_amount * $settings->admin_commission/100;
                       }
                       
                    }
                  $datamainorder = OrderServiceModel::select('service_charge')->where('order_id',$orderproducts->order_id)->first();
                  $service_charge = number_format($datamainorder->service_charge/count($serviceitems), 2, '.', '');
                 $dataitems = OrderServiceItemsModel::find($value->id);
                 $dataitems->admin_commission  = $vendor_commission + $service_charge;
                 $dataitems->vendor_commission = $t_amount - $vendor_commission + $value->vat;
                 $dataitems->save();

                 $ad_admin_commission += $vendor_commission;
                 $ad_vendor_commission += $t_amount - $vendor_commission + $value->vat;
             }
             $ad_admin_commission = $ad_admin_commission + $datamainorder->service_charge;
             $order_service->admin_commission = $ad_admin_commission;
        $order_service->vendor_commission = $ad_vendor_commission;
        $order_service->save();
        }



        if($request->order_status == 10)
        {
            $check = ServiceOrderRejected::where(['vendor_id'=>$userid,'service_order_id'=>$request->order_id])->get()->count();
            if($check <= 0)
            {
                $order_rejected = new ServiceOrderRejected();
                $order_rejected->vendor_id = $userid;
                $order_rejected->service_order_id = $request->order_id;
                $order_rejected->service_order_item_id = 0;
                $order_rejected->created_at = gmdate('Y-m-d H:i:s');
                $order_rejected->updated_at = gmdate('Y-m-d H:i:s');
                $order_rejected->save();
            }
            
        }

        //check all vendors rejected this order if true send rejected push to customer 
        $vendor_rejected = [];
        $check_vendor_in = CouponVendorServiceOrders::select('vendor_id')->where('order_id',$request->order_id)->pluck('vendor_id')->toarray();

        
        $vendor_list = DB::table('vendor_services')->where('service_id',$order->service_id);
        
        if(!empty($check_vendor_in))
        {
            $vendor_list = $vendor_list->whereIn('vendor_id',$check_vendor_in);
        }
        $vendor_list = $vendor_list->get()->toArray();
        
        foreach ($vendor_list as $key => $value) {
            $checkrejected = ServiceOrderRejected::where(['service_order_id'=>$order->order_id,'vendor_id'=>$value->vendor_id])->get()
            ->count();
            if($checkrejected == 0)
            {
                $vendor_rejected[] = $value->vendor_id;
            }
        } 
        
        //$vendor_list = array_column($vendor_list, 'vendor_id');
        $vendor_list = implode(",",$vendor_rejected);
        if(empty($vendor_list))
        {
        $vendor_list = 0;
        }

        

        if($vendor_list == 0)
        { 
           
        $orderproducts = OrderServiceItemsModel::where('order_id',$request->order_id)->orderBy('order_status','asc')->first();
        OrderServiceModel::where('order_id',$orderproducts->order_id)->update(['status'=>$orderproducts->order_status]);
        OrderServiceItemsModel::where('order_id',$request->order_id)->update(['order_status'=>$orderproducts->order_status]);

        $order = OrderServiceItemsModel::where('id',$request->id)->first();
        $order->order_status = 10;//rejected all vendors
        $order->save();

        $users = User::find($userid);
        
        $amount_to_credit = 0;
        $order_items = OrderServiceItemsModel::where('order_id',$request->order_id)->get();
        foreach($order_items as $odit)
        {
            $amount_to_credit += ($odit->hourly_rate * $odit->qty) - $odit->discount +  $odit->vat;
        }
        
        
        
        $data = [
                            'user_id' => $order_service->user_id,
                            'wallet_amount' => $amount_to_credit,
                            'pay_type' => 'credited',
                            'pay_method' => $order_service->payment_mode,
                            'description' => 'Service Order cancellation amount refunded to wallet.',
        ];
         
        if(wallet_history($data)) {
           $users->wallet_amount = $users->wallet_amount + $amount_to_credit;
           $users->save();
        }


        exec("php ".base_path()."/artisan order:update_service_status --uri=" . $order->order_id . " --uri2=" . $request->order_status . " --uri3=" . $order->id. "> /dev/null 2>&1 & ");
        
    
        }
        
        $check =  ServiceOrderStatusHistory::where('order_id',$order->order_id)->where('status_id',$request->order_status)->where('order_item_id',0)->get()->count();
        if($check == 0)
        {
                $datastatusins = new ServiceOrderStatusHistory;
                $datastatusins->order_id = $order->order_id;
                $datastatusins->order_item_id = 0;
                $datastatusins->status_id = $request->order_status;
                $datastatusins->created_at = gmdate('Y-m-d H:i:s');
                $datastatusins->updated_at = gmdate('Y-m-d H:i:s');
                $datastatusins->save();
        }
       
        $message = "Service Order status is changed successfully";
        if($request->order_status == 1) {
            exec("php ".base_path()."/artisan init_invoice:service_order ".$request->order_id." > /dev/null 2>&1 & ");
        }
        
        if($request->order_status == 1  || $request->order_status == 4 || $request->order_status == 3)
        {
         exec("php ".base_path()."/artisan order:update_service_status --uri=" . $order->order_id . " --uri2=" . $request->order_status . " --uri3=" . $order->id. "> /dev/null 2>&1 & ");
         exec("php ".base_path()."/artisan send:send_service_status_email --uri=" . $order->order_id . " --uri2=" . $request->order_status . " --uri3=" . $order->id. " > /dev/null 2>&1 & ");
        //\Artisan::call("send:send_service_status_email --uri=" . $order->order_id . " --uri2=" . $request->order_status . " --uri3=" . $order->id);
        //exec("php " . base_path() . "/artisan send:send_order_email --uri=" . urlencode($user->email) . " --uri2=" . $order->order_id . " --uri3=" . urlencode($name) . " --uri4=" . $user->id . " > /dev/null 2>&1 & ");
        }
        }
        else
        {
             $status = "1";
             $message = "You are not accepted this order!Please accept first.";
        }
        

        echo json_encode(['status'=>$status,'message'=>$message]);
        
    }
    public function change_status_rejected(Request $request)
    {
      $status = 0;
      $message = "";
      $userid = Auth::user()->id;
      
      if($request->order_status == 10)
      {

          $order = OrderServiceItemsModel::where(['order_id'=>$request->order_id,'id'=>$request->id])->where('accepted_vendor','=',0)->orWhere('accepted_vendor','=',$userid)->first();
          $order = OrderServiceItemsModel::find($request->id);



          if(!$order){
              return response()->json([
                  'status' => "0",
                  'message' => 'Invalid order_id',
                  'oData' => (object)[],
                  'errors' => (object) [],
              ], 401);
          }

          //$order->order_status = $request->order_status;
          $order->save();

          //update order main
          //$orderproducts = OrderServiceItemsModel::where('order_id',$request->order_id)->orderBy('order_status','asc')->first();
          //OrderServiceModel::where('order_id',$orderproducts->order_id)->update(['status'=>$orderproducts->order_status]);

          

          

            $check = ServiceOrderRejected::where(['vendor_id'=>$userid,'service_order_id'=>$request->order_id,'service_order_item_id'=>$request->id])->get()->count();
            if($check <= 0)
            {
                $order_rejected = new ServiceOrderRejected();
                $order_rejected->vendor_id = $userid;
                $order_rejected->service_order_id = $request->order_id;
                $order_rejected->service_order_item_id = $request->id;
                $order_rejected->created_at = gmdate('Y-m-d H:i:s');
                $order_rejected->updated_at = gmdate('Y-m-d H:i:s');
                $order_rejected->save();
            }

            //check all vendors rejected this order if true send rejected push to customer 
            $vendor_rejected = [];
            $vendor_list = DB::table('vendor_services')->where('service_id',$order->service_id)->get()->toArray();
            foreach ($vendor_list as $key => $value) {
                $checkrejected = ServiceOrderRejected::where(['service_order_id'=>$order->order_id,'vendor_id'=>$value->vendor_id])->get()->count();
                if($checkrejected == 0)
                {
                    $vendor_rejected[] = $value->vendor_id;
                }
            } 
            //$vendor_list = array_column($vendor_list, 'vendor_id');
            $vendor_list = implode(",",$vendor_rejected);
            if(empty($vendor_list))
            {
            $vendor_list = 0;
            }

            

            if($vendor_list == 0)
            { 
            $ordermaster = OrderServiceModel::where('order_id',$request->order_id)->first();  
            $ordermaster->status = 11; 
            $ordermaster->save();  

            $order = OrderServiceItemsModel::where('order_id',$request->order_id)->first();
            $order->order_status = 11;//rejected all vendors
            $order->save();

            exec("php ".base_path()."/artisan order:update_service_status --uri=" . $order->order_id . " --uri2=" . $request->order_status . " --uri3=" . $order->id. "> /dev/null 2>&1 & ");
            exec("php ".base_path()."/artisan order:update_service_status_vendor --uri=" . $order->order_id . " --uri2=10 --uri3=" . $order->order_id. "> /dev/null 2>&1 & ");
            }
      }

      $status = 1;
      $message = "Order rejected successfully!";
        

        echo json_encode(['status'=>$status,'message'=>$message]);
        
    }
    public function muteall(Request $request)
    {
      
        $status = "0";
        $message = "";
       
        $userid = Auth::user()->id;

        $allorders = OrderServiceModel::get();

        
        foreach ($allorders as $key => $value) {
            $insmute = ServiceOrderMuted::where(['vendor_id'=>$userid,'service_order_id'=>$value->order_id])->first();
            if(empty($insmute))
            {
                $insmute = new ServiceOrderMuted();
                $insmute->vendor_id = $userid;
                $insmute->service_order_id = $value->order_id;
                $insmute->save();
            }
            
        }

        $status = 1;
        $message = "Muted";
        
        
        echo json_encode(['status'=>$status,'message'=>$message]);
        
    }

}