<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\OrderModel;
use App\Models\OrderProductsModel;
use App\Models\ProductModel;
use App\Models\SettingsModel;
use App\Models\VendorDetailsModel;
use App\Models\User;
use App\Models\UserAdress;
use App\Models\Coupons;
use App\Models\CouponCategory;
use Illuminate\Http\Request;
use Validator;
use Kreait\Firebase\Contract\Database;
use App\Models\ContactUsSetting;
use App\Models\VendorModel;
use App\Models\VendorServiceTimings;
use App\Models\ProductCategory;
use App\Models\Categories;
use App\Models\CouponProducts;
use App\Models\CouponVendor;


class CartController extends Controller
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
    public function add_to_cart(REQUEST $request)
    {
        $status = (string) 0;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            // 'access_token' => 'required',
            'device_cart_id' => 'required',
            'product_id' => 'required|numeric|min:0|not_in:0',
            'product_variant_id' => 'required|numeric|min:0|not_in:0',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = 0;
            if($request->access_token){
                $user_id = $this->validateAccesToken($request->access_token);
            }
            $cart_condition = [
                "device_cart_id" => $request->device_cart_id,
                "user_id" => $user_id,
            ];
            // if($user_id){
            //     $cart_condition = [
            //         "device_cart_id" => $request->device_cart_id,
            //         "user_id" => $user_id,
            //     ];
            // }
            if ($request->clear_cart && Cart::where($cart_condition)->delete()) {
                $message = "Cart cleared";
            }
            $device_cart_id = $request->device_cart_id;
            $product_id = $request->product_id;
            $product_variant_id = $request->product_variant_id;
            list($product_status, $product_data, $message) = ProductModel::getProductVariant($product_id, $product_variant_id);
            $vendor_id = $product_data->product_vender_id;
            $quantity = $request->quantity ?? 1;
            if ($product_status && !empty($product_data)) {

                $cart_store_check =  Cart::where('store_id', '!=', $product_data['product_vender_id']);
                if ($user_id) {
                    $cart_store_check = $cart_store_check->where('user_id', '=', $user_id)->where('device_cart_id', '=', $device_cart_id);
                } else {
                    $cart_store_check = $cart_store_check->where('device_cart_id', '=', $device_cart_id);
                }
                $cart_store_check = $cart_store_check->get();
                if ($cart_store_check->count() > 0) {
                    $status = (string)4;
                    $message = "Your cart contains product from other distributor. Do you want to replace it?";
                    $o_data = $this->process_cart($user_id,1,$request);
                } else {

                    $cart_key = '';

                    $i_data['user_id'] = $user_id;
                    $i_data['product_id'] = $product_id;
                    $i_data['product_attribute_id'] = (int) $product_variant_id;
                    $i_data['quantity'] = $quantity;
                    $i_data['device_cart_id'] = $device_cart_id;
                    $i_data['store_id'] = $vendor_id;
                    $i_data['created_at'] = gmdate("Y-m-d H:i:s");

                    $in_stock = $product_data->stock_quantity;

                    $cart_condition = [];
                    $cart_condition = [
                        "product_id" => $i_data['product_id'],
                        "product_attribute_id" => $i_data['product_attribute_id'],
                        "user_id" => $user_id,
                    ];

                    if($user_id==0){
                        $cart_condition = [
                            "product_id" => $i_data['product_id'],
                            "product_attribute_id" => $i_data['product_attribute_id'],
                            "device_cart_id" => $device_cart_id,
                            "user_id"=>0
                        ];
                    }

                    $product_cart_data = Cart::get_user_cart($cart_condition);
                    if (count($product_cart_data)) {
                        $status = (string) 0;

                        if (count($product_cart_data) == 1) {
                            $product_cart_data = $product_cart_data[0];
                            if ((($quantity + $product_cart_data->quantity) <= $in_stock) || ($product_data->allow_back_order == 1)) {
                                $quantity = $quantity + $product_cart_data->quantity;
                                Cart::update_cart(["quantity" => $quantity], ["id" => $product_cart_data->id]);

                                $status = (string) 1;
                                $message = "Product added to your cart";
                            } else {
                                $status = (string) 0;
                                $message = "Unable to increase the product quantity beacuse you reached maximum level of stock";
                            }
                        } else {
                            $status = (string) 0;
                            $message = 'This item has multiple customizations added. Increase the correct item from the cart';
                        }
                    } else {
                        if (($quantity <= $in_stock) || ($product_data->allow_back_order == 1)) {
                            Cart::create_cart($i_data);
                            $status = (string)(string) 1;
                            $message = "Product added to your cart";
                        } else {
                            $status = (string) 0;
                            $message = "Unable to add product to cart due to limited stock";
                        }
                    }

                    $o_data = $this->process_cart($user_id,1,$request);
                    $status = $status ?? (string) 1;
                }
            } else {
                $status = (string) 0;
                $message = 'No product exists';
            }
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
    }
    public function get_cart(Request $request)
    { 
        $status = (string) 1;
        $message = "";
        $errors = [];
        $o_data = [];
        $erservice_requestrors = [];
        $validator = Validator::make($request->all(), [
            // 'access_token' => 'required',
            'device_cart_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = 0;
            if($request->access_token){
                $user_id = $this->validateAccesToken($request->access_token);
            }

            $o_data = $this->process_cart($user_id,1,$request,TRUE);
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function checkout(Request $request)
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
            $o_data = $this->process_cart($user_id,1,$request);
            //printr($o_data);
            if(!$o_data['store_is_open']){
                $status = (string) 0;
                $message = "Sorry our store is closed. We are unable to process your order.";
                return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => (object)[]]);
            }
            $discount = 0;

            $coupon = [];
            $coupon_id = 0;

            if ($request->coupon_code) {

                list($status, $message, $result) = $this->apply_coupon_fun($request->access_token, $request->coupon_code);

                if ($status == 1) {
                    $o_data  = $result;
                }
                $message = $message;
            }
            $history = \App\Models\RefHistory::with('accepted_user')->where([['sender_id',$user_id],['status',1]])->orderBy('id','asc')->first();

            if ($history) {

                list($status, $message, $result) = $this->apply_ref_fun($request->access_token, $history);

                if ($status == 1) {
                    $o_data  = $result;
                }
                $message = $message;
            }

            $o_data['default_address'] = UserAdress::get_user_default_address($user_id);
            $o_data['address_list'] = UserAdress::get_address_list($user_id);
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => convert_all_elements_to_string($o_data)]);
    }
    public function update_cart(Request $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            // 'device_cart_id' => 'required',
            // 'access_token' => 'required',
            'cart_id' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            // $user_id = $this->validateAccesToken($request->access_token);

            // $cart_condition = [
            //     "id" => $request->cart_id,
            //     "user_id" => $user_id,
            // ];

            $user_id = 0;
            if($request->access_token){
                $user_id = $this->validateAccesToken($request->access_token);
            }
            $cart_condition = [
                "id" => $request->cart_id,
            ];
            if($user_id){
                $cart_condition = [
                    "id" => $request->cart_id,
                    // "device_cart_id" => $request->device_cart_id,
                    "user_id" => $user_id,
                ];
            }else{
                $cart_condition = [
                    "id" => $request->cart_id,
                    "device_cart_id" => $request->device_cart_id,
                    "user_id" => 0,
                ];
            }

            if ($request->type == "add") {
                // if (Cart::where($cart_condition)->increment('quantity', 1)) {
                //     $message = "Cart updated";
                // } else {
                //     $message = "Invalid data passed";
                // }

                $add = true;
                $product_cart_data = Cart::get_user_cart($cart_condition);
                if (count($product_cart_data) == 1) {
                    $product_cart_data = $product_cart_data[0];
                    list($product_status, $product_data, $message) = ProductModel::getProductVariant($product_cart_data->product_id, $product_cart_data->product_variant_id);
                    // dd($product_data,($product_cart_data->quantity > $product_data->stock_quantity));
                    if(($product_cart_data->quantity >= $product_data->stock_quantity) && ($product_data->allow_back_order == 0)){
                        $add = false;
                    }
                }
                if ($add) {
                    Cart::where($cart_condition)->increment('quantity', 1);
                    $message = "Cart updated";
                } else {
                    $status = (string) 0;
                    $message = "Unable to increase the product quantity beacuse you reached maximum level of stock";
                }
                
            } else {
                if (Cart::where($cart_condition)->decrement('quantity', 1)) {
                    $message = "Cart updated";
                } else {
                    $status = (string) 0;
                    $message = "Invalid data passed";
                }
            }

            $o_data = $this->process_cart($user_id,1,$request);
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function process_cart($user_id,$api='1',$request=null,$is_cart=FALSE)
    {
        $tax_amount = 0;
        if($user_id){
            $where['cart.user_id'] = $user_id;
        }else{
            if($request  && $request->device_cart_id){
                 $where['cart.user_id'] = '0';
                 $where['cart.device_cart_id'] = $request->device_cart_id;
            }
        }
        $where['product.deleted'] = 0;
        $where['product.product_status'] = 1;
        $where['product.deleted'] = 0;
        $where['product.product_status'] = 1;


        $cart = Cart::select('cart.*')->where($where)->join('vendor_details', 'vendor_details.user_id', 'cart.store_id')->join('product', 'product.id', 'cart.product_id')->get();
        $product = [];

        $cart_total = 0;
        $vendor_id = 0;
        $taxable_amount  = 0;
        foreach ($cart as $key => $val) {
            list($status, $product, $message) = ProductModel::getProductVariant($val->product_id, $val->product_attribute_id);
            if ($status && !empty($product)) {
                $vendor_id = $product['product_vender_id'];

                $product = process_product_data_api($product);
                $amt = $product['regular_price'];
                if ($product['sale_price']) {
                    $amt = $product['sale_price'];
                }
                $product_total_amount = $amt * $val->quantity;
                $product['total_amount'] = $product_total_amount;
                $cart_total += $product_total_amount;
                 if (isset($product['product_taxable']) && $product['product_taxable'] == 1) {
                    $taxable_amount += $product_total_amount;
                 }
            }
            $cart[$key]->product_details = $product;
        }

        $settings = SettingsModel::first();
        $tax_percentage = 0;
        if (isset($settings->tax_percentage)) {
            $tax_percentage = $settings->tax_percentage;
        }
        
        $shipping_charge = 0;
        $vendor = VendorModel::where('role','3')->where('id',$vendor_id)->first();
        $shipping_charge = $vendor->delivery_charge??0;
        if(empty($shipping_charge))
        {
            if (isset($settings->shipping_charge)) {
                $shipping_charge = $settings->shipping_charge;
            }
        }
        if($request  && $request->order_type == 1){
            $shipping_charge = 0;
        }
        if($is_cart == TRUE){
            $shipping_charge = 0;
        }
        $service_charge = 0;
        if(!empty($settings->service_charge))
        {   
            if(!empty($cart_total))
            {
                $service_charge = $settings->service_charge;
                $service_charge = ( $taxable_amount * $service_charge ) / 100;
            }
            
        }
        if (!empty($taxable_amount) && !empty($tax_percentage)) {
            $tax_amount = ( ( $taxable_amount + $shipping_charge + $service_charge ) * $tax_percentage) / 100;
        }
        $grand_total = $tax_amount + $cart_total + $shipping_charge + $service_charge;
        $cart_count = count($cart);
        $cart_items = $cart;

        
        $minimum_order_amount = $vendor ? $vendor->minimum_order_amount : 0;

        $store_is_open = check_store_open($request,$vendor_id);

        if($api == '1'){
            $cart_items = convert_all_elements_to_string($cart_items);
        }
       
        return [
            'cart_total'            => number_format($cart_total, 2, '.', ''), 
            'tax_amount'            => number_format($tax_amount, 2, '.', ''), 
            'grand_total'           => number_format($grand_total, 2, '.', ''), 
            'cart_count'            => (string)$cart_count,
            'minimum_order_amount'  => (string)$minimum_order_amount, 
            'store_is_open'         => (string)$store_is_open, 
            'activity_id'           => (string)$vendor ? $vendor->activity_id : '', 
            'is_dinein'             => (string)($vendor ? ($vendor->is_dinein ? 1 : 0) : 0), 
            'is_delivery'           => (string)($vendor ? ($vendor->is_delivery ? 1 : 0) : 0), 
            'cart_items'            => ($cart_items),
            'taxable_amount'           => number_format($taxable_amount, 2, '.', ''), 
            'shipping_charge'       => number_format($shipping_charge, 2, '.', ''), 
            'service_charge'       => number_format($service_charge, 2, '.', ''), 
        ];
    }
    
    public function delete_cart(Request $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            // 'access_token' => 'required',
            'cart_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            // $user_id = $this->validateAccesToken($request->access_token);
            // $cart_condition = [
            //     "id" => $request->cart_id,
            //     "user_id" => $user_id,
            // ];

            $user_id = 0;
            if($request->access_token){
                $user_id = $this->validateAccesToken($request->access_token);
            }
            $cart_condition = [
                "id" => $request->cart_id,
            ];
            if($user_id){
                $cart_condition = [
                    "id" => $request->cart_id,
                    // "device_cart_id" => $request->device_cart_id,
                    "user_id" => $user_id,
                ];
            }


            if (Cart::where($cart_condition)->delete()) {
                $message = "Cart item removed";
            }

            $o_data = $this->process_cart($user_id,1,$request);
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function clear_cart(Request $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            // 'access_token' => 'required',
            'device_cart_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            // $user_id = $this->validateAccesToken($request->access_token);
            // $cart_condition = [
            //     "user_id" => $user_id,
            // ];
            $user_id = 0;
            if($request->access_token){
                $user_id = $this->validateAccesToken($request->access_token);
            }
            $cart_condition = [
                "device_cart_id" => $request->device_cart_id,
            ];
            if($user_id){
                $cart_condition = [
                    "device_cart_id" => $request->device_cart_id,
                    // "device_cart_id" => $request->device_cart_id,
                    "user_id" => $user_id,
                ];
            }
            if (Cart::where($cart_condition)->delete()) {
                $message = "Cart cleared";
            }
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function payment_init(Request $request)
    {
        $status = (string) 0;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'payment_type' => 'required|integer|min:1',
            // 'address_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $cart_details = $this->process_cart($user_id,'2',$request);

            if(!$cart_details['store_is_open']){
                $status = (string) 0;
                $message = "Sorry our store is closed. We are unable to process your order.";
                return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => (object)[]]);
            }
            
            $adress = $request->address_id ?  UserAdress::where([['user_id' , $user_id],['id' , $request->address_id]])->first() : new UserAdress();
            if(!$adress){
                $message = "No address found.";
                return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object) $o_data]);
            }
            $discount = 0;

            $coupons = [];
            $coupon_id = 0;
            $vendor_id = 0;
            foreach ($cart_details['cart_items'] as $key => $val) {
                $vendor_id = $val['product_details']['product_vendor_id'];
            }

            if ($request->coupon_code) {
                $coupons = Coupons::where(['coupon_code' => $request->coupon_code, 'coupon_status' => 1])->where('start_date', '<=', date('Y-m-d'))->where('coupon_end_date', '>=', date('Y-m-d'))->get();
            }
            $history = \App\Models\RefHistory::with('accepted_user')->where([['sender_id',$user_id],['status',1]])->orderBy('id','asc')->first();
            foreach ($coupons as $coupon)
            {
                
                if ($coupon) {
                    if ($cart_details['grand_total'] < $coupon->minimum_amount) {
                    } else {
                        $applied_to = $coupon->applied_to;
                        $amount_type = $coupon->amount_type;
                        $amount = $coupon->coupon_amount;
                        // $categories = CouponCategory::where('coupon_id', $coupon->coupon_id)->get()->toArray();
                        // $categories = array_column($categories, 'category_id');
                        $categories = CouponCategory::where('coupon_id', $coupon->id)->pluck('category_id')->toArray();
                        $coupon_id = $coupon->coupon_id;
                        foreach ($cart_details['cart_items'] as $key => $val) {
                            $det = $val['product_details'];
                            if ($applied_to == 1) {
                                $p_cat_ids = ProductCategory::where('product_id',$val->product_details['product_id'])->pluck('category_id')->toArray();
                                $categories2 = Categories::whereIn('id', $p_cat_ids)->pluck('parent_id')->toArray();
                                $categories_count = count($categories);
                                // if (in_array($val->product_details['category_id'], $categories)) {
                                if ($categories_count === 0 || (count(array_intersect($categories2, $categories)))) {
                                    $dis = $amount;
                                    if ($amount_type == 1) {
                                        $dis = ($val->product_details['total_amount'] * $amount) / 100;
                                    }
                                    $det['coupon_discount'] = $dis;
                                    $det['grand_total'] = $val->product_details['total_amount'] - $dis;
                                    $discount += $dis;
                                } else {
                                    $det['coupon_discount'] = 0;
                                    $det['grand_total'] = $val->product_details['total_amount'];
                                }
                            } else {
                                $pro_ids = CouponProducts::where('coupon_id', $coupon->id)->pluck('product_id')->toArray();
                                if($pro_ids && in_array($val->product_details['product_id'],$pro_ids)){
                                    $dis = $amount;
                                    if ($amount_type == 1) {
                                        $dis = ($val->product_details['total_amount'] * $amount) / 100;
                                    }
                                    $det['coupon_discount'] = $dis;
                                    $det['grand_total'] = $val->product_details['total_amount'];
                                    $discount = $dis;
                                }else{
                                    $dis = $amount;
                                    if ($amount_type == 1) {
                                        $dis = ($val->product_details['total_amount'] * $amount) / 100;
                                    }
                                    $det['coupon_discount'] = 0;
                                    $det['grand_total'] = $val->product_details['total_amount'];
                                    $discount = $dis;
                                }
                            }
                            $cart_details['cart_items'][$key]['product_details'] = $det;
                        }
                        $cart_details['discount'] = $discount;
                        $settings = SettingsModel::first();
                        $tax_percentage = 0;
                        if (isset($settings->tax_percentage)) {
                            $tax_percentage = $settings->tax_percentage;
                        }
                        
                        $service_charge_percentage = 0;
                        if (isset($settings->service_charge)) {
                            $service_charge_percentage = $settings->service_charge;
                        }
                        
                        $cart_details['service_charge'] = (string) ( ($cart_details['cart_total'] - $discount) * $service_charge_percentage ) / 100;
                        $cart_details['tax_amount'] = (string) ( ($cart_details['cart_total'] - $discount + $cart_details['service_charge']) * $tax_percentage ) / 100;
                        $cart_details['grand_total'] = $cart_details['cart_total'] - $discount + $cart_details['service_charge'] + $cart_details['shipping_charge'];
                        if($discount !== 0){
                            break;
                        }
                    }
                }
                
            }
            
            
            if ($history) {
                $con = ContactUsSetting::first();
                $applied_to = 0;
                $amount_type = 1;
                $amount     = $con->ref_discount;
                $discount = 0;
                foreach ($cart_details['cart_items'] as $key => $val) {
                    $vendor_id = $val['product_details']['product_vendor_id'];
                    $det = $val['product_details'];
                    $dis = $amount;
                    if ($amount_type == 1) {
                        $dis = ($val->product_details['total_amount'] * $amount) / 100;
                    }
                    $det['coupon_discount'] = $dis;
                    $det['grand_total'] = $val->product_details['total_amount'] - $dis;
                    $discount += $dis;
                    $cart_details['cart_items'][$key]['product_details'] = $det;
                }
                $cart_details['discount'] = $discount;
                $settings = SettingsModel::first();
                $tax_percentage = 0;
                if (isset($settings->tax_percentage)) {
                    $tax_percentage = $settings->tax_percentage;
                }
                
                $service_charge_percentage = 0;
                if (isset($settings->service_charge)) {
                    $service_charge_percentage = $settings->service_charge;
                }
                
                $cart_details['service_charge'] = (string) ( ($cart_details['cart_total'] - $discount) * $service_charge_percentage ) / 100;
                $cart_details['tax_amount'] = (string) ( ($cart_details['cart_total'] - $discount + $cart_details['service_charge']) * $tax_percentage ) / 100;
                $cart_details['grand_total'] = $cart_details['cart_total'] - $discount + $cart_details['service_charge'] + $cart_details['shipping_charge'];
            }
            //printr($cart_details);
            $amount_to_pay = $cart_details['grand_total'];

            if ((int) $amount_to_pay == 0) {
                $message = "Your cart is empty";
            } else {
                $vendor = VendorModel::with('vendordata')->where('role','3')->where('id',$vendor_id)->first();
                // $timings = VendorServiceTimings::where('vendor',$vendor_id)->get();
                // dd($amount_to_pay,$vendor_id,$timings);
                if($vendor->minimum_order_amount && $vendor->minimum_order_amount > $amount_to_pay){
                    $message = "Minimum order amount for this store is JOD ".$vendor->minimum_order_amount;
                }else{
                    $check = \App\Models\TempOrderModel::where(['user_id' => $user_id])->first();
                    if ($check) {
                        \App\Models\TempOrderModel::where(['user_id' => $user_id])->delete();
                        \App\Models\TempOrderProductsModel::where(['order_id' => $check->id])->delete();
                    }
                    $temp_id = $user_id . uniqid() . time();
                    $temp_order = new \App\Models\TempOrderModel();
                    $temp_order->user_id = $user_id;
                    $temp_order->address_id = $request->address_id ?? 0;
                    $temp_order->order_type = $request->order_type == 1 ? 1 : 0;

                    $temp_order->pick_up_date = $request->pick_up_date ? date('Y-m-d',strtotime($request->pick_up_date)) : '';
                    $temp_order->pick_up_time = $request->pick_up_time ? date('h:i A',strtotime($request->pick_up_time)) : '';

                    $temp_order->total = $cart_details['cart_total'];
                    $temp_order->vat = $cart_details['tax_amount'];
                    $temp_order->service_charge = $cart_details['service_charge'];
                    $temp_order->discount = $discount;
                    
                    if($history){
                        $temp_order->ref_history_id = $history->id;
                        $temp_order->ref_code = $history->ref_code;
                    }
                    
                    $temp_order->grand_total = $cart_details['grand_total'];
                    $temp_order->shipping_charge = $cart_details['shipping_charge'];
                    $temp_order->payment_mode = $request->payment_type;
                    $temp_order->temp_id = $temp_id;

                    $settings = SettingsModel::first();
                    $tax_percentage = 0;
                    if (isset($settings->tax_percentage)) {
                        $tax_percentage = $settings->tax_percentage;
                    }
                    $price_after_discount = $temp_order->total-$temp_order->discount;
                    $taxable_amount = $cart_details['taxable_amount'];
                    $tax_amount=0;
                    if (!empty($taxable_amount) && !empty($tax_percentage)) {
                        $tax_amount = ( ( $taxable_amount + $cart_details['shipping_charge'] + $cart_details['service_charge'] - $discount ) * $tax_percentage) / 100;
                    }
                    $temp_order->vat = $tax_amount;
                    $temp_order->grand_total = ($price_after_discount) + $tax_amount + $cart_details['shipping_charge'] + $cart_details['service_charge'];
                    if($request->test){
                        // dd($tax_amount,$temp_order);
                    }
                    $admin_commission = $temp_order->service_charge;
                    $vendor_commission = 0;
                    $cart_total = (int)$temp_order->grand_total - (int)$temp_order->vat - $temp_order->service_charge - $temp_order->shipping_charge;
                    $vendor->servicecommission = (int)($vendor->vendordata->first()->servicecommission ?? 0);
                    $vendor->pharmacycommission = (100 - (int)$vendor->servicecommission) > 0 ? 100 - (int)$vendor->servicecommission : 0;
                    $vendor->admin_commission_percentage = $vendor->servicecommission;
                    $vendor->vendor_commission_percentage = $vendor->pharmacycommission;
                    if (($vendor->pharmacycommission && $cart_total)) {
                        $admin_commission = ($cart_total * $vendor->admin_commission_percentage / 100) +  (int) $temp_order->service_charge;
                        $vendor_commission = ($cart_total * $vendor->vendor_commission_percentage / 100) + (int)$temp_order->vat;
                    }

                    $temp_order->admin_commission = $admin_commission;
                    $temp_order->vendor_commission = $vendor_commission;

                    $temp_order->admin_commission_percentage = $vendor->admin_commission_percentage;
                    $temp_order->vendor_commission_percentage = $vendor->vendor_commission_percentage;
                    $temp_order->save();

                    $temp_order_id = $temp_order->id;
                    foreach ($cart_details['cart_items'] as $val) {
                        $settings  = SettingsModel::first();
                        $vendor_data = VendorDetailsModel::where('user_id', $val['product_details']['product_vendor_id'])->first();
                        $vendor_commission = 0;
                        $t_amount = $val->quantity * $val['product_details']['sale_price'];
                        $vendor_data->pharmacycommission = (100 - (int)$vendor_data->servicecommission) > 0 ? 100 - (int)$vendor_data->servicecommission : 0;
                        if (!empty($vendor_data->pharmacycommission && !empty($t_amount))) {

                            $vendor_commission = $t_amount * $vendor_data->pharmacycommission / 100;
                        } else {
                            if (!empty($settings->admin_commission) && !empty($t_amount)){
                                $vendor_commission  = $t_amount * $settings->admin_commission / 100;
                            }
                        }


                        $temp_order_prds = new \App\Models\TempOrderProductsModel();
                        $temp_order_prds->order_id = $temp_order_id;

                        $temp_order_prds->product_id = $val->product_id;
                        $temp_order_prds->product_attribute_id = $val->product_attribute_id;
                        $temp_order_prds->product_type = $val['product_details']['product_type'];
                        $temp_order_prds->quantity = $val->quantity;
                        $temp_order_prds->price = $val['product_details']['sale_price'];
                        $temp_order_prds->discount = 0;
                        $temp_order_prds->total = $val['product_details']['total_amount'];
                        $temp_order_prds->vendor_id = $val['product_details']['product_vendor_id'];
                        $temp_order_prds->admin_commission = $vendor_commission;
                        $temp_order_prds->vendor_commission = $t_amount - $vendor_commission;
                        $temp_order_prds->shipping_charge = 0;
                        $temp_order_prds->save();
                    }
                    $wallet_amount_used = 0;
                    if ($request->payment_type == 2 || $request->payment_type == 3 || $request->payment_type == 4 || $request->payment_type == 5) {
                        $o_data = $this->payment_init_stripe($payment_token = $temp_id, $invoice_id = $temp_id, $amount_to_pay, $wallet_amount_used, $user_id, $request->address_id, $cart_details['tax_amount']);
                        $status = 1;
                        $message = "";
                    } else {
                        $user = User::where(['id' => $user_id])->get()->first();
                        if ($user->wallet_amount < $amount_to_pay) {
                            $status = (string) 0;
                            $message = "Insufficient wallet balance";
                        } else {
                            $wallet_amount_used = $amount_to_pay;
                            $paymentreport = [
                                'transaction_id' => $temp_id,
                                'payment_status' => 'P',
                                'user_id' => $user->id,
                                'ref_id' => $temp_id,
                                'amount' => $amount_to_pay,
                                'created_at' => gmdate('Y-m-d H:i:s'),
                                'wallet_amount_used' => $wallet_amount_used,
                            ];
                            $subTotal = $amount_to_pay;
                            $paymentreport['vat'] = $cart_details['tax_amount'];
                            \App\Models\PaymentReport::insert($paymentreport);

                            $res_status = $this->payment_success($temp_id);
                            if ($res_status === 1) {
                                $status =  1;
                                $message = "Order Placed Successfully";
                                $orderdata = OrderModel::where('invoice_id', $temp_id)->first();
                                $o_data['invoice_id']  = $temp_id;
                                $o_data['order_id']    = (string) $orderdata->order_id;
                                $o_data['order_no']    = config('global.sale_order_prefix') . date(date('Ymd', strtotime($orderdata->created_at))) . $orderdata->order_id;
                                OrderModel::where('order_id', $orderdata->order_id)->update(['order_no' => $o_data['order_no']]);
                            } else {
                                $status = (string) 0;
                                $message = "Soemthing went wrong";
                            }
                        }
                    }
                }
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object) $o_data]);
    }
    public function payment_init_stripe($payment_token, $invoice_id, $amount_to_pay, $payment_by_wallet, $user_id, $address_id, $tax_amount = 0)
    {
        $user = User::where(['id' => $user_id])->get()->first();
        $response = array();
        $data['client_reference_id'] = $invoice_id;
        $data['product'] = "Lacon";
        $data['description'] = "Product Purchase";
        $data['quantity'] = 1;
        $data['image'] = asset('/web_assets/images/logo-talents.png');
        $data['success_url'] = url('/') . '/payment_response/?sessio_id={CHECKOUT_SESSION_ID}&token=' . $payment_token;
        $data['cancel_url'] = url('/') . '/payment_cancel?sessio_id={CHECKOUT_SESSION_ID}&token={$payment_token}';
        $data['amount'] = $amount_to_pay * 100;
        $data['email'] = $user->email ?? '';

        $address = new UserAdress();
        $add_r = [];
        if($address_id){
            $address = UserAdress::get_address_details($address_id);
            $add_r = [
            'name' => $user->name ?? $user->first_name . ' ' . $user->last_name,
                'address' => [
                    'line1' => $address->address,
                    'city' => $address->city_name,
                    'state' => $address->state_name,
                    'country' => $address->country_name,
                ],
            ];
        }

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $checkout_session = \Stripe\PaymentIntent::create([
            'amount' => ceil($amount_to_pay) * 100,
            'currency' => 'JOD',
            'description' => "product purchase",
            'shipping' => $add_r,
        ]);

        $data['session_id'] = $checkout_session->id;
        $ref = $checkout_session->id;
        $paymentreport = [
            'transaction_id' => $invoice_id,
            'payment_status' => 'P',
            'user_id' => $user->id,
            'ref_id' => $payment_token,
            'amount' => $amount_to_pay,
            'created_at' => gmdate('Y-m-d H:i:s'),
            'wallet_amount_used' => $payment_by_wallet,
        ];
        $subTotal = $amount_to_pay;
        $paymentreport['vat'] = $tax_amount;
        \App\Models\PaymentReport::insert($paymentreport);

        $payment_ref = $checkout_session->client_secret;

        return compact('invoice_id', 'payment_ref');
    }
    public function payment_response()
    {
    }
    public function payment_cancel()
    {
    }
    public function place_order(Request $request)
    {
        $status = (string) 0;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'payment_type' => 'invoice_id',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $res_status = $this->payment_success($request->invoice_id);
            // dd($res_status);
            if ($res_status === 1) {
                $status = (string) 1;
                $orderdata = OrderModel::where('invoice_id', $request->invoice_id)->first();
                $message = "Order Placed Successfully";
                $o_data['invoice_id']  = $request->invoice_id;
                $o_data['order_id']    = (string) $orderdata->order_id;
                $o_data['order_no']    = config('global.sale_order_prefix') . date(date('Ymd', strtotime($orderdata->created_at))) . $orderdata->order_id;
                OrderModel::where('order_id', $orderdata->order_id)->update(['order_no' => $o_data['order_no']]);
            } else {
                $status = 0;
                $message = "Something went wrong";
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object) $o_data]);
    }
    private function payment_success($invoice_id)
    {
        //try {

            $data = \App\Models\TempOrderModel::where(['temp_id' => $invoice_id])->first();
            if ($data) {

                $order = new OrderModel();
                $order->invoice_id = $invoice_id;
                $order->user_id = $data->user_id;
                $order->address_id = $data->address_id;
                $order->order_type = $data->order_type;

                $order->pick_up_date = $data->pick_up_date ? date('Y-m-d',strtotime($data->pick_up_date)) : '';
                $order->pick_up_time = $data->pick_up_time ? date('h:i A',strtotime($data->pick_up_time)) : '';
                
                $order->ref_history_id = $data->ref_history_id;

                $order->ref_history_id = $data->ref_history_id;
                $order->ref_code = $data->ref_code;

                $order->total = $data->total;
                $order->vat = $data->vat;
                $order->discount = $data->discount;
                
                $order->grand_total = $data->grand_total;
                $order->payment_mode = $data->payment_mode;
                $order->status = 0;
                $order->booking_date = gmdate('Y-m-d H:i:s');


                $order->admin_commission = $data->admin_commission;
                $order->vendor_commission = $data->vendor_commission;

                $order->admin_commission_percentage = $data->admin_commission_percentage;
                $order->vendor_commission_percentage = $data->vendor_commission_percentage;
                $order->shipping_charge = $data->shipping_charge;
                $order->service_charge = $data->service_charge;
                $order->created_at = gmdate('Y-m-d H:i:s');
                $order->user_id = $data->user_id;

                $order->save();

                $order->order_number = order_number($order);
                $order->save();
                $history = \App\Models\RefHistory::find($data->ref_history_id);
                if($history){
                    $history->status = 2;
                    $history->save();
                }

                // dd('q');
                $order_id = $order->order_id;
                $vendor_id = '';
                $admin_commission = 0;
                $vendor_commission = 0;
                // dd($order_id);
                $order_prds_data = \App\Models\TempOrderProductsModel::where('order_id', $data->id)->get();
                $total_qty = 0;
                $total_items_qty = 0;
                foreach ($order_prds_data as $val) {
                    $total_qty += 1;
                    $total_items_qty += $val->quantity;
                    $order_prds = new OrderProductsModel();
                    $order_prds->order_id = $order_id;
                    $order_prds->product_id = $val->product_id;
                    $order_prds->product_attribute_id = $val->product_attribute_id;
                    $order_prds->product_type = $val->product_type;
                    $order_prds->quantity = $val->quantity;
                    $order_prds->price = $val->price;
                    $order_prds->discount = $val->discount;
                    $order_prds->total = $val->total;
                    $order_prds->vendor_id = $val->vendor_id;
                    $order_prds->order_status = 0;
                    $order_prds->admin_commission = $val->admin_commission;
                    $order_prds->vendor_commission = $val->vendor_commission;
                    $order_prds->shipping_charge = $val->shipping_charge;
                    $order_prds->save();
                    $vendor_id = $val->vendor_id;
                    $admin_commission = $admin_commission + $order_prds->admin_commission;
                    $vendor_commission = $vendor_commission + $order_prds->vendor_commission;

            
                    $stock = \App\Models\ProductSelectedAttributeList::where([['product_id',$val->product_id],['product_attribute_id',$val->product_attribute_id]])->first();
                    if($stock){
                        $stock->stock_quantity = $stock->stock_quantity - $val->quantity;
                        $stock->save();
                    }
                }
                $order->admin_commission_per = $order->admin_commission ;//$admin_commission;
                $order->vendor_commission_per = $order->vendor_commission;//$vendor_commission;
                
                $order->vendor_id = $vendor_id;
                $vendor = VendorModel::where('role','3')->where('id',$vendor_id)->first();
                $order->activity_id = $vendor->activity_id ?? 0;
                $order->total_qty = $total_qty;
                $order->total_items_qty = $total_items_qty;
                $order->save();

                \App\Models\TempOrderModel::where(['temp_id' => $invoice_id])->delete();
                \App\Models\TempOrderProductsModel::where(['id' => $data->id])->delete();

                $payObj = \App\Models\PaymentReport::where('transaction_id', $invoice_id)->get()->first();
                $payObj->payment_status = 'A';
                $payObj->save();
                $users = User::find($payObj->user_id);

                $wallet_amount_used = $payObj->wallet_amount_used;
                if ($wallet_amount_used > 0) {
                    $users = User::find($payObj->user_id);
                    if ($users) {
                        $w_data = [
                            'user_id' => $users->id,
                            'wallet_amount' => $wallet_amount_used,
                            'pay_type' => 'debited',
                            'pay_method'  => 1,
                            'description' => 'Used for cart checkout',
                        ];
                        if (wallet_history($w_data)) {
                            $users->wallet_amount = $users->wallet_amount - $wallet_amount_used;
                            $users->save();
                        }
                    }
                }
                Cart::where(['user_id' => $users->id])->delete();
                Cart::where(['user_id' => $users->id])->delete();




                $order_no = config('global.sale_order_prefix').date(date('Ymd', strtotime($order->created_at))).$order->order_id;
                $title = $order_no ;//'Order Placed Successfully';
                $description = "Your order placed successfully.For more information, Please check the Order Status.";
                $notification_id = time();
                $ntype = 'order_placed';
                if (!empty($users->firebase_user_key)) {
                    $notification_data["Notifications/" . $users->firebase_user_key . "/" . $notification_id] = [
                        "title" => $title,
                        "description" => $description,
                        "notificationType" => $ntype,
                        "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                        "orderId" => (string) $order_id,
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
                            "orderId" => (string) $order_id,
                            "imageURL" => "",
                        ]
                    );
                }


                $name = $users->name ?? $users->first_name . ' ' . $users->last_name;

                // exec("php " . base_path() . "/artisan order:send_order_notification --uri=" . $order_id . " > /dev/null 2>&1 & ");
                // \Artisan::call("order:send_order_notification --uri=" . $order_id); 
                //\Artisan::call("send:send_order_email --uri=" . urlencode($users->email) . " --uri2=" . $order_id . " --uri3=" . urlencode($name) . " --uri4=" . $users->id );
                //\Artisan::call("send:send_grocery_booking_notification --uri=" . $order_id . " --uri2=" . $order_prds->id ); 

                exec("php ".base_path()."/artisan send:send_order_email --uri=" . urlencode($users->email) . " --uri2=" . $order_id . " --uri3=" . urlencode($name) . " --uri4=" . $users->id . " > /dev/null 2>&1 & ");
                exec("php ".base_path()."/artisan send:send_grocery_booking_notification --uri=" . $order_id . " --uri2=" . $order_prds->id . " > /dev/null 2>&1 & ");

                // exec("php " . base_path() . "/artisan send:send_order_email --uri=" . urlencode($users->email) . " --uri2=" . $order_id . " --uri3=" . urlencode($name) . " --uri4=" . $users->id . " > /dev/null 2>&1 & ");
            }
            return 1;
        // } catch (\Exception $e) {
        //     // dd($e->getMessage());
        //     return 0;
        // }
    }
    public function apply_coupon_fun($access_token, $coupon_code)
    {
        

        $user_id = $this->validateAccesToken($access_token);
        $coupons = Coupons::where(['coupon_code' => $coupon_code, 'coupon_status' => 1])->where('start_date', '<=', date('Y-m-d'))->where('coupon_end_date', '>=', date('Y-m-d'))->get();
        foreach ($coupons as $coupon) {
            $should_continue = false;
            $status = (string) 1;
            $message = "";
            $o_data = [];
            $errors = [];
            if ($coupon) {
                $o_data = $this->process_cart($user_id,'2');
                if ($o_data['cart_count'] == 0) {
                    $status = (string) 0;
                    $message = "No items in cart";
                    $errors = "";
                    $o_data = [];
                } else if ($o_data['grand_total'] < $coupon->minimum_amount) {
                    $status = (string) 0;
                    $message = "Minimum order amount should be " . $coupon->minimum_amount;
                    $errors = "";
                    $o_data = [];
                } else {
                    $applied_to = $coupon->applied_to;
                    $amount_type = $coupon->amount_type;
                    $amount = $coupon->coupon_amount;
                    $categories = CouponCategory::where('coupon_id', $coupon->id)->pluck('category_id')->toArray();
                    // $categories = array_column($categories, 'category_id');
                    $discount = 0;
                    foreach ($o_data['cart_items'] as $key => $val) {
                        $det = $val['product_details'];
                        if ($applied_to == 1) {
                            $p_cat_ids = ProductCategory::where('product_id',$val->product_details['product_id'])->pluck('category_id')->toArray();
                            $categories2 = Categories::whereIn('id', $p_cat_ids)->pluck('parent_id')->toArray();
                            $categories_count = count($categories);
                            $product_id = $val->product_details['product_id'];
                            // if (in_array($val->product_details['category_id'], $categories)) {
                            if ($categories_count === 0 || (count(array_intersect($categories2, $categories)))) {
                                $dis = $amount;
                                if ($amount_type == 1) {
                                    $dis = ($val->product_details['total_amount'] * $amount) / 100;
                                }
                                $det['coupon_discount'] = $dis;
                                $det['grand_total'] = $val->product_details['total_amount'] - $dis;
                                $discount += $dis;
                            } else {
                                $det['coupon_discount'] = 0;
                                $det['grand_total'] = $val->product_details['total_amount'];
                                
                                $o_data = null;
                                $status = (string) 0;
                                $message = "Invalid Coupon";
                                $errors = "";
                                $should_continue = true;
                                continue;
                            }
                        } else {
                            $pro_ids = CouponProducts::where('coupon_id', $coupon->id)->pluck('product_id')->toArray();
                            // if(request()->test){
                            //     dd($pro_ids);
                            // }
                            $ids_count = count($pro_ids);
                            if(($pro_ids && in_array($val->product_details['product_id'],$pro_ids))){
                                $dis = $amount;
                                if ($amount_type == 1) {
                                    $dis = ($val->product_details['total_amount'] * $amount) / 100;
                                }
                                $det['coupon_discount'] = $dis;
                                $det['grand_total'] = $val->product_details['total_amount'];
                                $discount = $dis;
                            }else{
                                $dis = $amount;
                                if ($amount_type == 1) {
                                    $dis = ($val->product_details['total_amount'] * $amount) / 100;
                                }
                                $det['coupon_discount'] = $dis;
                                $det['grand_total'] = $val->product_details['total_amount'];
                                $discount = 0;
                                // $discount = $dis;
                            }
                            
                        }
                        $o_data['cart_items'][$key]['product_details'] = $det;
                    }
                    if($should_continue){
                        continue;
                    }
                    
                    $settings = SettingsModel::first();
                    $tax_percentage = 0;
                    if (isset($settings->tax_percentage)) {
                        $tax_percentage = $settings->tax_percentage;
                    }
                    
                    $service_charge_percentage = 0;
                    if (isset($settings->service_charge)) {
                        $service_charge_percentage = $settings->service_charge;
                    }
                    
                    $o_data['service_charge'] = (string) ( ($o_data['cart_total'] - $discount) * $service_charge_percentage ) / 100;
                    
                    $o_data['tax_amount'] =  ( ($o_data['cart_total'] + $o_data['shipping_charge'] + $o_data['service_charge'] - $discount) * $tax_percentage ) / 100;
                    $o_data['discount'] = number_format($discount, 2, '.', '');
                    
                    $o_data['coupon_code'] = (string) $coupon_code;
                    //$o_data['grand_total'] =  number_format($o_data['grand_total'] - $discount, 2, '.', '');
                    $grand_total =  $o_data['cart_total'] + $o_data['shipping_charge'] + $o_data['service_charge'] - $discount + $o_data['tax_amount'];
                    $o_data['grand_total'] = number_format($grand_total, 2, '.', '');
                    $o_data['service_charge'] = number_format($o_data['service_charge'], 2, '.', '');
                    $o_data['tax_amount'] = number_format($o_data['tax_amount'], 2, '.', '');
                    $o_data = convert_all_elements_to_string($o_data);
                }
            } else {
                $o_data = null;
                $status = (string) 0;
                $message = "Invalid Coupon";
                $errors = "";
            }
            if($status == 1){
                return [$status, $message, $o_data];
            }
        }
        return [$status, $message, $o_data];
    }
    public function apply_ref_fun($access_token, $history)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $user_id = $this->validateAccesToken($access_token);
        if ($history) {
            $o_data = $this->process_cart($user_id,'2');
            if ($o_data['cart_count'] == 0) {
                $status = (string) 0;
                $message = "No items in cart";
                $errors = "";
                $o_data = [];
            } else {
                $con = ContactUsSetting::first();
                $applied_to = 0;
                $amount_type = 1;
                $amount     = $con->ref_discount;
                $discount = 0;
                foreach ($o_data['cart_items'] as $key => $val) {
                    $det = $val['product_details'];
                    ////
                    $dis = $amount;
                    if ($amount_type == 1) {
                        $dis = ($val->product_details['total_amount'] * $amount) / 100;
                    }
                    $det['coupon_discount'] = $dis;
                    $det['grand_total'] = $val->product_details['total_amount'] - $dis;
                    $discount += $dis;
                    ///
                    $o_data['cart_items'][$key]['product_details'] = $det;
                }
                $settings = SettingsModel::first();
                $tax_percentage = 0;
                if (isset($settings->tax_percentage)) {
                    $tax_percentage = $settings->tax_percentage;
                }
                
                $service_charge_percentage = 0;
                if (isset($settings->service_charge)) {
                    $service_charge_percentage = $settings->service_charge;
                }
                
                $o_data['service_charge'] = (string) ( ($o_data['taxable_amount'] - $discount) * $service_charge_percentage ) / 100;
                
                $o_data['tax_amount'] =  ( ($o_data['taxable_amount'] + $o_data['shipping_charge'] + $o_data['service_charge'] - $discount) * $tax_percentage ) / 100;
                //printr($o_data);
                $o_data['discount'] = number_format($discount, 2, '.', '');
                $o_data['coupon_code'] = (string) $history->ref_code;
                $o_data['ref_code'] = (string) $history->ref_code;
                $o_data['ref_code_name'] = (string)$history->accepted_user->first_name.' '. $history->accepted_user->last_name;
                
                $grand_total =  $o_data['taxable_amount'] + $o_data['shipping_charge'] + $o_data['service_charge'] - $discount + $o_data['tax_amount'];
                
                $o_data['tax_amount'] = number_format($o_data['tax_amount'],2,'.','');
                $o_data['service_charge'] = number_format($o_data['service_charge'],2,'.','');
                $o_data['grand_total'] = number_format($grand_total,2,'.','');
                
               // $o_data['grand_total'] =  number_format($o_data['grand_total'] - $discount, 2, '.', '');
                $o_data = convert_all_elements_to_string($o_data);
            }
        } else {
            $o_data = null;
            $status = (string) 0;
            $message = "Invalid Coupon";
            $errors = "";
        }

        return [$status, $message, $o_data];
    }
    public function apply_coupon(Request $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'coupon_code' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $coupon = Coupons::where(['coupon_code' => $request->coupon_code, 'coupon_status' => 1])->where('start_date', '<=', date('Y-m-d'))->where('coupon_end_date', '>=', date('Y-m-d'))->first();
            if ($coupon) {
                $o_data = $this->process_cart($user_id,'2');
                if ($o_data['cart_count'] == 0) {
                    $status = (string) 0;
                    $message = "No items in cart";
                    $errors = $validator->messages();
                    $o_data = [];
                } else if ($o_data['grand_total'] < $coupon->minimum_amount) {
                    $status = (string) 0;
                    $message = "Minimum order amount should be " . $coupon->minimum_amount;
                    $errors = $validator->messages();
                    $o_data = [];
                } else {
                    $applied_to = $coupon->applied_to;
                    $amount_type = $coupon->amount_type;
                    $amount = $coupon->coupon_amount;
                    $categories = CouponCategory::where('coupon_id', $coupon->coupon_id)->get()->toArray();
                    $categories = array_column($categories, 'category_id');
                    $discount = 0;
                    foreach ($o_data['cart_items'] as $key => $val) {
                        $det = $val['product_details'];
                        if ($applied_to == 1) {
                            if (in_array($val->product_details['category_id'], $categories)) {
                                $dis = $amount;
                                if ($amount_type == 1) {
                                    $dis = ($val->product_details['total_amount'] * $amount) / 100;
                                }
                                $det['coupon_discount'] = $dis;
                                $det['grand_total'] = $val->product_details['total_amount'] - $dis;
                                $discount += $dis;
                            } else {
                                $det['coupon_discount'] = 0;
                                $det['grand_total'] = $val->product_details['total_amount'];
                            }
                        } else {
                            $dis = $amount;
                            if ($amount_type == 1) {
                                $dis = ($val->product_details['total_amount'] * $amount) / 100;
                            }
                            $det['coupon_discount'] = 0;
                            $det['grand_total'] = $val->product_details['total_amount'];
                            $discount = $dis;
                        }
                        $o_data['cart_items'][$key]['product_details'] = $det;
                    }
                    $o_data['discount'] = number_format($discount, 2, '.', '');
                    $o_data['grand_total'] = number_format($o_data['grand_total'] - $discount, 2, '.', '');
                }
            } else {
                $o_data = null;
                $status = (string) 0;
                $message = "Invalid Coupon";
                $errors = $validator->messages();
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
}