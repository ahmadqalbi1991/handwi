<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\OrderServiceModel;
use App\Models\OrderProductsModel;
use App\Models\OrderServiceItemsModel;
use App\Models\Service;
use App\Models\ServiceCategories;
use App\Models\ServiceCategorySelected;
use App\Models\ServiceOrderStatusHistory;
use App\Models\User;
use App\Models\Rating;
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
                'status' => (string) 0,
                'message' => login_message(),
                'oData' => [],
                'errors' => (object) [],
            ]);
            exit;
        } else {
            $user = $user->first();
            if ($user->active == 1) {
                return $user->id;
            } else {
                http_response_code(401);
                echo json_encode([
                    'status' => (string) 0,
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ]);
                exit;
                return response()->json([
                    'status' => (string) 0,
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ], 401);
                exit;
            }
        }
    }
    public function my_service_orders(REQUEST $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $order_list = OrderServiceModel::orderBy('order_id', 'desc');
            if($request->activity_id)
            {
                $order_list = $order_list->where('activity_id',$request->activity_id);
            }
            else
            {
                $order_list = $order_list->whereIn('activity_id',[0,6]);   
            }

            $order_list = $order_list->with(['services' => function ($qr) {
                $qr->select('orders_services_items.id', 'order_id', 'orders_services_items.service_id', 'image', 'service.name', 'service_price', 'orders_services_items.order_status','doc','booking_date','task_description')
                ->join('service', 'service.id', 'orders_services_items.service_id');
            }])->where('user_id', $user_id)->get();
            foreach ($order_list as $key => $val) {
                $order_list[$key]->status_text = service_order_status($val->status);
                $order_list[$key]->order_no    = config('global.sale_order_prefix') . "-SER" . date(date('Ymd', strtotime($val->created_at))) . $val->order_id;
                $order_list[$key]->payment_mode_id = $val->payment_mode;
                $order_list[$key]->payment_mode = payment_mode($val->payment_mode);
                $order_list[$key]->order_date = get_date_in_timezone($val->created_at, config('global.datetime_format'));
                $order_list[$key]->invoice_url = get_service_pdf_url($order_list[$key]->order_no);

                $service = $val->services;
                foreach ($service as $key1 => $value1) {
                    $selected_category_id = ServiceCategorySelected::where('service_id', $value1->service_id)->first();
                    $service[$key1]->category_name  = ServiceCategories::find($selected_category_id->category_id)->name;
                    $service[$key1]->parent_category_name  = ServiceCategories::find(ServiceCategories::find($selected_category_id->category_id)->parent_id)->name??'';
                    $service[$key1]->image = get_uploaded_image_url($value1->image, 'service_image_upload_dir');
                    $where2['service_id'] = $value1->service_id;
                    $service[$key1]->rating = number_format(Rating::avg_rating($where2), 1, '.', '');
                    $service[$key1]->is_rated = (string) Rating::where(['user_id'=>$user_id,'service_id'=>$value1->service_id])->count();
                    $service[$key1]->status_text = service_order_status($value1->order_status);
                }
                $order_list[$key]->services = $service;
                $order_list[$key]->booking_date = $value1->booking_date;
                $order_list[$key]->grand_total = number_format($val->grand_total, 2, '.', '');
                $order_list[$key]->vat = number_format($val->vat, 2, '.', '');
                $order_list[$key]->doc = '';
                if(!empty($value1->doc))
                {
                  $order_list[$key]->doc = asset($value1->doc);  
                }
                 
                $order_list[$key]->task_description = $value1->task_description; 
                $o_data = $order_list;
               
            }
            $o_data = convert_all_elements_to_string($o_data);
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    public function my_service_order_details(REQUEST $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'service_order_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $order = OrderServiceModel::orderBy('order_id', 'desc')->with(['services' => function ($qr) {
                $qr->select('orders_services_items.id', 'order_id', 'service_id', 'image', 'service.name', 'service.description','service_price', 'orders_services_items.order_status','qty','text','hourly_rate','doc','booking_date','task_description')
                ->join('service', 'service.id', 'orders_services_items.service_id');
            }])->where(['user_id' => $user_id, 'orders_services.order_id' => $request->service_order_id])->first();

            if ($order) {

                $order->status_text = service_order_status($order->status);
                $order->order_no    = config('global.sale_order_prefix') . "-SER" . date(date('Ymd', strtotime($order->created_at))) . $order->order_id;
                $order->payment_mode_id = (string) $order->payment_mode;
                $order->payment_mode = payment_mode($order->payment_mode);
                $order->order_date = get_date_in_timezone($order->created_at, config('global.datetime_format'));
                $order->invoice_url = get_service_pdf_url($order->order_no);
                $order->address = \App\Models\UserAdress::get_address_details($order->address_id);
                $order->ref_user_name = User::where('ref_code',$order->ref_code)->first()->name??"";
                $service = $order->services;
                foreach ($service as $key => $value) {
                    $selected_category_id = ServiceCategorySelected::where('service_id', $value->service_id)->first();
                    $service[$key]->category_name  = ServiceCategories::find($selected_category_id->category_id)->name;
                    $service[$key]->parent_category_name  = ServiceCategories::find(ServiceCategories::find($selected_category_id->category_id)->parent_id)->name??'';
                    $service[$key]->image = get_uploaded_image_url($value->image, 'service_image_upload_dir');
                    if($value->doc) 
                    {
                        $service[$key]->doc = asset($value->doc);
                    }
                    
                    $where2['service_id'] = $value->service_id;
                    $where2['type']   = 3;
                    $service[$key]->rating = number_format(Rating::avg_rating($where2), 2, '.', '');
                    $service[$key]->is_rated = (string) Rating::where(['user_id'=>$user_id,'service_id'=>$value->service_id,'type'=>3])->count();
                    $service[$key]->rating_count = (string) count(Rating::rating_list($where2));
                    $service[$key]->status_text = service_order_status($value->order_status);
                    $service[$key]->booking_date = $value->booking_date;
                    $status_history = ServiceOrderStatusHistory::where(['order_id'=>$order->order_id])->orderBy('status_id','desc')->get();
                    
                    foreach ($status_history as $key_his => $value_his) {
                        $status_history[$key_his]->status_text = service_order_status($value_his->status_id);
                        $status_history[$key_his]->date_time = get_date_in_timezone($value_his->created_at, 'Y-m-d H:i:s');
                    }
                    $service[$key]->status_history = $status_history->toarray();
                }
                $order->services = convert_all_elements_to_string($service);
                $order->booking_date = $value->booking_date;
                $order->doc = '';
                if(!empty($value->doc))
                {
                  $order->doc = asset($value->doc);  
                }
                 
                $order->task_description = $value->task_description; 
                $order->grand_total = number_format($order->grand_total, 2, '.', '');
                $order->vat = number_format($order->vat, 2, '.', '');
                $o_data = $order;
            }
            $o_data = convert_all_elements_to_string($o_data->toArray());
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
    }
    public function re_order_service(REQUEST $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'service_order_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $order = OrderServiceModel::orderBy('order_id', 'desc')->with(['services' => function ($qr) {
                $qr->select('orders_services_items.id', 'order_id', 'service_id', 'image', 'service.name', 'service.description','service_price', 'orders_services_items.order_status','qty','text','hourly_rate','doc','orders_services_items.booking_date','task_description')
                ->join('service', 'service.id', 'orders_services_items.service_id');
            }])->where(['user_id' => $user_id, 'orders_services.order_id' => $request->service_order_id])->first();

            if ($order) {
                $service = $order->services;
                $service_Cart = new \App\Http\Controllers\Api\v1\ServiceCartController(app('firebase.database'));

                foreach ($service as $key => $value) {
                    $request->merge(['service_id' => $value->service_id]);
                    $request->merge(['service_date' => date('Y-m-d H',strtotime($value->booking_date))]);
                    $request->merge(['text' => $value->text]);
                    $request->merge(['hourly_rate' => $value->hourly_rate]);
                    $request->merge(['task_description' => $value->task_description]);
                    $request->merge(['qty' => $value->qty]);

                    $request->merge(['remove_cart' => '']);
                    if($key == 0){
                        $request->merge(['remove_cart' => 1]);
                    }
                    $add_to_cart = $service_Cart->add_service_to_cart($request);
                }
                $get_cart = $service_Cart->get_service_cart($request)->original;
                $status = 1;
                $message = "Items has been added to the cart for re-order.";
                $o_data = $get_cart['oData'] ?? [];
            }
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
    }

      public function booked_slots(REQUEST $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            // 'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $sorted_order_list= [];
            $order_list = OrderServiceModel::select( 'booking_date')
            ->orderBy('booking_date', 'asc')
            ->get();
            foreach($order_list as $val){
                $booked_date  = \Carbon\Carbon::parse($val->booking_date)->format('d-m-Y');
                $booked_time = \Carbon\Carbon::parse($val->booking_date)->format("h:i");
                $request_date = $request->booking_date;
                if($request_date == $booked_date){
                    array_push($sorted_order_list, $booked_time);
                }
                
            }
            
            $o_data = $sorted_order_list;
            
            $o_data = convert_all_elements_to_string($o_data);
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }


    public function service_cancel_order(REQUEST $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $order = OrderServiceItemsModel::where(['order_id' => $request->order_id])->get();
            $mainorderdata = OrderServiceModel::where('order_id', $request->order_id)->first();

            if ($order) {
                $highest_order_prd_status = OrderServiceItemsModel::where(['order_id' => $request->order_id])->orderBy('order_status', 'desc')->first();

                if (isset($highest_order_prd_status->order_status) && $highest_order_prd_status->order_status == 0) {

                    $amount_to_credit = $mainorderdata->grand_total;
                    $users = User::find($user_id);
                    if($mainorderdata->payment_mode  != 5){
                        $data = [
                            'user_id' => $user_id,
                            'wallet_amount' => $mainorderdata->grand_total,
                            'pay_type' => 'credited',
                            'pay_method' => $mainorderdata->payment_mode,
                            'description' => 'Service Order cancellation amount refunded to wallet.',
                        ];

                        if (wallet_history($data)) {
                            $users->wallet_amount = $users->wallet_amount + $mainorderdata->grand_total;
                            $users->save();
                        }
                    }
                    


                    

                    // $users->wallet_amount = $users->wallet_amount + $amount_to_credit;
                    // $users->save();
                    $c_st = config('global.order_status_cancelled');
                    OrderServiceModel::where('order_id', $request->order_id)->update(['status' => $c_st]);
                    OrderServiceItemsModel::where(['order_id' => $request->order_id])->update(['order_status' => $c_st]);

                    foreach ($order as $key => $value_o) {
                        $check =  ServiceOrderStatusHistory::where('order_id',$value_o->order_id)->where('status_id',$c_st)->where('order_item_id',$value_o->id)->get()->count();
                        if($check == 0)
                     {
                        $datastatusins = new ServiceOrderStatusHistory;
                        $datastatusins->order_id = $value_o->order_id;
                        $datastatusins->order_item_id = $value_o->id;
                        $datastatusins->status_id = $c_st;
                        $datastatusins->created_at = gmdate('Y-m-d H:i:s');
                        $datastatusins->updated_at = gmdate('Y-m-d H:i:s');
                        $datastatusins->save();
                     }
                    }

                   
                    $status = (string) 1;
                    $message = "Your service order has been cancelled successfully.";

                    $title = $mainorderdata->order_no;
                    $description = $message;
                    $notification_id = time();
                    $ntype = 'service_order_cancelled';
                    if (!empty($users->firebase_user_key)) {
                        $notification_data["Notifications/" . $users->firebase_user_key . "/" . $notification_id] = [
                            "title" => $title,
                            "description" => $description,
                            "notificationType" => $ntype,
                            "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                            "orderId" => (string) $request->order_id,
                            "url" => (string) $request->id,
                            "imageURL" => '',
                            "read" => "0",
                            "seen" => "0",
                        ];
                        $this->database->getReference()->update($notification_data);
                    }

                    if (!empty($users->user_device_token)) {
                        send_single_notification(
                            $users->user_device_token,
                            [
                                "title" => $title,
                                "body" => $description,
                                "icon" => 'myicon',
                                "sound" => 'default',
                                "click_action" => "EcomNotification"
                            ],
                            [
                                "type" => $ntype,
                                "notificationID" => $notification_id,
                                "orderId" => (string) $request->order_id,
                                "imageURL" => "",
                            ]
                        );
                    }
                    exec("php ".base_path()."/artisan order:update_service_status_vendor_from_user --uri=" . $request->order_id. "> /dev/null 2>&1 & ");
                } else {
                    $status = (string) 0;
                    $message = "You can't cancel this service order";
                }
            } else {
                $status = (string) 0;
                $message = "Error";
            }
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
}