<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\OrderModel;
use App\Models\OrderProductsModel;
use App\Models\User;
use App\Models\OrderServiceModel;
use App\Models\Contracting;
use App\Models\Maintainance;
use App\Models\OrderServiceItemsModel;
use App\Models\OrderStatusHistory;
use DB;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Validator;
use App\Models\ActivityType;
use App\Models\Rating;


class OrderController extends Controller
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
                'status' => 0,
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
                    'status' => 0,
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ]);
                exit;
                return response()->json([
                    'status' => 0,
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ], 401);
                exit;
            }
        }
    }
    public function update_activity(){
        $order_list = OrderModel::orderBy('order_id', 'desc')->with(['products' => function ($qr) {
            $qr->with('vendor.vendordata')->select('order_products.id', 'order_id', 'product_id', 'default_attribute_id', 'order_products.vendor_id')->join('product', 'product.id', 'order_products.product_id');
        }])->get();

        foreach ($order_list as $key => $val) {
            $products = $val->products;
            foreach ($products as $pkey => $pval) {
                if(isset($pval->vendor->vendordata)){
                    $store = $pval->vendor->vendordata;
                    $val->activity_id = $pval->vendor->activity_id;
                    $val->save();
                }
            }
        }
        $order_list = OrderModel::orderBy('order_id', 'desc')->get();
        return $order_list;
    }
    public function my_orders(REQUEST $request)
    {
        $status = 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $order_list = OrderModel::orderBy('order_id', 'desc')->with(['products' => function ($qr) {
                $qr->with('vendor.vendordata')->select('order_products.id', 'order_id', 'product_id', 'default_attribute_id', 'order_products.vendor_id')->join('product', 'product.id', 'order_products.product_id');
            }])->where('user_id', $user_id);
            if($request->activity_id){
                $order_list->where('activity_id',$request->activity_id);
            }else{
                // $order_list->where('activity_id',0);

            }
            $order_list = $order_list->get();


            foreach ($order_list as $key => $val) {
                $order_list[$key]->status_text = order_status($val->status);
                $order_list[$key]->order_no = config('global.sale_order_prefix') . date(date('Ymd', strtotime($val->created_at))) . $val->order_id;
                $order_list[$key]->payment_mode_id = (string) $val->payment_mode;
                $order_list[$key]->payment_mode = payment_mode($val->payment_mode);
                $products = $val->products;
                $order_list[$key]->order_date = get_date_in_timezone($val->created_at, config('global.datetime_format'));

                $order_number   = config('global.sale_order_prefix') . date(date('Ymd', strtotime($val->created_at))) . $val->order_id;
                $order_list[$key]->invoice_url = get_pdf_url($order_number);
                if($val->address_id){
                    $order_list[$key]->address = \App\Models\UserAdress::get_address_details($val->address_id) ??[];
                    $order_list[$key]->address = convert_all_elements_to_string($order_list[$key]->address);
                }
                $order_list[$key]->order_type_text = order_type($val->order_type);
                foreach ($products as $pkey => $pval) {
                    $order_list[$key]->vendor_id = $pval->vendor_id;
                    if(isset($pval->vendor->vendordata)){
                        $store = $pval->vendor->vendordata;
                        $order_list[$key]->store = ['id'=>(string)$store->id,'store_id'=>(string)$store->id,'company_name'=>$store->company_name,'logo'=>$store->logo];
                    }
                    unset($order_list[$key]->products[$pkey]->vendor);
                    $product_image = '';
                    if ($pval->default_attribute_id) {
                        $det = DB::table('product_selected_attribute_list')->select('image')->where('product_id', $pval->product_id)->where('product_attribute_id', $pval->default_attribute_id)->first();
                        if ($det) {
                            $images = $det->image;
                            $images = explode(",", $det->image);
                            $images = array_values(array_filter($images));
                            $product_image = (count($images) > 0) ? $images[0] : $det->image;
                        }
                    } else {
                        $det = DB::table('product_selected_attribute_list')->select('image')->where('product_id', $pval->product_id)->orderBy('product_attribute_id', 'DESC')->limit(1)->first();
                        if ($det) {
                            $images = $det->image;
                            $images = explode(",", $det->image);
                            $images = array_values(array_filter($images));
                            $product_image = (count($images) > 0) ? $images[0] : $det->image;
                        }
                    }
                    $products[$pkey]->image = $product_image ? get_uploaded_image_url(config('global.upload_path').config('global.product_image_upload_dir').$product_image) : '';
                    // $products[$pkey]->image = $product_image ? url(config('global.upload_path') . '/' . config('global.product_image_upload_dir') . $product_image) : '';
                }
                $order_list[$key]->products = $products;
                $status_history = OrderStatusHistory::where(['order_id'=>$val->order_id])->orderBy('status_id','desc')->get();
                    
                    foreach ($status_history as $key_his => $value_his) {
                        $status_history[$key_his]->status_text = order_status($value_his->status_id);
                        $status_history[$key_his]->date_time = get_date_in_timezone($value_his->created_at, 'Y-m-d H:i:s');
                    }
                $order_list[$key]->status_history = $status_history->toarray();
            }
            $o_data = convert_all_elements_to_string($order_list);
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    public function my_order_details(REQUEST $request)
    {
        $status = 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $order = OrderModel::with(['ref_code_history','products' => function ($qr) {
                $qr->with('vendor.vendordata')->select('order_products.*', 'product_attribute_id as product_variant_id', 'default_attribute_id', 'product_name', 'order_products.vendor_id')->join('product', 'product.id', 'order_products.product_id');
            }])->where('user_id', $user_id)->where('order_id', $request->order_id)->first();

            if ($order) {
                $order->ref_code_name = $order->ref_code_history ? $order->ref_code_history->name : '';
                $order->id = (string)($order->order_id);
                $order->order_no = config('global.sale_order_prefix') . date(date('Ymd', strtotime($order->created_at))) . $order->order_id;
                $order->grand_total = number_format($order->grand_total, 2, '.', '');
                $order->vat = number_format($order->vat, 2, '.', '');
                $order->status_text = order_status($order->status);
                if($order->address_id){
                    $order->address = \App\Models\UserAdress::get_address_details($order->address_id) ??[];
                    $order->address = convert_all_elements_to_string($order->address);
                }
                $order->order_type_text = order_type($order->order_type);

                $order->payment_mode_id = (string) $order->payment_mode;
                $order->payment_mode = payment_mode($order->payment_mode);
                $order->order_date = get_date_in_timezone($order->created_at, config('global.datetime_format'));

                $order_number   = config('global.sale_order_prefix') . date(date('Ymd', strtotime($order->created_at))) . $order->order_id;
                $order->invoice_url = get_pdf_url($order_number);

                $products = $order->products;


                foreach ($products as $pkey => $pval) {
                    // $order->vendor_id = $pval->vendor_id;
                    if(isset($pval->vendor->vendordata)){
                        $store = $pval->vendor->vendordata;

                        $store->rating      = number_format(\App\Models\Rating::avg_rating(['vendor_id'=>$order->vendor_id]), 1, '.', '');
                        $store->rating_count = \App\Models\Rating::where('vendor_id',$order->vendor_id)->get()->count() ?? 0;

                        $store_timing = check_store_open($request,$order->vendor_id,'1');
                        $store->open_time = $store_timing['open_time'] ?? '';
                        $store->close_time = $store_timing['close_time'] ?? '';
                        $store->store_is_open = $store_timing['open'] ?? '0';

                        $stor  = [
                            'id'=>(string)$store->id,
                            'store_id'=>(string)$store->id,
                            'company_name'=>$store->company_name,
                            'logo'=>$store->logo,
                            'available_from'  => $store->open_time." - ".$store->close_time,
                            'store_is_open'  => $store->store_is_open,
                            'rating'=>$store->rating,
                            'rating_count'=>$store->rating_count,
                            'is_ratted'=> Rating::where('vendor_id',$order->vendor_id)->where('user_id',$user_id)->count() ? '1' : '0',
                        ];

                        $order->store = $stor;
                    }
                    unset($order->products[$pkey]->vendor);
                    
                    $products[$pkey]->order_status_text = order_status($pval->order_status);
                    $products[$pkey]->brand = (string) $pval->brand;

                    $products[$pkey]->avg_rating = number_format(Rating::avg_rating(['product_id'=>$pval->product_id]), 1, '.', '');
                    $products[$pkey]->rating_count = Rating::where('product_id',$pval->product_id)->get()->count();

                    $products[$pkey]->is_ratted = Rating::where('product_id',$pval->product_id)->where('user_id',$user_id)->count() ? '1' : '0';

                    $product_image = '';
                    if ($pval->default_attribute_id) {
                        $det = DB::table('product_selected_attribute_list')->select('image','product_desc')->where('product_id', $pval->product_id)->where('product_attribute_id', $pval->default_attribute_id)->first();
                        if ($det) {
                            $images = $det->image;
                            $images = explode(",", $det->image);
                            $images = array_values(array_filter($images));
                            $product_image = (count($images) > 0) ? $images[0] : $det->image;
                        }
                    } else {
                        $det = DB::table('product_selected_attribute_list')->select('image','product_desc')->where('product_id', $pval->product_id)->orderBy('product_attribute_id', 'DESC')->limit(1)->first();
                        if ($det) {
                            $images = $det->image;
                            $images = explode(",", $det->image);
                            $images = array_values(array_filter($images));
                            $product_image = (count($images) > 0) ? $images[0] : $det->image;
                        }
                    }
                    $product_attributes = \App\Models\ProductModel::getSelectedProductAttributeVals($pval->product_attribute_id);
                    if($product_attributes && $product_attributes->attribute_name){
                        $products[$pkey]->attribute_name = (string) $product_attributes->attribute_name;
                        $products[$pkey]->attribute_values = (string) $product_attributes->attribute_values;
                    }
                    
                    $product_attributes_full = \App\Models\ProductModel::getSelectedProductAttributeValsFull($pval->product_attribute_id);
                    if($product_attributes_full){
                    $products[$pkey]->selected_attribute_list = $product_attributes_full->toArray();
                    }else{
                        $products[$pkey]->selected_attribute_list = [];
                    }


                    $products[$pkey]->product_desc = $det->product_desc;
                    $products[$pkey]->image = $product_image ? get_uploaded_image_url(config('global.upload_path').config('global.product_image_upload_dir').$product_image) : '';
                    // $products[$pkey]->image = $product_image ? url(config('global.upload_path') . '/' . config('global.product_image_upload_dir') . $product_image) : '';
                }
                $order->products = convert_all_elements_to_string($products);
                $status_history = OrderStatusHistory::where(['order_id'=>$order->order_id])->orderBy('status_id','desc')->get();
                    
                    foreach ($status_history as $key_his => $value_his) {
                        $status_history[$key_his]->status_text = order_status($value_his->status_id);
                        $status_history[$key_his]->date_time = get_date_in_timezone($value_his->created_at, 'Y-m-d H:i:s');
                    }
                $order->status_history = $status_history->toarray();
                unset($order->order_id);

                
            }
            $o_data = convert_all_elements_to_string($order);
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => ($o_data)], 200);
    }
    public function re_order(REQUEST $request)
    {
        $status = 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $order = OrderModel::with(['ref_code_history','products' => function ($qr) {
                $qr->with('vendor.vendordata')->select('order_products.*', 'product_attribute_id as product_variant_id', 'default_attribute_id', 'product_name', 'order_products.vendor_id')->join('product', 'product.id', 'order_products.product_id');
            }])->where('user_id', $user_id)->where('order_id', $request->order_id)->first();
            if ($order) {
                if($order->status != 4){
                    $status = 0;
                    $message = "Only Delivered order can be re-order.";
                    return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => ($o_data)], 200);
                }

                $products = $order->products;
                $cart = new \App\Http\Controllers\Api\v1\CartController(app('firebase.database'));

                foreach ($products as $pkey => $pval) {
                    $request->merge(['product_id' => $pval->product_id]);
                    $request->merge(['product_variant_id' => $pval->product_variant_id]);
                    $request->merge(['quantity' => $pval->quantity]);
                    $request->merge(['clear_cart' => '']);
                    if($pkey == 0){
                        $request->merge(['clear_cart' => 1]);
                    }
                    $add_to_cart = $cart->add_to_cart($request);
                }
                $get_cart = $cart->get_cart($request)->original;
                $status = 1;
                $message = "Items has been added to the cart for re-order.";
                $o_data = $get_cart['oData'] ?? [];
            }
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => ($o_data)], 200);
    }
    public function cancel_order(REQUEST $request)
    {
        $status = 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $order = OrderModel::with(['products'])->where('user_id', $user_id)->where('order_id', $request->order_id)->first();

            if ($order) {
                $highest_order_prd_status = OrderModel::where('order_id', $request->order_id)->orderby('status', 'desc')->first();

                if (isset($highest_order_prd_status->status) && $highest_order_prd_status->status <= 2) {
                    $users = User::find($user_id);

                    if (in_array($order->payment_mode, [1,2,3,4])) {
                        $amount_to_credit = $order->grand_total;
                        $data = [
                            'user_id' => $user_id,
                            'wallet_amount' => $amount_to_credit,
                            'pay_type' => 'credited',
                            'pay_method' => $order->payment_mode,
                            'description' => 'Order cancellation amount refunded to wallet.',
                        ];

                        if (wallet_history($data)) {
                            $users->wallet_amount = $users->wallet_amount + $amount_to_credit;
                            $users->save();
                        }
                    }


                    $c_st = config('global.order_status_cancelled');
                    OrderModel::where('order_id', $request->order_id)->update(['status' => $c_st]);
                    OrderProductsModel::where('order_id', $request->order_id)->update(['order_status' => $c_st]);

                    $items = OrderProductsModel::with('variant')->where('order_id', $request->order_id)->get();
                    if($items->count()){
                        foreach ($items as $key => $row) {
                            $variant = $row->variant;
                            $variant->stock_quantity = $variant->stock_quantity + $row->quantity;
                            $variant->save();
                        }
                    }

                    $check =  OrderStatusHistory::where('order_id',$request->order_id)->where('status_id',10)->get()->count();
                    if($check == 0)
                    {
                    $datastatusins = new OrderStatusHistory;
                    $datastatusins->order_id = $request->order_id;
                    $datastatusins->status_id = 10;
                    $datastatusins->created_at = gmdate('Y-m-d H:i:s');
                    $datastatusins->updated_at = gmdate('Y-m-d H:i:s');
                    $datastatusins->save();
                    }

                    $status = 1;
                    $message = "Your order has been cancelled successfully.";

                    if (in_array($order->payment_mode, [1,2,3,4])) {
                        $message = "Your order has been cancelled successfully. Amount has been refunded to your wallet.";
                    }
                    $order_no = config('global.sale_order_prefix').date(date('Ymd', strtotime($order->created_at))).$order->order_id;

                    $title = $order_no;//'Order Cancelled';
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
                    \Artisan::call("send:send_grocery_booking_notification --uri=" . $order->order_id . " --uri2=" . 'cancellation' );
                } else {
                    $status = 0;
                    $message = "You can't cancel this order";
                }
            }
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }

    public function refund_request(REQUEST $request)
    {
        $status = 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'order_id' => 'required|numeric|min:1',
            'payment_mode' => 'required|numeric|min:1|max:2',
            'type' => 'required|numeric|min:1|max:2',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            if ($request->type == 1) { //product order
                $order = OrderModel::with(['products'])->where('user_id', $user_id)->where('order_id', $request->order_id)->first();

                if ($order) {
                    $highest_order_prd_status = OrderProductsModel::where('order_id', $request->order_id)->orderby('order_status', 'desc')->first();

                    if (isset($highest_order_prd_status->order_status) && $highest_order_prd_status->order_status == 10) {
                        $amount_to_credit = $order->grand_total;

                        $users = User::find($user_id);
                        // $users->wallet_amount = $users->wallet_amount + $amount_to_credit;
                        // $users->save();
                        $c_st = config('global.order_status_cancelled');
                        $indata = [
                            'refund_method' => $request->payment_mode,
                            'refund_requested' => 1,
                            'refund_requested_date' => gmdate('Y-m-d H:i:s'),
                        ];
                        OrderModel::where('order_id', $request->order_id)->update($indata);
                        //OrderProductsModel::where('order_id', $request->order_id)->update(['order_status'=>$c_st]);
                        $status = 1;
                        $message = "Your order refund request send successfully.";


                        $title = 'Refund request send successfully';
                        $description = $message;
                        $notification_id = time();
                        $ntype = 'refund_request';
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
                    } else {
                        $status = 0;
                        $message = "You can't request refund for this order";
                    }
                } else {
                    $status = 0;
                    $message = "Error";
                }
            } else {

                //for service
                $order = OrderServiceModel::with(['services'])->where('user_id', $user_id)->where('order_id', $request->order_id)->first();
                if ($order) {
                    $highest_order_prd_status = OrderServiceItemsModel::where('order_id', $request->order_id)->orderby('order_status', 'desc')->first();


                    if (isset($highest_order_prd_status->order_status) && $highest_order_prd_status->order_status == 10) {
                        $users = User::find($user_id);
                        // $users->wallet_amount = $users->wallet_amount + $amount_to_credit;
                        // $users->save();
                        $c_st = config('global.order_status_cancelled');
                        $indata = [
                            'refund_method' => $request->payment_mode,
                            'refund_requested' => 1,
                            'refund_requested_date' => gmdate('Y-m-d H:i:s'),
                        ];
                        OrderServiceModel::where('order_id', $request->order_id)->update($indata);
                        //OrderProductsModel::where('order_id', $request->order_id)->update(['order_status'=>$c_st]);
                        $status = 1;
                        $message = "Your service order refund request send successfully.";


                        $title = 'Service Refund request send successfully';
                        $description = $message;
                        $notification_id = time();
                        $ntype = 'service_refund_request';
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
                    } else {
                        $status  = 0;
                        $message = "You can't request refund for this service order";
                    }
                } else {
                    $status = 0;
                    $message = "Invalid order id";
                }
            }
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function contracting(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $input = $request->all();
            $ins = [
                'description' => $request->description,
                'building_type' => $request->building_type,
                'contract_type' => $request->contract_type,

            ];

            if ($request->file("file")) {
                $response = image_save($request, config('global.service_image_upload_dir'), 'file');
                if ($response['status']) {
                    $ins['file'] = $response['link'];
                }
            }

            $ins['created_at'] = gmdate('Y-m-d H:i:s');
            Contracting::create($ins);

            $status = "1";
            $message = "Contracting Submitted successfully";
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors], 200);
    }

    public function maintainance(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $input = $request->all();
            $ins = [
                'description' => $request->description,
                'building_type' => $request->building_type,
            ];

            if ($request->file("file")) {
                $response = image_save($request, config('global.service_image_upload_dir'), 'file');
                if ($response['status']) {
                    $ins['file'] = $response['link'];
                }
            }

            $ins['created_at'] = gmdate('Y-m-d H:i:s');
            Maintainance::create($ins);

            $status = "1";
            $message = "Maintainance Submitted successfully";
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors], 200);
    }
    function bookingCount(Request $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => (string) 0,
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $user_id = $this->validateAccesToken($request->access_token);
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();
        


        
        //banner END
        $activities  = ActivityType::where(['deleted' => '0','status' => '1'])->whereNotIn('id',[2])
        ->orderBy('sort_order', 'asc')
        ->select('id','name','description','logo')->get();
        foreach ($activities as $key => $value) {
            $count = 0;
            if($value->id == 7 || $value->id == 5 || $value->id == 3)
            {
                $activity_id = $value->id;
                $count = OrderModel::where('user_id',$user_id)->where('activity_id',$activity_id)->count();
            }
            else if($value->id == 6 || $value->id == 4 || $value->id == 1)
            { 
                $activity = [$value->id];
                if($value->id == 6)
                {
                    $activity = [0,6];
                }
                
                $count = OrderServiceModel::where('user_id',$user_id)->whereIn('activity_id',$activity)->get()->count();
            }
            $activities[$key]->count = $count;
        }



     
        $o_data['activities']  = $activities;
        

        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => convert_all_elements_to_string($o_data)], 200);
    }
}