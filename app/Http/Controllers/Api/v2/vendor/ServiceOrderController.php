<?php

namespace App\Http\Controllers\Api\v2\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OrderServiceModel;
use App\Models\OrderProductsModel;
use App\Models\OrderServiceItemsModel;
use App\Models\User;
use App\Models\Rating;
use App\Models\ServiceOrderRejected;
use App\Models\VendorDetailsModel;
use App\Models\ServiceCategorySelected;
use App\Models\ServiceCategories;
use App\Models\ServiceOrderStatusHistory;
use App\Models\CouponVendorServiceOrders;
use App\Models\ServiceAssignedVendors;
use DB;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Validator;

class ServiceOrderController extends Controller
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    private function validateAccesToken($access_token)
    {

        $user = User::where(['user_access_token' => $access_token])->get();

        if ($user->count() == 0) {
            http_response_code(401);
            echo json_encode([
                'status' => "0",
                'message' => login_message(),
                'oData' => [],
                'errors' => (object) [],
            ]);
            exit;

        } else {
            $user = $user->first();
            if ($user->verified == 1) {
                return $user->id;
            } else {
                http_response_code(401);
                echo json_encode([
                    'status' => "0",
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ]);
                exit;
                return response()->json([
                    'status' => "0",
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ], 401);
                exit;
            }
        }
    }
    public function service_orders(REQUEST $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $order_list1 = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            
            $page_no= $request->page_no??1;
            $limit = $request->limit;
            $offset = ($page_no - 1) * $limit;
            $order_list = [];
            $notrelated_orders = [];

            $user_id = $this->validateAccesToken($request->access_token);
            $vendor_coupon_orders  = CouponVendorServiceOrders::pluck('order_id')->toArray();
            
            if(!empty($vendor_coupon_orders))
            {
            $vendor_coupon_orders  = array_unique($vendor_coupon_orders);   
            
            foreach ($vendor_coupon_orders as $key => $value) {
           $check_vendor = CouponVendorServiceOrders::where(['vendor_id'=>$user_id,'order_id'=>$value])->get()->count();
           if($check_vendor == 0)
            {
             $notrelated_orders[] = $value;
            }

            }
        
            }
            //$check = CouponVendorServiceOrders::where('order_id',$value)->get()->
            
            $user_d = User::where(['user_access_token' => $request->access_token])->first();
            $vendor_services = DB::table('vendor_services')->where('vendor_id',$user_id)->get()->toArray();
            $vendor_services = $serar = array_column($vendor_services, 'service_id');
            $vendor_services = implode(",",$vendor_services);

           if(!empty($serar))
            {
            $order_list = OrderServiceModel::select('orders_services.order_id','orders_services.order_no','orders_services_items.id as id','orders_services.status as order_status','orders_services.grand_total as price',
            'users.name as customer_name',
            'orders_services_items.booking_date',
            'orders_services_items.qty',
            'orders_services_items.hourly_rate',
            'orders_services_items.text',
            'orders_services_items.id as subid',
            'service.name as service_name',
            'orders_services.is_mute','service.image',
            'orders_services.created_at')->orderBy('orders_services.order_id','desc')
            ->join('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')
            ->join('service', 'service.id', 'orders_services_items.service_id');
            
           
           if(is_numeric($request->status) && $request->status >= 0 && $request->status != 11)
            {
              $order_list = $order_list->where('orders_services.status',$request->status);  
            }
           $order_list = $order_list->join('users', 'users.id', 'orders_services.user_id')
           ->whereIn('orders_services_items.service_id',explode(",", $vendor_services))
           ->whereNotIn('orders_services.order_id',$notrelated_orders)
           ->where(function($query) use($user_id)
            {
            $query->where('orders_services_items.accepted_vendor','=','0')
           ->orWhere('orders_services_items.accepted_vendor',$user_id);
            });
           //$order_list = $order_list->whereIn('orders_services.order_id',ServiceAssignedVendors::where(['vendor_user_id'=>$user_id])->whereIn('service_status',[0,1])->select('order_id'));
           if($request->limit && $request->page_no)
           {
           $order_list = $order_list->limit($limit)->skip($offset); 
           }
           $created = $user_d->created_at;
           $order_list = $order_list->where('orders_services.created_at','>=',$created)->distinct('order_id')->get();
           $checkwith = 0;
           if($request->status == 11)
           {
             $checkwith = 1;
           }
             foreach ($order_list as $key => $value) {
                $check = ServiceOrderRejected::where(['vendor_id'=>$user_id,'service_order_id'=>$value->order_id,'service_order_item_id'=>0])->get()->count();
                if($check == $checkwith)
                {
                    
                      $order_list1[$key] = $value;
                      $order_list1[$key]->status_text  = service_order_status($value->order_status);
                      if($request->status == 11)
                      {
                      $order_list1[$key]->status_text  = "Rejected";
                      $order_list1[$key]->order_status  = 11;
                      }
                      
                      $order_list1[$key]->order_number = $value->order_no."-".$value->subid;
                      $order_list1[$key]->booking_date = date('d-M-y h:i A', strtotime($value->booking_date));
                      $order_list1[$key]->image = get_uploaded_image_url($value->image,'service_image_upload_dir');
                }
                
            }
             $o_data = array_values($order_list1);
            
            }
            else
            {
                $o_data = [];
            }


                // $order_list[$key]->status_text = order_status($val->status);
                // $order_list[$key]->order_no    = config('global.sale_order_prefix')."-SER".date(date('Ymd', strtotime($val->created_at))).$val->order_id;
                // $order_list[$key]->payment_mode_id = $val->payment_mode;
                // $order_list[$key]->payment_mode = payment_mode($val->payment_mode);
                // $service = $val->services;
                // foreach ($service as $key => $value) {
                //  $service[$key]->image = get_uploaded_image_url($value->image,'service_image_upload_dir');  
                //  $where2['service_id'] = $value->id;
                //  $service[$key]->rating = Rating::avg_rating($where2);

           

           
           
         
            $o_data = convert_all_elements_to_string($o_data);
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    public function service_order_details(REQUEST $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'service_order_id' => 'required',
           // 'id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $acceptedvendor = "0";
            $item_id = $request->id;
            $user_id = $this->validateAccesToken($request->access_token);
            $order = OrderServiceModel::orderBy('order_id','desc')->with(['services' => function ($qr) use ($item_id) {
                $qr->select('orders_services_items.id', 'order_id', 'service_id','image','service.name','service_price','hourly_rate','service.description as service_description','orders_services_items.text','orders_services_items.qty','order_status','doc','vat','discount','task_description as description','vendor_comment','vendor_doc','booking_date','accepted_vendor')
                ->join('service', 'service.id', 'orders_services_items.service_id');
            }])->where(['orders_services.order_id'=>$request->service_order_id])->first();
            
           // ->where('orders_services_items.id',$item_id)
            if ($order) {
               
                $order->status_text = service_order_status($order->status);
                $order->order_no    = config('global.sale_order_prefix')."-SER".date(date('Ymd', strtotime($order->created_at))).$order->order_id;
                $order->payment_mode_id = $order->payment_mode;
                $order->ref_user_name = User::where('ref_code',$order->ref_code)->first()->name??"";
                $order->payment_mode = payment_mode($order->payment_mode);
                $order->address = \App\Models\UserAdress::get_address_details($order->address_id);
                $service = $order->services;
                $category_name= "";
                $total = 0;
                foreach ($service as $key => $value) {
                 $selected_category_id = ServiceCategorySelected::where('service_id', $value->service_id)->first();
                 $category_name = ServiceCategories::find($selected_category_id->category_id)->name??'';
                 $service[$key]->category_name  = $category_name;
                 $service[$key]->image = get_uploaded_image_url($value->image,'service_image_upload_dir');  
                 $where2['service_id'] = $value->id;
                 $service[$key]->rating = Rating::avg_rating($where2);
                 $service[$key]->booking_date = date('d-M-y h:i A', strtotime($value->booking_date));
                 if($value->doc)
                 {
                        $service[$key]->doc = asset($value->doc);
                 }
                 $acceptedvendor = $value->accepted_vendor;
                    
                 $total += $value->qty * $value->hourly_rate;
                }
                
                $order->services = $service;
                $order->total = $total;
                //$order->grand_total = ($total - $value->discount) + ($value->vat) + $order->service_charge;
                $order->grand_total = $order->grand_total;
                $order->status_text = service_order_status($value->order_status);
                $order->status = $value->order_status;
                $order->discount = $value->discount;
                $order->vat = $order->vat;
                $order->category_name = $category_name;
                $order->booking_date = $value->booking_date;
                $order->order_date = get_date_in_timezone($order->created_at, 'd-M-y h:i A');
                $order->category_id = $selected_category_id->category_id??0;
                $order->name = $value->name;
                $o_data = $order;
                
                if($acceptedvendor != 0 && $acceptedvendor != $user_id)
                {
                   
                    $status = "2";
                    $message = "This service not available to view, already accepted by another vendor";
                    $errors = "This service not available to view, already accepted by another vendor";
                }
                
            }
            $o_data = convert_all_elements_to_string($o_data);
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
    }
    public function cancel_order(REQUEST $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $order = OrderModel::with(['products'])->where('user_id', $user_id)->where('order_id', $request->order_id)->first();

            if ($order) {
                $highest_order_prd_status = OrderProductsModel::where('order_id',$request->order_id)->orderby('order_status','desc')->first();

                if(isset($highest_order_prd_status->order_status) && $highest_order_prd_status->order_status == 1){
                    $amount_to_credit = $order->grand_total;
                    $w_data = [
                        'user_id' => $user_id,
                        'wallet_amount' => $amount_to_credit,
                        'pay_type' => 'credited',
                        'description' => 'Order Cancelled',
                    ];
                    if (wallet_history($w_data)) {
                        $users = User::find($user_id);
                        $users->wallet_amount = $users->wallet_amount + $amount_to_credit;
                        $users->save();
                        $c_st = config('global.order_status_cancelled');
                        OrderModel::where('order_id', $request->order_id)->update(['status'=>$c_st]);
                        OrderProductsModel::where('order_id', $request->order_id)->update(['order_status'=>$c_st]);
                        $status = "1";
                        $message = "Your order has been cancelled successfully. Amount has refunded to your wallet.";


                        $title = 'Order Cancelled';
                        $description = $message;
                        $notification_id = time();
                        $ntype = 'order_cancelled';
                        if (!empty($users->firebase_user_key)) {
                            $notification_data["Notifications/" . $users->firebase_user_key . "/" . $notification_id] = [
                                "title" => $title,
                                "description" => $description,
                                "notificationType" => $ntype,
                                "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                                "orderId" => (string) $request->order_id,
                                "url" => "",
                                "imageURL" => '',
                                "read" => "0",
                                "seen" => "0",
                            ];
                            $this->database->getReference()->update($notification_data);
                        }
                
                        if (!empty($users->user_device_token)) {
                            send_single_notification($users->user_device_token, [
                                "title" => $title,
                                "body" => $description,
                                "icon" => 'myicon',
                                "sound" => 'default',
                                "click_action" => "EcomNotification"],
                                ["type" => $ntype,
                                    "notificationID" => $notification_id,
                                    "orderId" => (string) $request->order_id,
                                    "imageURL" => "",
                                ]);
                        }


                    }else{
                        $status = "0";
                        $message = "Something went wrong!! Try again";
                    }
                    

                }else{
                     $status = "0";
                    $message = "You can't cancel this order";
                }
                
            } 
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    public function service_change_status_old(Request $request)
    {
        
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];

        $userid = $this->validateAccesToken($request->access_token);
        $user = User::find($userid);
        $name = $users->name ?? $user->first_name . ' ' . $user->last_name;
        
        $order_service = OrderServiceModel::where('order_id',$request->order_id)->where('accepted_vendor','=',0)->orWhere('accepted_vendor','=',$userid)->first();
        
        if(empty($order_service)){
            return response()->json([
                'status' => "0",
                'message' => 'Invalid order_id',
                'oData' => (object)[],
                'errors' => (object) [],
            ], 401);
        }

        if($order_service->status == 10){
            return response()->json([
                'status' => "0",
                'message' => 'Permission denied to change the status of this order',
                'oData' => (object)[],
                'errors' => (object) [],
            ], 200);
        }

        
       
             if($order_service->status == 0)
             {
                
                

                if($request->order_status == 1)
                {
                $order =  OrderServiceItemsModel::where('order_id',$request->order_id)->get();


                

                //vendor commisson
                $vendor_data = VendorDetailsModel::where('user_id',$userid)->first();
                $vendor_commission = 0;

                foreach($order as $orders_value)
                {
                    $t_amount += ($orders_value->hourly_rate * $orders_value->qty) - $orders_value->discount;
                    

                }

                if(!empty($vendor_data->servicecommission && !empty($t_amount)))
                {
                    //   $vendor_commission = $t_amount * $vendor_data->servicecommission/100;
                    //   $admin_commission = $vendor_commission + $order_service->service_charge??0;
                    //   $vendor_commission = $t_amount - $vendor_commission + $order->vat;
                    
                      $vendor_commission = $order_service->grand_total * $vendor_data->servicecommission/100;
                      $admin_commission = $order_service->grand_total - $vendor_commission;
                      
                    
                }

                OrderServiceItemsModel::where('order_id',$request->order_id)
                ->update([
                'order_status'=>$request->order_status,
                'accepted_vendor'=>$userid,
                'accepted_date'=>gmdate('Y-m-d H:i:s'),
                'admin_commission'=>$admin_commission,
                'vendor_commission'=>$vendor_commission]);
                
                
                $order_service->status          =  $request->order_status;
                $order_service->accepted_vendor = $userid;
                $order_service->admin_commission = $admin_commission;
                $order_service->vendor_commission = $vendor_commission;
                

              
                }
                if($request->order_status == 4)
                {
                    $doc = "";
                    if ($file = $request->file("doc")) {
                    $response = image_upload($request, 'document', 'doc');
                    if ($response['status']) {
                        $doc = $response['link'];
                    }
                    }

                    OrderServiceItemsModel::where('order_id',$request->order_id)
                    ->update([
                    'vendor_comment'=>$request->comment,'doc'=>$doc]);
                    
                    
                }

                if($request->order_status == 10)
                {
                $check = ServiceOrderRejected::where(['vendor_id'=>$userid,'service_order_id'=>$request->order_id,'service_order_item_id'=>0])->get()->count();
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

                if($request->order_status != 10)
                {
                OrderServiceItemsModel::where('order_id',$request->order_id)
                ->update([
                    'order_status'=>$request->order_status]);
                $order_service->status = $request->order_status;
                }
                $order_service->save();



                
                

             }
             else
             {
             $status = "2";
             $message = "This order already accepetd by another vendor!";
             return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
            }





            
            exit;

         if($request->order_status == 4) 
         {
         $order_accepted = OrderServiceItemsModel::where(['order_id'=>$request->order_id,'id'=>$request->id,'accepted_vendor'=>$userid])->get()->count();
         $order->vendor_comment = $request->comment;
          if ($file = $request->file("doc")) {
            $response = image_upload($request, 'document', 'doc');
            if ($response['status']) {
                $doc = $response['link'];
            }
        }
        if(!empty($doc))
        {
            $order->vendor_doc = $doc;
        }
        
        }



          exit;

         $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        // update on order table
        $userid = $this->validateAccesToken($request->access_token);
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
        if($order->order_status == 10){
            return response()->json([
                'status' => "0",
                'message' => 'Permission denied to change the status of this order',
                'oData' => (object)[],
                'errors' => (object) [],
            ], 200);
        }
        if($request->order_status == 4) 
        {
        $order_accepted = OrderServiceItemsModel::where(['order_id'=>$request->order_id,'id'=>$request->id,'accepted_vendor'=>$userid])->get()->count();
        $order->vendor_comment = $request->comment;
        if ($file = $request->file("doc")) {
            $response = image_upload($request, 'document', 'doc');
            if ($response['status']) {
                $doc = $response['link'];
            }
        }
        if(!empty($doc))
        {
            $order->vendor_doc = $doc;
        }
        
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
             $status = "2";
             $message = "This order already accepetd by another vendor!";
             return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
            }
            }
            
            $order_accepted = 1;
        }
        if($order_accepted == 1) 
        {
        if($request->order_status != 10)
        {
            $order->order_status = $request->order_status;
           $order_service->status = $request->order_status;
        }
        if($request->order_status == 1) 
        {
        $order->accepted_vendor = $userid;
        $order->accepted_date   = gmdate('Y-m-d H:i:s');

         
        //save vendor commission
        $vendor_data = VendorDetailsModel::where('user_id',$userid)->first();

        
        $vendor_commission = 0;

        //$t_amount = (($order->hourely_rate * $order->qty) + $order->vat) - $order->discount;
        $t_amount = ($order->hourly_rate * $order->qty) - $order->discount;
        
        if(!empty($vendor_data->servicecommission && !empty($t_amount)))
                    {

                      $vendor_commission = $t_amount * $vendor_data->servicecommission/100;
                    }
                    else
                    { 
                       $vendor_commission  = 0;
                    }
        //save vendor commission END
        $order->admin_commission = $vendor_commission + $order_service->service_charge??0;
        $order->vendor_commission = $t_amount - $vendor_commission + $order->vat;
        }
        $order->save();
        
        //update order main
        $orderproducts = OrderServiceItemsModel::where('order_id',$request->order_id)->orderBy('order_status','asc')->first();
        OrderServiceModel::where('order_id',$orderproducts->order_id)->update(['status'=>$orderproducts->order_status]);


        if($request->order_status == 10)
        {
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


        $order = OrderServiceItemsModel::where('id',$request->id)->first();
        $order->order_status = 10;//rejected all vendors
        $order->save();

        $users = User::find($userid);
        
        

        $amount_to_credit = ($order->hourly_rate * $order->qty) - $order->discount +  $order->vat;
        
        $main_order_data = OrderServiceModel::where(['order_id'=>$request->order_id])->get()->first();
        if($main_order_data->payment_mode != 5){
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
       
        if($request->order_status == 1  || $request->order_status == 4 || $request->order_status == 3)
        {
        exec("php ".base_path()."/artisan order:update_service_status --uri=" . $order->order_id . " --uri2=" . $request->order_status . " --uri3=" . $order->id. "> /dev/null 2>&1 & ");
        //\Artisan::call("order:update_service_status --uri=" . $order->order_id . " --uri2=" . $order->status . " --uri3=" . $order->id);
        //exec("php " . base_path() . "/artisan send:send_order_email --uri=" . urlencode($user->email) . " --uri2=" . $order->order_id . " --uri3=" . urlencode($name) . " --uri4=" . $user->id . " > /dev/null 2>&1 & ");
        }
       
        }
        else
        {
             $status = "1";
             $message = "You are not accepted this order!Please accept first.";
        }
        

        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
        
    }
    public function service_change_status(Request $request)
    {
         $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        // update on order table
        $userid = $this->validateAccesToken($request->access_token);
        $user = User::find($userid);
        $name = $users->name ?? $user->first_name . ' ' . $user->last_name;

        $store_id = $user->id;
         
        $order_service = OrderServiceModel::where('order_id',$request->order_id)->where('accepted_vendor','=',0)->orWhere('accepted_vendor','=',$userid)->first();
        $order = OrderServiceItemsModel::where(['order_id'=>$request->order_id])->where('accepted_vendor','=',0)->orWhere('accepted_vendor','=',$userid)->first();
        $order = OrderServiceItemsModel::where('order_id',$request->order_id)->first();
       
        if(!$order){
            return response()->json([
                'status' => "0",
                'message' => 'Invalid order_id',
                'oData' => (object)[],
                'errors' => (object) [],
            ], 401);
        }
        if($order->order_status == 10){
            return response()->json([
                'status' => "0",
                'message' => 'Permission denied to change the status of this order',
                'oData' => (object)[],
                'errors' => (object) [],
            ], 200);
        }
        if($request->order_status == 4) 
        {
        $order_accepted = OrderServiceItemsModel::where(['order_id'=>$request->order_id,'accepted_vendor'=>$userid])->get()->count();
        $order->vendor_comment = $request->comment;
        if ($file = $request->file("doc")) {
            $response = image_upload($request, 'document', 'doc');
            if ($response['status']) {
                $doc = $response['link'];
            }
        }
        if(!empty($doc))
        {
            $order->vendor_doc = $doc;
        }
        
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
             $status = "2";
             $message = "This order already accepetd by another vendor!";
             return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
            }
            }
            
            $order_accepted = 1;
        }
        if($order_accepted >= 1) 
        {
        if($request->order_status != 10)
        {
            $order->order_status = $request->order_status;
           $order_service->status = $request->order_status;
        }
        if($request->order_status == 1) 
        {
        $order->accepted_vendor = $userid;
        $order->accepted_date   = gmdate('Y-m-d H:i:s');

         
        //save vendor commission
        $vendor_data = VendorDetailsModel::where('user_id',$userid)->first();
        
        
        $vendor_commission = 0;
        $ad_admin_commission = 0;
        $ad_vendor_commission = 0;
        $serviceitems = OrderServiceItemsModel::where('order_id',$request->order_id)->get();
        $service_charge_com = number_format($order_service->service_charge/count($serviceitems), 2, '.', '');
        foreach($serviceitems as $key => $value_int_con)
        {
               //$t_amount = (($order->hourely_rate * $order->qty) + $order->vat) - $order->discount;
                $t_amount = ($value_int_con->hourly_rate * $value_int_con->qty) - $value_int_con->discount;
        
                if(!empty($vendor_data->servicecommission && !empty($t_amount)))
               {

                $vendor_commission = $t_amount * $vendor_data->servicecommission/100;
               } 
               else
               { 
               $vendor_commission  = 0;
               }
          //save vendor commission END
         $dataitems = OrderServiceItemsModel::find($value_int_con->id);
         $dataitems->admin_commission = $vendor_commission + $service_charge_com??0;
         $dataitems->vendor_commission = $t_amount - $vendor_commission + $order->vat;
         $dataitems->save();

         $ad_admin_commission += $vendor_commission;
        $ad_vendor_commission += $t_amount - $vendor_commission + $value_int_con->vat;

        }

        $ad_admin_commission = $ad_admin_commission + $order_service->service_charge;
        $order_service->admin_commission = $ad_admin_commission;
        $order_service->vendor_commission = $ad_vendor_commission;
        $order_service->save();

      
        }
        $order->save();
        
        
        //update order main
        $orderproducts = OrderServiceItemsModel::where('order_id',$request->order_id)->orderBy('order_status','desc')->first();
        OrderServiceModel::where('order_id',$orderproducts->order_id)->update(['status'=>$orderproducts->order_status]);
        OrderServiceItemsModel::where('order_id',$request->order_id)->update(['order_status'=>$orderproducts->order_status,'accepted_vendor'=>$orderproducts->accepted_vendor]);


        if($request->order_status == 10)
        {
            $check = ServiceOrderRejected::where(['vendor_id'=>$userid,'service_order_id'=>$request->order_id,'service_order_item_id'=>0])->get()->count();
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
        
        $orderproducts = OrderServiceItemsModel::where('order_id',$request->order_id)->orderBy('order_status','desc')->first();
        OrderServiceModel::where('order_id',$orderproducts->order_id)->update(['status'=>$orderproducts->order_status]);

      
        $order = OrderServiceItemsModel::where('order_id',$request->order_id)->update(['order_status'=>10]);
        $order = OrderServiceItemsModel::where('order_id',$request->order_id)->first();
        // $order->order_status = 10;//rejected all vendors
        // $order->save();

        $users = User::find($userid);
        
        $amount_to_credit = 0;
        $order_details = OrderServiceItemsModel::where('order_id',$request->order_id)->get();
        foreach ($order_details as $key => $value_it) {
            $amount_to_credit += ($value_it->hourly_rate * $value_it->qty) - $value_it->discount +  $value_it->vat;
        }
        
        
        $main_order_data = OrderServiceModel::where(['order_id'=>$request->order_id])->get()->first();
        if($main_order_data->payment_mode != 5){
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
        }


        exec("php ".base_path()."/artisan order:update_service_status --uri=" . $order->order_id . " --uri2=" . $request->order_status . " --uri3=" . $order->id. "> /dev/null 2>&1 & ");
        
    
        }
        
        $check =  ServiceOrderStatusHistory::where('order_id',$order->order_id)->where('status_id',$request->order_status)->get()->count();
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
       
        if($request->order_status == 1  || $request->order_status == 4 || $request->order_status == 3)
        {
        exec("php ".base_path()."/artisan order:update_service_status --uri=" . $order->order_id . " --uri2=" . $request->order_status . " --uri3=" . $order->id. "> /dev/null 2>&1 & ");
        //\Artisan::call("order:update_service_status --uri=" . $order->order_id . " --uri2=" . $order->status . " --uri3=" . $order->id);
        //exec("php " . base_path() . "/artisan send:send_order_email --uri=" . urlencode($user->email) . " --uri2=" . $order->order_id . " --uri3=" . urlencode($name) . " --uri4=" . $user->id . " > /dev/null 2>&1 & ");
        }
       
        }
        else
        {
             $status = "1";
             $message = "You are not accepted this order!Please accept first.";
        }
        
        if($request->order_status == 4) {
            exec("php ".base_path()."/artisan init_invoice:service_order ".$request->order_id." > /dev/null 2>&1 & ");
        }
        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
        
    }
    public function mute_service_order(REQUEST $request){
    $status   = "0";
     $message  = "";
     $o_data   = [];
     $errors   = [];
         
     $validator = Validator::make($request->all(), [
         'type' => 'required',
         'access_token' => 'required',
     ]);

     if ($validator->fails()) {
         $status = "0";
         $message = "Validation error occured"; 
         $errors = $validator->messages();
     }else{
          $userid = $this->validateAccesToken($request->access_token);
          $user = User::find($userid);
         $type = $request->type;
         if($type == 'all'){
             $data1 = OrderServiceModel::where('is_mute','!=', 1)->update(['is_mute'=>1]);
             $status = "1";
             $message = "all service orders muted";
         }else{
             $data = OrderServiceModel::where(['order_id'=>$request->order_id])
             ->update(['is_mute'=>1]);
             $status = "1";
             $message = "service order muted";
         }
     }
      return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
    }

}
