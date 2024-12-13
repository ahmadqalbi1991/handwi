<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\ServiceCart;
use App\Models\OrderServiceModel;
use App\Models\OrderServiceItemsModel;
use App\Models\Service;
use App\Models\SettingsModel;
use App\Models\User;
use App\Models\UserAdress;
use App\Models\Coupons;
use App\Models\CouponServices;
use App\Models\Rating;
use App\Models\ServicePrice;
use App\Models\CouponVendor;
use App\Models\CouponVendorServiceOrders;
use App\Models\ServiceCategorySelected;
use App\Models\VendorServiceTimings;
use App\Models\ContactUsSetting;
use App\Models\ServiceTimings;
use App\Models\ServiceCategories;
use Illuminate\Http\Request;
use Validator;
use Kreait\Firebase\Contract\Database;

class ServiceCartController extends Controller
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
            if ($user->active == 1) {
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
    public function add_service_to_cart(REQUEST $request)
    {
       
        $status = "0";
        $message = "";
        $o_data = array();
        $errors = (object) array();
        date_default_timezone_set('Asia/Dubai');
        $today=date('d-m-Y H:i:s', strtotime(date('Y-m-d H:i:s')));
        $start_date= date('Y-m-d H', strtotime($today. ' + 1 hours'));
        
        $request->request->add(['start_date' => $start_date]);
        
        $validator = Validator::make($request->all(), [
            'device_cart_id' => 'required',
            'text' => 'required',
            'hourly_rate' => 'required',
            'qty' => 'required',
            'service_id' => 'required|numeric|min:0|not_in:0',
           // 'service_date' => 'required|date_format:Y-m-d H|after_or_equal:start_date'
        ],[
        'service_date.after_or_equal' => 'Kindly reschedule your time!'
         ]);
           
        

        if ($validator->fails()) {
            $status = "0";
            $message = $validator->errors()->first();
            // if($message == "Kindly reschedule your time!")
            // {
            // $status = "2";    
            // }
            $errors = $validator->messages();
        } else {
            $access_token = $request->access_token;
            $user = User::where('user_access_token', $access_token)->first();
            $user_id = $user->id??0;
            $service_id = $request->service_id;

            $service = Service::where(['active'=>1,'deleted'=>0,'id'=>$service_id]);
            if ($service->count() >= 1) {

                $services = $service->first(); 
                if($services->availability == 1 && $services->open_time != null && $services->close_time != null)
                {
                    $datetotime = date('H:i', strtotime($request->service_date.":00:00"));
                   
                    if($services->open_time <= $datetotime && $services->close_time >= $datetotime)
                    { }
                    else
                    {
                        $status = "2";
                        $message = 'Booking not available in your selected time, available time - '.date('h:i A', strtotime($services->open_time)).' - '.date('h:i A', strtotime($services->close_time)); 
                        $o_data = $this->process_cart($user_id);
                        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
                    }
                }

                $cart_key = '';
                if(!empty($user_id))
                {
                $i_data['user_id']    = $user_id;
                }   
                $i_data['device_cart_id']    = $request->device_cart_id;
                $i_data['service_id'] = $service_id;
                //$i_data['booked_time'] = $request->service_date;
                $i_data['text']    = $request->text;
                $i_data['qty']    = $request->qty;
                $i_data['hourly_rate']    = $request->hourly_rate;
                //$i_data['task_description']    = $request->task_description;
                // if ($file = $request->file("doc")) {
                //     $response = image_upload($request, 'document', 'doc');
                //     if ($response['status']) {
                //         $i_data['doc'] = $response['link'];
                //     }
                // }
                $i_data['created_at']  = gmdate("Y-m-d H:i:s");

                $datamain = ServiceCart::get();
                $cart_condition = [];
                $cart_condition = [
                    "service_id" => $i_data['service_id'],
                    "device_cart_id"    => $i_data['device_cart_id'],
                ];
                if(!empty($user_id))
                {
                $cart_condition['user_id']    = $user_id;
                }

                
                if($request->remove_cart)
                {
                    $condition = [
                    "device_cart_id" => $i_data['device_cart_id'],
                    ];
                    if(!empty($user_id))
                    {
                    $condition['user_id']    = $user_id;
                    }

                    ServiceCart::where($condition)->delete();
                }
                
                //check current service category 
                $catgory = ServiceCategorySelected::where('service_id',$service_id)
                ->leftjoin('service_category','service_category.id','service_category_selected.category_id')->first()->parent_id??0;
                $catgoryop = ServiceCategorySelected::where('service_id',$service_id)
                ->first()->category_id??0;
                $activity_id = ServiceCategories::find($catgoryop)->activity_id;
               
                
                $check = ServiceCart::where('cart_service.service_id','!=',$service_id);
                if(!empty($user_id))
                {
                    $check = $check->where(["user_id" => $user_id]);
                }
                $check = $check->where(["device_cart_id" => $request->device_cart_id])
                ->leftjoin('service_category_selected','service_category_selected.service_id','=','cart_service.service_id')
                ->leftjoin('service_category','service_category.id','service_category_selected.category_id')
                ->first();
                 
                //check cart service category
                $check_val = 0;
                
                if(!empty($check))
                {
                    $cart_service_category = $check->parent_id;
                    if($cart_service_category != $catgory || $cart_service_category == 0 || $catgory == 0)
                    {
                        $check_val = 1;
                    }
                }
                
                if($check_val > 0)
                {
                        $status = "0";
                        $message = 'Your cart already contains service from another category. Do you want to replace it?'; 
                        $o_data = $this->process_cart($user_id,$request->device_cart_id);
                }
                else
                {
                  

                   
                $service_cart_data = ServiceCart::get_user_cart($cart_condition);
                if (count($service_cart_data)) {
                    $status = "";
                   
                    if (count($service_cart_data) == 1) {
                        // if ($request->file("doc")) {
                        //     $response = image_save($request, config('global.document_image_upload_dir'), 'doc', '');
                        //     if ($response['status']) {
                        //         $doc = $response['link'];
                        //     }
                        // }

                        if ($file = $request->file("doc")) {
                            $response = image_upload($request, 'document', 'doc');
                            if ($response['status']) {
                                $doc = $response['link'];
                            }
                        }

                          $service_cart_data = $service_cart_data[0];
                          if(empty($doc))
                          {
                            $doc = $service_cart_data->doc;
                          }
                          $newqty = $request->qty;
                          if($request->add_qty == 1)
                          {
                            $newqty = $service_cart_data->qty + $request->qty;
                          }
                           ServiceCart::update_cart(array(
                                "device_cart_id" => $request->device_cart_id,
                                "booked_time" => $request->service_date,
                                "text" => $request->text,
                                "hourly_rate" => $request->hourly_rate,
                                "qty" => $newqty,
                                "task_description" => $request->task_description,
                                "doc" => $doc,
                            ), ["id" => $service_cart_data->id]);

                            $status = "1";
                            $message = "Service cart updated";
                          

                    } else {
                        $status = "3";
                        $message = 'This item has multiple customizations added. Increase the correct item from the cart';
                    }

                } else {
                    
                        ServiceCart::create_cart($i_data);
                        $status = "1";
                        $message = "Service added to your cart";
                }
                
                $o_data = $this->process_cart($user_id,$request->device_cart_id);
                $status = $status ?? "1";
                }
            } else {
                $status = "0";
                $message = 'No Service exists';
            }
            
        }
        $o_data = convert_all_elements_to_string($o_data);
        if(empty($o_data['cart_count']))
        {
            $o_data['cart_items'] = [];  
        }

        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    public function add_service_to_cart_service_category_check(REQUEST $request)
    {
       
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = (object) array();
        date_default_timezone_set('Asia/Dubai');
        $today=date('d-m-Y H:i:s', strtotime(date('Y-m-d H:i:s')));
        $start_date= date('Y-m-d H', strtotime($today. ' + 1 hours'));
        
        $request->request->add(['start_date' => $start_date]);
        
        $validator = Validator::make($request->all(), [
            'device_cart_id' => 'required',
            'service_id' => 'required|numeric|min:0|not_in:0',
        ],[
        'service_date.after_or_equal' => 'Kindly reschedule your time!'
         ]);
           
        

        if ($validator->fails()) {
            $status = "0";
            $message = $validator->errors()->first();
            if($message == "Kindly reschedule your time!")
            {
            $status = "2";    
            }
            $errors = $validator->messages();
        } else {
            $access_token = $request->access_token;
            $user = User::where('user_access_token', $access_token)->first();
            $user_id = $user->id??0;
            $service_id = $request->service_id;

            $service = Service::where(['active'=>1,'deleted'=>0,'id'=>$service_id]);
            if ($service->count() >= 1) {
              //////////////////////////////////////////////////////////////
                $services = $service->first(); 
               
                if($request->remove_cart)
                {
                    $condition = [
                    "device_cart_id" => $i_data['device_cart_id'],
                    ];
                    if(!empty($user_id))
                    {
                    $condition['user_id']    = $user_id;
                    }

                    ServiceCart::where($condition)->delete();
                }
                
                //check current service category 
                //$catgory = ServiceCategorySelected::where('service_id',$service_id)->first()->category_id;
                $catgory = ServiceCategorySelected::where('service_id',$service_id)
                ->leftjoin('service_category','service_category.id','service_category_selected.category_id')->first()->parent_id??0;
                
              

                $check = ServiceCart::where('cart_service.service_id','!=',$service_id);
                if(!empty($user_id))
                {
                    $check = $check->where(["user_id" => $user_id]);
                }
                $check = $check->where(["device_cart_id" => $request->device_cart_id])
                ->leftjoin('service_category_selected','service_category_selected.service_id','=','cart_service.service_id')
                ->leftjoin('service_category','service_category.id','service_category_selected.category_id')->first();
              
                //check cart service category
                $check_val = 0;
                
                if(!empty($check))
                {
                    $cart_service_category = $check->parent_id;
                    if($cart_service_category != $catgory || $cart_service_category == 0 || $catgory == 0)
                    {
                        $check_val = 1;
                    }
                }
                /////////////////////////////////////////////////////////////////////
                
                //$check_val = $check->count();

                if($check_val > 0)
                {
                        $status = "0";
                        $message = 'Your cart already contains service from another category. Do you want to replace it?'; 
                        $o_data = $this->process_cart($user_id,$request->device_cart_id);
                }
                else
                {
                    $status = "1";
                    $message = ''; 
                    $o_data = $this->process_cart($user_id,$request->device_cart_id);
                }
            } else {
                $status = "0";
                $message = 'No Service exists';
            }
            
        }
        $o_data = convert_all_elements_to_string($o_data);
        if(empty($o_data['cart_count']))
        {
            $o_data['cart_items'] = [];  
        }

        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    public function get_service_cart(Request $request)
    {
        
        $status = "1";
        $message = "";
        $o_data = (object) array();
        $errors = [];
        $validator = Validator::make($request->all(), [
            'device_cart_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $access_token = $request->access_token;
            $user = User::where('user_access_token', $access_token)->first();
            $user_id = $user->id??0;
            $o_data = $this->process_cart($user_id,$request->device_cart_id);
        }
        $o_data = convert_all_elements_to_string($o_data);
        
        if(empty($o_data['cart_count']))
        {
            $o_data['cart_items'] = [];  
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function service_checkout(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $default_address = (object)[];

       


        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'device_cart_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            if($message == "Kindly reschedule your time!")
            {
            $status = "2";    
            }
            $errors = $validator->messages();
        } else {
            
            $user_id = $this->validateAccesToken($request->access_token);
            $o_data = $this->process_cart($user_id,$request->device_cart_id,$request->latitude,$request->longitude);
            $o_data = convert_all_elements_to_string($o_data);


            $discount = 0;

            $coupon = [];
            $coupon_id = 0;
            if ($request->coupon_code) {
            
                list($status,$message,$result) = $this->apply_service_coupon_fun($request->access_token,$request->device_cart_id,$request->coupon_code,$request->latitude,$request->longitude);
                
                if($status == "1")
                {
                $o_data  = convert_all_elements_to_string($result); 
                if(empty($o_data['cart_count']))
                {
                $o_data['cart_items'] = [];  
                }
                $o_data['coupon_code'] = (string) $request->coupon_code; 
                $cart_total = (string) ($o_data['cart_total']);

                $settings = SettingsModel::first();
                $tax_percentage = 0;
                if (isset($settings->tax_percentage)) {
                    $tax_percentage = $settings->tax_percentage;
                }
                
                $service_charge_percentage = 0;
                if (isset($settings->tax_percentage)) {
                    $service_charge_percentage = $settings->service_charge;
                }
                $o_data['service_charge'] = (string) (( ($cart_total - $o_data['discount']) * $service_charge_percentage)/100);
                $tax_amount = ( ( $cart_total + $o_data['service_charge']  - $o_data['discount'] ) * $tax_percentage) / 100;
                $o_data['tax_amount'] = (string) number_format($tax_amount, 2, '.', '');
                $o_data['grand_total'] = (string) number_format($cart_total - $o_data['discount'] + $tax_amount + $o_data['service_charge'], 2, '.', '');

                }
                $message = $message;

            }
            else
            {
                
                $history = \App\Models\RefHistory::with('accepted_user')->where([['sender_id',$user_id],['status',1]])->orderBy('id','asc')->first();

                if ($history) {
                    list($status, $message, $result) = $this->apply_ref_fun($request->access_token,$request->device_cart_id, $history,$request->latitude,$request->longitude);
    
                    if ($status == 1) {
                        $o_data  = convert_all_elements_to_string($result);
                    }
                    $message = $message;
                }

              $cart_total = (string) ($o_data['cart_total']);

              $settings = SettingsModel::first();
              $tax_percentage = 0;
              if (isset($settings->tax_percentage)) {
               $tax_percentage = $settings->tax_percentage;
              }
                $service_charge_percentage = 0;
                if (isset($settings->tax_percentage)) {
                    $service_charge_percentage = $settings->service_charge;
                }
                $service_charge = (string) (( ($cart_total - $o_data['discount']) * $service_charge_percentage) / 100);
              $tax_amount = ( ( $cart_total - $o_data['discount'] + $service_charge ) * $tax_percentage) / 100;
              $o_data['service_charge'] = $service_charge;
              $o_data['tax_amount'] = (string) number_format($tax_amount, 2, '.', '');
              $o_data['grand_total'] = (string) number_format($cart_total - $o_data['discount'] + $tax_amount + $service_charge, 2, '.', ''); // (string) ($cart_total - $o_data['discount'] + $tax_amount + $service_charge);
            }
            
            
              

            $o_data['default_address'] = $default_address;
            $default_address = convert_all_elements_to_string(UserAdress::get_user_default_address($user_id)->toArray());
            if($default_address)
            {
                $o_data['default_address'] = $default_address;
            }
            
            $o_data['address_list'] = convert_all_elements_to_string(UserAdress::get_address_list($user_id));
        }
        if(empty($o_data['cart_count']))
        {
            $o_data['cart_items'] = [];  
        }
       

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function update_cart(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'cart_id' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);

            $cart_condition = [
                "id" => $request->cart_id,
                "user_id" => $user_id,
            ];
            if ($request->type == "add") {
                if (Cart::where($cart_condition)->increment('quantity', 1)) {
                    $message = "Cart updated";
                } else {
                    $message = "Invalid data passed";
                }
            } else {
                if (Cart::where($cart_condition)->decrement('quantity', 1)) {
                    $message = "Cart updated";
                } else {
                    $message = "Invalid data passed";
                }
            }

            $o_data = $this->process_cart($user_id);
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function process_cart($user_id,$device_cart_id,$latitude='',$longitude='')
    {
        $cart_total = 0;
        $tax_amount = 0;
        $grand_total =0;
        $cart_count  = 0;
        $cart_items = [];

        $service_radius = 50;

        $settings = SettingsModel::first();
        if (isset($settings->service_radius)) {
            $service_radius = $settings->service_radius;
        }
    
        if(!empty($user_id))
        {
        $where['cart_service.user_id'] = $user_id;
        }
        $where['cart_service.device_cart_id'] = $device_cart_id;
        $where['service.deleted'] = 0;
        $where['service.active'] = 1;

        

        $servicecart = ServiceCart::select('cart_service.id','service.id as service_id','service.name','service_price','cart_service.booked_time','users.name as customer_name','users.id as customer_id','service.description','service.image','cart_service.text','cart_service.hourly_rate','cart_service.task_description','cart_service.doc','qty','service.service_price_type','booked_time')
        ->join('users','users.id','=','cart_service.user_id')
        ->where($where)->join('service', 'service.id', 'cart_service.service_id')->get();
        
        $service = [];

        $cart_total = 0;
        $is_service_for_all_available = "1";
        foreach ($servicecart as $key => $val) {
                $amt = $val->hourly_rate * $val->qty;
            //    if(!empty($val->city_id))
            //     {
            //     $pricecity = ServicePrice::where(['service_id'=>$val->service_id,'city'=>$val->city_id])->get()->first(); 
            //     }
            //     if(!empty($pricecity))
            //     {
            //     $servicecart[$key]->service_price  = $pricecity->service_price;
            //     $amt = $pricecity->service_price;
            //     }

                
                
                $service_total_amount = $amt;
                $cart_total += $service_total_amount;
                
                $servicecart[$key]->image = get_uploaded_image_url($val->image,'service_image_upload_dir');
                $servicecart[$key]->description = (string) $val->description;
                $where2['service_id'] = $val->service_id;
                $where2['type']   = 3;
                $servicecart[$key]->rating = (string) number_format(Rating::avg_rating($where2),1,'.', '');
                $servicecart[$key]->rating_count = (string) count(Rating::rating_list($where2));
                $servicecart[$key]->coupon_discount = 0;
                $servicecart[$key]->is_serviceable = "1";
                $servicecart[$key]->is_serviceable_text = "";

                if($latitude != '' && $longitude != ''){
                    
                    $usersListQry = \DB::table('vendor_services')->where('vendor_services.service_id','=',$val->service_id)
                    ->join('users','users.id','=','vendor_services.vendor_id')
                    ->join('vendor_locations','vendor_locations.user_id','=','users.id')
                    ->whereNotNull('vendor_locations.longitude');

                    $distance =
                        "6371 * acos (
                        cos ( radians( CAST (vendor_locations.latitude AS double precision) ) )
                        * cos( radians( CAST ({$latitude} AS double precision) ) )
                        * cos( radians( CAST ({$longitude} AS double precision) ) - radians( CAST (vendor_locations.longitude AS double precision) ) )
                        + sin ( radians( CAST (vendor_locations.latitude AS double precision) ) )
                        * sin( radians ( CAST ({$latitude} AS double precision) ) )
                    )";
                    $usersListQry->selectRaw("$distance as distance")->orderBy('distance','asc');
                    $usersListQry->whereRaw("$distance<=$service_radius");
                    $usersListQry = $usersListQry->where(['users.deleted'=>0])->get();
                    if($usersListQry->count() == 0){
                        $servicecart[$key]->is_serviceable = "0";
                        $servicecart[$key]->is_serviceable_text = "Not available in your area";
                        $is_service_for_all_available = "0";
                    }
                }
                
        }
        $settings = SettingsModel::first();
        $tax_percentage = 0;
        if (isset($settings->tax_percentage)) {
            $tax_percentage = $settings->tax_percentage;
        }
        $service_charge = 0;
        if(!empty($cart_total))
        {
            $service_charge = $settings->service_charge;
        }
        
        $service_charge = ($cart_total * $service_charge) / 100;
        
        $crt_sub=($cart_total+$service_charge);
        $tax_amount = ( $crt_sub * $tax_percentage) / 100;
        $grand_total = $tax_amount + $cart_total + $service_charge;
        $cart_count = count($servicecart);
        $discount   = 0;
        $cart_items = $servicecart;

        $booked_time = $servicecart[0]['booked_time']??'';
        $task_description = $servicecart[0]->task_description??'';
        $doc = $servicecart[0]->doc??'';
        if($doc)
        {
        $doc = asset($doc);
        }
        

        return ['cart_total' => number_format($cart_total, 2,'.', ''), 'tax_amount' => number_format($tax_amount, 2,'.', ''),'service_charge' => number_format($service_charge, 2,'.', ''),'discount' => number_format($discount, 2,'.', ''), 'grand_total' => number_format($grand_total, 2,'.', ''), 'cart_count' => $cart_count,'coupon_code'=> '',
         'cart_items' => $cart_items,'is_service_for_all_available'=>$is_service_for_all_available,
         'booked_time'=>$booked_time,'task_description'=>$task_description,'doc'=>$doc];
    }
    public function delete_service_cart(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'device_cart_id' => 'required',
            'cart_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $access_token = $request->access_token;
            $user = User::where('user_access_token', $access_token)->first();
            $user_id = $user->id??0;
            $cart_condition = [
                "id" => $request->cart_id,
                "device_cart_id" => $request->device_cart_id,
            ];
            if(!empty($user_id))
            {
                $cart_condition['user_id'] = $user_id; 
            }

            if (ServiceCart::where($cart_condition)->delete()) {
                $message = "Cart item removed";
            }

            $o_data = $this->process_cart($user_id,$request->device_cart_id);
        }
        $o_data = convert_all_elements_to_string($o_data);
        if(empty($o_data['cart_count']))
        {
            $o_data['cart_items'] = [];  
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function clear_service_cart(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'device_cart_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $access_token = $request->access_token;
            $user = User::where('user_access_token', $access_token)->first();
            $user_id = $user->id??0;
            $cart_condition = [
                "device_cart_id" => $request->device_cart_id,
            ];
            if(!empty($user_id))
            {
                $cart_condition['user_id'] = $user_id; 
            }
            if (ServiceCart::where($cart_condition)->delete()) {
                $message = "Cart cleared";
            }
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function service_payment_init(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        date_default_timezone_set('Asia/Dubai');
        $today=date('d-m-Y H:i:s', strtotime(date('Y-m-d H:i:s')));
        $start_date= date('Y-m-d H', strtotime($today. ' + 1 hours'));
        
        $request->request->add(['start_date' => $start_date]);

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'payment_type' => 'required|integer|min:1',
            'address_id' => 'required|integer|min:1',
            'device_cart_id' => 'required',
           // 'service_date' => 'required|date_format:Y-m-d H|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = $validator->errors()->first();
            if($message == "Kindly reschedule your time!")
            {
            $status = "2";    
            }
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $cart_details = $this->process_cart($user_id,$request->device_cart_id,$request->latitude,$request->longitude);
            $coupon_vendor = 0;
            if($cart_details['is_service_for_all_available'] == "1"){
                if(isset($cart_details['cart_items']))
                {
                date_default_timezone_set('Asia/Dubai');
                $today=date('d-m-Y H:i:s', strtotime(date('Y-m-d H:i:s')));
                $start_date= date('Y-m-d H', strtotime($today. ' + 1 hours'));

                $cartitems = $cart_details['cart_items'];
                foreach ($cartitems as $key => $value) {
                    if($value->booked_time < $start_date)
                    {
                        return response()->json(['status' => "2", 'message' => "Kindly reschedule your time!", 'errors' => (object) $errors, 'oData' => (object) $o_data]);
                    }
                }

               }

                $discount = 0;

                $coupons = [];
                $coupon_id = 0;
                if ($request->coupon_code) {
                    $coupons = Coupons::where(['coupon_code' => $request->coupon_code, 'coupon_status' => 1])->where('start_date', '<=', date('Y-m-d'))->where('coupon_end_date', '>=', date('Y-m-d'))->get();
                }
                $history = \App\Models\RefHistory::with('accepted_user')->where([['sender_id',$user_id],['status',1]])->orderBy('id','asc')->first();
                $coupon_exist = false;
            foreach($coupons as $coupon)
            {
                if ($coupon) {
                    $coupon_used = OrderServiceModel::where('coupon_id',$coupon->id)->count();
                    $coupon_used_user = OrderServiceModel::where(['coupon_id'=>$coupon->id,'user_id'=>$user_id])->count();
                    if ($cart_details['grand_total'] < $coupon->minimum_amount) {
                    } else {
                        if($coupon_used < $coupon->coupon_usage_percoupon || $coupon->coupon_usage_percoupon == 0)
                        {
                            if($coupon_used_user < $coupon->coupon_usage_peruser || $coupon->coupon_usage_peruser == 0)
                            {
                                $applied_to = $coupon->applied_to;
                                $amount_type = $coupon->amount_type;
                                $amount = $coupon->coupon_amount;
                                
                                $services = CouponServices::where('coupon_id', $coupon->id)->get()->toArray();
                                $services = array_column($services, 'service_id');
                                $discount = 0;
                                $cart_count_find = count($cart_details['cart_items']);
                                foreach ($cart_details['cart_items'] as $key => $val) {
                                    $det = $val;
                                    if ($applied_to == 3) {
                                        if (empty($services) || in_array($val['service_id'], $services)) {
                                            $dis = $amount;
                                            $det['coupon_discount'] = number_format($dis/$cart_count_find, 2, '.', '');
                                            $det['discount'] = number_format($dis/$cart_count_find, 2, '.', '');
                                            if ($amount_type == 1) {
                                                $dis = (($val['hourly_rate'] * $val['qty']) * $amount) / 100;
                                                $det['coupon_discount'] = $dis;
                                                $det['discount'] = $dis;
                                            }
                                            else
                                            {
                                                $dis = number_format($dis/$cart_count_find, 2, '.', '');
                                            }
                                            
                                            
                                            $discount += $dis;
                                            $coupon_vendors = CouponVendor::where('coupon_id',$coupon->id)->get();
                                        } else {
                                            $det['coupon_discount'] = 0;
                                            
                                        }
                                    } else {
                                        // $dis = $amount;
                                        // if ($amount_type == 1) {
                                        //     $dis = ($val['service_price'] * $amount) / 100;
                                        // }
                                        // $det['coupon_discount'] = $dis;
                                        // $det['grand_total'] = $val['service_price'];
                                        // $discount = $dis;
                                        $det['coupon_discount'] = 0;
                                        $coupon_vendors = CouponVendor::where('coupon_id',$coupon->id)->get();
                                    }
                                    $cart_details['cart_items'][$key] = $det;
                                }
                                $cart_details['discount'] = $discount;
                                $cart_details['grand_total'] = $cart_details['grand_total'] - $discount;
                                $cart_details['coupon_id'] = $coupon->id??0;
        
                                //
                                $cart_total = $cart_details['cart_total'];
        
                                $settings = SettingsModel::first();
                                $tax_percentage = 0;
                                if (isset($settings->tax_percentage)) {
                                    $tax_percentage = $settings->tax_percentage;
                                }
                                $service_charge_percentage = 0;
                                if (isset($settings->tax_percentage)) {
                                    $service_charge_percentage = $settings->service_charge;
                                }
                                $cart_details['service_charge'] = ( ($cart_total - $cart_details['discount']) * $service_charge_percentage) / 100;
                                $tax_amount = ( ($cart_total+$cart_details['service_charge'] - $cart_details['discount']) * $tax_percentage) / 100;
                                $cart_details['tax_amount'] = $tax_amount;
                                $cart_details['grand_total'] = ($cart_total - $cart_details['discount'] + $tax_amount + $cart_details['service_charge']);
                                
                                
                                if($discount !== 0){
                                    $coupon_exist = true;
                                    break;
                                }
                            }
                        }
                        
                        
                    }
                }

            }
            if(!$coupon_exist){
                $history = \App\Models\RefHistory::with('accepted_user')->where([['sender_id',$user_id],['status',1]])->orderBy('id','asc')->first();

                    if ($history) {
                    
                        list($status, $message, $result) = $this->apply_ref_fun($request->access_token,$request->device_cart_id, $history);
        
                        if ($status == 1) {
                            $cart_details  = $result;
                        }
                        $message = $message;
                    }

                $cart_total = (string) ($cart_details['cart_total']);

                $settings = SettingsModel::first(); 
                $tax_percentage = 0;
                if (isset($settings->tax_percentage)) {
                $tax_percentage = $settings->tax_percentage;
                }
                $service_charge_percentage = 0;
                if (isset($settings->tax_percentage)) {
                $service_charge_percentage = $settings->service_charge;
                }
                $cart_details['service_charge'] = ( ($cart_total - $cart_details['discount']) * $service_charge_percentage) / 100;
                $tax_amount = ( ($cart_total+ $cart_details['service_charge'] - $cart_details['discount']) * $tax_percentage) / 100;
                $cart_details['tax_amount'] = (string) $tax_amount;
                $cart_details['grand_total'] = (string) ($cart_total  - $cart_details['discount'] + $tax_amount + $cart_details['service_charge']);
            }

                $amount_to_pay = $cart_details['grand_total'];

                if ((int) $amount_to_pay == 0) {
                    $message = "Your cart is empty";
                } else {
                    $check = \App\Models\TempServiceModel::where(['user_id' => $user_id])->first();
                    if ($check) {
                        \App\Models\TempServiceModel::where(['user_id' => $user_id])->delete();
                        \App\Models\TempServiceModelItems::where(['order_id' => $check->id])->delete();
                    }
                    $temp_id = $user_id . uniqid() . time();
                
                    $temp_order = new \App\Models\TempServiceModel();
                    $temp_order->user_id = $user_id;
                    $temp_order->user_latitude = $request->latitude;
                    $temp_order->user_longitude = $request->longitude;
                    $temp_order->address_id = $request->address_id;
                    $temp_order->total = $cart_details['cart_total'];
                    $temp_order->vat = $cart_details['tax_amount'];
                    $temp_order->discount = $cart_details['discount'];
                    if($history){
                        $temp_order->ref_history_id = $history->id;
                        $temp_order->ref_code = $history->ref_code;
                    }
                    $temp_order->coupon_id = $cart_details['coupon_id']??0;
                    $temp_order->grand_total = $cart_details['grand_total'];
                    $temp_order->payment_mode = $request->payment_type;
                    $temp_order->booking_date = '';
                    $temp_order->task_description = '';
                   
                    $temp_order->service_charge = $cart_details['service_charge']??0;
                    $temp_order->temp_id = $temp_id;
                    $temp_order->save();

                    $temp_order_id = $temp_order->id;
                    foreach ($cart_details['cart_items'] as $val) {

                        $temp_order_prds = new \App\Models\TempServiceModelItems();
                        $temp_order_prds->order_id = $temp_order_id;

                        $temp_order_prds->service_id = $val->service_id;
                        $temp_order_prds->price = $val['hourly_rate'];
                        $temp_order_prds->discount = $val['discount']??0;
                        $temp_order_prds->total = $val['service_price'];
                        $temp_order_prds->admin_commission = 0;
                        $temp_order_prds->vendor_commission = 0;
                        $temp_order_prds->text = $val['text'];
                        $temp_order_prds->hourly_rate = $val['hourly_rate'];
                        $temp_order_prds->task_description = $val['task_description'];
                        $temp_order_prds->doc = $val['doc'];
                        $temp_order_prds->qty = $val['qty'];
                        $temp_order_prds->booking_date = $val['booked_time'].":00:00";
                        $temp_order_prds->save();
                    }
                    $wallet_amount_used = 0;
                    if ($request->payment_type == 2 || $request->payment_type == 3 || $request->payment_type == 4 || $request->payment_type == 5) {
                        $o_data = $this->payment_init_stripe($payment_token = $temp_id, $invoice_id = $temp_id, $amount_to_pay, $wallet_amount_used, $user_id, $request->address_id,$cart_details['tax_amount']);
                        $status = "1";
                        $message = "";
                    }else{
                        $user = User::where(['id' => $user_id])->get()->first();
                        if($user->wallet_amount<$amount_to_pay){
                            $status = "0";
                            $message = "Insufficient wallet balance";
                        }else{
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
                                $status = "1";
                                $message = "Order Placed Successfully";
                                $orderdata = OrderServiceModel::where('invoice_id',$temp_id)->first();
                                $o_data['invoice_id']  = $temp_id;
                                $o_data['order_id']    = (string) $orderdata->order_id;
                                $o_data['order_no']    = config('global.sale_order_prefix')."-SER".date(date('Ymd', strtotime($orderdata->created_at))).$orderdata->order_id;
                                OrderServiceModel::where('order_id',$orderdata->order_id)->update(['order_no'=>$o_data['order_no']]);
                            } else {
                                $status = "0";
                                $message = "Soemthing went wrong";
                            }
                        }
                    }
                }
            }else{
                $status = "5";
                $message = "Some of the services are not available in your area";
            }

        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object) $o_data]);
    }
    public function payment_init_stripe($payment_token, $invoice_id, $amount_to_pay, $payment_by_wallet, $user_id, $address_id,$tax_amount=0)
    {
        $user = User::where(['id' => $user_id])->get()->first();
        $response = array();
        $data['client_reference_id'] = $invoice_id;
        $data['product'] = "HEALTHYWEALTHY";
        $data['description'] = "Product Purchase";
        $data['quantity'] = 1;
        $data['image'] = asset('/web_assets/images/logo-talents.png');
        $data['success_url'] = url('/') . '/payment_response/?sessio_id={CHECKOUT_SESSION_ID}&token=' . $payment_token;
        $data['cancel_url'] = url('/') . '/payment_cancel?sessio_id={CHECKOUT_SESSION_ID}&token={$payment_token}';
        $data['amount'] = $amount_to_pay * 100;
        $data['email'] = $user->email ?? 'modauk2022@gmail.com';
        $address = UserAdress::get_address_details($address_id);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $checkout_session = \Stripe\PaymentIntent::create([
            'amount' => ceil($amount_to_pay) * 100,
            'currency' => 'JOD',
            'description' => "product purchase",
            'shipping' => [
                'name' => $user->name ?? $user->first_name . ' ' . $user->last_name,
                'address' => [
                    'line1' => $address->address,
                    'city' => $address->city_name,
                    'state' => $address->state_name,
                    'country' => $address->country_name,
                ],
            ],
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
    public function service_place_order(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'payment_type' => 'invoice_id',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $res_status = $this->payment_success($request->invoice_id);
            if ($res_status === 1) {
                $status = "1";
                $message = "Order Placed Successfully";
                $orderdata = OrderServiceModel::where('invoice_id',$request->invoice_id)->first();
                $o_data['invoice_id']  = $request->invoice_id;
                $o_data['order_id']    = (string) $orderdata->order_id;
                $o_data['order_no']    = config('global.sale_order_prefix')."-SER".date(date('Ymd', strtotime($orderdata->created_at))).$orderdata->order_id;
                OrderServiceModel::where('order_id',$orderdata->order_id)->update(['order_no'=>$o_data['order_no']]);
            } else {
                $status = "0";
                $message = "Soemthing went wrong";

            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object) $o_data]);
    }
    private function payment_success($invoice_id)
    {
        try {

            $data = \App\Models\TempServiceModel::where(['temp_id' => $invoice_id])->first();
            if ($data) {

                $order = new OrderServiceModel();
                $order->invoice_id = $invoice_id;
                $order->user_id = $data->user_id;
                $order->address_id = $data->address_id;
                $order->ref_history_id = $data->ref_history_id;
                $order->user_longitude = $data->user_longitude;
                $order->user_latitude = $data->user_latitude;
                $order->ref_code = $data->ref_code;
                $order->total      = $data->total;
                $order->vat = $data->vat;
                $order->discount = $data->discount;
                $order->coupon_id = $data->coupon_id??0;
                $order->grand_total = $data->grand_total;
                $order->payment_mode = $data->payment_mode;
                $order->status = 0;//pending
                $order->booking_date = $data->booking_date;
                $order->task_description = $data->task_description;
                $order->doc = $data->doc;
                $order->admin_commission = $data->admin_commission;
                $order->vendor_commission = $data->vendor_commission;
                $order->created_at = gmdate('Y-m-d H:i:s');
                $order->service_charge = $data->service_charge;
                $order->user_id = $data->user_id;
                $order->save();

                $history = \App\Models\RefHistory::find($data->ref_history_id);
                if($history){
                    $history->status = 2;
                    $history->save();
                }
                // dd('q');
                $order_id = $order->order_id;

                if(!empty($data->coupon_id))
                {
                 $coupon_vendors = CouponVendor::where('coupon_id',$data->coupon_id)->get();
                 if(count($coupon_vendors) > 0)
                 {
                    foreach ($coupon_vendors as $key => $value) {
                       $veins  = new CouponVendorServiceOrders;
                       $veins->vendor_id = $value->vendor;
                       $veins->coupon_id = $value->coupon_id;
                       $veins->order_id = $order_id;
                       $veins->order_id = $order_id;
                       $veins->created_at = gmdate('Y-m-d H:i:s');
                       $veins->updated_at = gmdate('Y-m-d H:i:s');
                       $veins->save();

                    }
                 }   
                }
                // dd($order_id);
                $order_prds_data = \App\Models\TempServiceModelItems::where('order_id', $data->id)->get();
                $total_qty = 0;
                $total_items_qty = 0;
                $settings = SettingsModel::first();
                $tax_percentage = 0;
                if (isset($settings->tax_percentage)) {
                   $tax_percentage = $settings->tax_percentage;
                }
                foreach ($order_prds_data as $val) {
                    if(!empty($tax_percentage))
                    {
                     $vat_int = (($val->hourly_rate * $val->qty) * $tax_percentage)/100;
                    }
                    $service_data = Service::find($val->service_id);
                    $service_data->order_count = $service_data->order_count + 1;
                    $service_data->save();

                    $catgory = ServiceCategorySelected::where('service_id',$val->service_id)->first()->category_id;
                    $activity_id = ServiceCategories::find($catgory)->activity_id;

                    $total_qty += 1;
                    $total_items_qty += $val->quantity;
                    $order_prds = new OrderServiceItemsModel();
                    $order_prds->order_id = $order_id;
                    $order_prds->service_id = $val->service_id;
                    $order_prds->price = $val->price;
                    $order_prds->discount = $val->discount;
                    $order_prds->total = $val->total;
                    $order_prds->order_status = 0;
                    $order_prds->text = $val->text;
                    $order_prds->hourly_rate = $val->hourly_rate;
                    $order_prds->task_description = $val->task_description;
                    $order_prds->doc = $val->doc;
                    $order_prds->qty = $val->qty;
                    $order_prds->admin_commission = $val->admin_commission;
                    $order_prds->vendor_commission = $val->vendor_commission;
                    $order_prds->booking_date = $val->booking_date;
                    $order_prds->vat = $vat_int??0;
                    $order_prds->withdraw_status = 3;
                    if($data->payment_mode == 5)
                    {
                    $order_prds->withdraw_status = 3;
                    }
                    $order_prds->save();
                }
                
                
                $order->save();
                
                //save activity id
                $orderupdate = OrderServiceModel::find($order->order_id);
                $orderupdate->activity_id = $activity_id;
                $orderupdate->save();
                //save activity id end


                \App\Models\TempServiceModel::where(['temp_id' => $invoice_id])->delete();
                \App\Models\TempServiceModelItems::where(['id' => $data->id])->delete();

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
                ServiceCart::where(['user_id' => $users->id])->delete();
                ServiceCart::where(['user_id' => $users->id])->delete();

                $order_no = config('global.sale_order_prefix')."-SER".date(date('Ymd', strtotime($order->created_at))).$order->order_id;

                $title = $order_no;
                $description = 'Your service order has been placed successfully';
                $notification_id = time();
                $ntype = 'service_order_placed';
                if (!empty($users->firebase_user_key)) {
                    $notification_data["Notifications/" . $users->firebase_user_key . "/" . $notification_id] = [
                        "title" => $title,
                        "description" => $description,
                        "notificationType" => $ntype,
                        "createdAt" => gmdate("Y-m-d H:i:s", $notification_id),
                        "orderId" => (string) $order_id,
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
                            "orderId" => (string) $order_id,
                            "imageURL" => "",
                        ]);
                }
 
                   exec("php ".base_path()."/artisan order:send_service_notification --uri=" . $order_id." --uri2=" . $order_prds->id . " > /dev/null 2>&1 & ");
                   //exec("php ".base_path()."/artisan order:send_order_placed_email --uri=" . $order_id . " > /dev/null 2>&1 & ");
                   exec("php ".base_path()."/artisan send:send_order_placed_email --uri=" . $order_id . " > /dev/null 2>&1 & ");
                   //\Artisan::call('send:send_order_placed_email --uri='.$order_id);
                   //\Artisan::call("order:send_service_notification --uri=" . $order_id); exit;
                   //echo "php ".base_path()."/artisan order:send_service_notification --uri=" . $order_id." --uri2=" . $order_prds->id . " > /dev/null 2>&1 & ";
      
                  
            }
            return 1;
        } catch (\Exception $e) {
             printr($e->getMessage());exit;
            return 0;
        }
    }
    public function apply_service_coupon_fun($access_token,$device_cart_id,$coupon_code,$latitude='',$longitude='')
    {
        
         try{
            $user_id = $this->validateAccesToken($access_token);
            $coupons = Coupons::where(['coupon_code' => $coupon_code, 'coupon_status' => 1])->where('start_date', '<=', date('Y-m-d'))->where('coupon_end_date', '>=', date('Y-m-d'))->get();
             
            foreach ($coupons as $coupon) {
                $status = "1";
                $message = "";
                $o_data = [];
                $errors = [];

                if ($coupon) {
                    $o_data = $this->process_cart($user_id,$device_cart_id,$latitude,$longitude);
                    if ($o_data['cart_count'] == 0) {
                        $status = "0";
                        $message = "No items in cart ";
                        $errors = "";
                        $o_data = [];
                    } else if ($o_data['grand_total'] < $coupon->minimum_amount) {
                        $status = "0";
                        $message = "Minimum order amount should be " . $coupon->minimum_amount;
                        $errors = "";
                        $o_data = [];
                    } else {
                        $coupon_used = OrderServiceModel::where('coupon_id',$coupon->id)->count();
                        $coupon_used_user = OrderServiceModel::where(['coupon_id'=>$coupon->id,'user_id'=>$user_id])->count();
                        if($coupon_used < $coupon->coupon_usage_percoupon || $coupon->coupon_usage_percoupon == 0)
                        {
                          if($coupon_used_user < $coupon->coupon_usage_peruser || $coupon->coupon_usage_peruser == 0)
                            {
                                $applied_to = $coupon->applied_to;
                                $amount_type = $coupon->amount_type;
                                $amount = $coupon->coupon_amount;
                                $services = CouponServices::where('coupon_id', $coupon->id)->get()->toArray();
                                $services = array_column($services, 'service_id');
                                $discount = 0;
                                $cart_count_find = count($o_data['cart_items']);
                                foreach ($o_data['cart_items'] as $key => $val) {
                                    $det = $val;
                                    if($applied_to == 3) {
                                        $services_count = count($services);
                                        if ( $services_count === 0 || (in_array($val['service_id'], $services))) {
                                            $dis = $amount;
                                            $det['coupon_discount'] = round($dis);
                                            $det['discount'] = $dis;
                                            if ($amount_type == 1) {
                                                $dis = (($val['hourly_rate'] * $val['qty']) * $amount) / 100;
                                                $det['coupon_discount'] = round($dis);
                                                $det['discount'] = $dis;
                                            }
                                            else
                                            {
                                                $dis = number_format($dis/$cart_count_find, 2, '.', '');
                                            }
                                            
                                            
                                            $discount += $dis;
                                        } else {
                                            $message = "Invalid Coupon";
                                            $status = "0";
                                            $det['coupon_discount'] = 0;
                                            
                                        }
                                    } else {
                                        // $dis = $amount;
                                        // if ($amount_type == 1) {
                                        //     $dis = ($val['service_price'] * $amount) / 100;
                                        // }
                                        // $det['coupon_discount'] = round($dis);
                                        // $det['grand_total'] = $val['service_price'];
                                        // $discount = $dis;
                                        $message = "Invalid Coupon";
                                        $status = "0";
                                        $det['coupon_discount'] = 0;
                                    }
                                    $o_data['cart_items'][$key] = $det;
                                }
                                $o_data['discount'] = number_format($discount,2,'.', '');
                                $o_data['coupon_code'] = (string) $coupon_code;
                                $o_data['grand_total'] = number_format($o_data['grand_total'] - $discount,2,'.', '');
                            }
                     else
                     {
                        $o_data = null;
                        $status = "0";
                        //$message = "Usage limit per Coupon exceeded";
                        $message = "Invalid Coupon";
                        $errors = "";
                     }
                    }
                    else
                    {
                        $o_data = null;
                        $status = "0";
                        //$message = "Coupon Usage limit per User exceeded";
                        $message = "Invalid Coupon";
                        $errors = "";
                    }
                    }
                     
                } else {
                    $o_data = null;
                    $status = "0";
                    $message = "Invalid Coupon";
                    $errors = "";
                    
                }
                if($status == "1"){
                    return [$status,$message,$o_data];
                }
            }
        $status = "0";
        $o_data = null;
        $message = "Invalid Coupon";
        
        return [$status,$message,$o_data];
         } catch(\Exception $e){
             $o_data = null;
             $status = "0";
            $message = "Invalid Coupon";
            return [$status,$message,$o_data];
         }
    }
    public function apply_service_coupon(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'coupon_code' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $coupon = Coupons::where(['coupon_code' => $request->coupon_code, 'coupon_status' => 1])->where('start_date', '<=', date('Y-m-d'))->where('coupon_end_date', '>=', date('Y-m-d'))->first();
            if ($coupon) {
                $o_data = $this->process_cart($user_id);
                if ($o_data['cart_count'] == 0) {
                    $status = "0";
                    $message = "No items in cart";
                    $errors = $validator->messages();
                    $o_data = [];
                } else if ($o_data['grand_total'] < $coupon->minimum_amount) {
                    $status = "0";
                    $message = "Minimum order amount should be " . $coupon->minimum_amount;
                    $errors = $validator->messages();
                    $o_data = [];
                } else {
                    $applied_to = $coupon->applied_to;
                    $amount_type = $coupon->amount_type;
                    $amount = $coupon->coupon_amount;
                    $services = CouponServices::where('coupon_id', $coupon->id)->get()->toArray();
                    $services = array_column($services, 'service_id');
                    $discount = 0;
                    foreach ($o_data['cart_items'] as $key => $val) {
                        $det = $val;
                        if ($applied_to == 3) {
                            if (in_array($val['service_id'], $services)) {
                                $dis = $amount;
                                if ($amount_type == 1) {
                                    $dis = ($val['service_price'] * $amount) / 100;
                                }
                                $det['coupon_discount'] = $dis;
                                $discount += $dis;
                            } else {
                                $message = "Invalid Coupon6";
                                $status = "0";
                                $det['coupon_discount'] = 0;
                                
                            }
                        } else {
                            //$dis = $amount;
                            if ($amount_type == 1) {
                               // $dis = ($val['service_price'] * $amount) / 100;
                            }
                            //$det['coupon_discount'] = $dis;
                            //$det['grand_total'] = $val['service_price'];
                            //$discount = $dis;
                            $message = "Invalid Coupon7";
                            $status = "0";
                            $det['coupon_discount'] = 0;
                        }
                        $o_data['cart_items'][$key] = $det;
                    }
                    $o_data['discount'] = number_format($discount,2,'.', '');
                    $o_data['coupon_code'] = (string) $request->coupon_code;
                    $o_data['grand_total'] = number_format($o_data['grand_total'] - $discount,2,'.', '');
                }
            } else {
                $o_data = null;
                $status = "0";
                $message = "Invalid Coupon8";
                $errors = $validator->messages();
            }

        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }

    public function timeslote(Request $request)
    {
        
        $status = "1";
        $message = "";
        $o_data = array();
        $errors = [];
        $hours = [];
        $available_time = [];
        $validator = Validator::make($request->all(), [
            'date' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $today = strtolower(date('D',strtotime($request->date)));
            $now = time_to_uae(date($request->date.' H:i:s'), "H:i:s");

            $timings = VendorServiceTimings::where('vendor',1)->first();
            if(empty($timings))
            {
               
            }
            else
            {

                $start_time = '';
                $end_time   = '';
                if($today == 'sun' && $timings->sunday == 1){
                    $start_time = $timings->sun_from;
                    $end_time   = $timings->sun_to;
                }
                if($today == 'mon' && $timings->monday == 1){
                    $start_time = $timings->mon_from;
                    $end_time   = $timings->mon_to;
                }
                if($today == 'tue' && $timings->tuesday == 1){
                    $start_time = $timings->tues_from;
                    $end_time   = $timings->tues_to;
                }
                if($today == 'wed' && $timings->wednesday == 1){
                    $start_time = $timings->wed_from;
                    $end_time   = $timings->wed_to;
                }
                if($today == 'thu' && $timings->thursday == 1){
                    $start_time = $timings->thurs_from;
                    $end_time   = $timings->thurs_to;
                }
                if($today == 'fri' && $timings->friday == 1){
                    $start_time = $timings->fri_from;
                    $end_time   = $timings->fri_to;
                }
                if($today == 'sat' && $timings->saturday == 1){
                    $start_time = $timings->sat_from;
                    $end_time   = $timings->sat_to;
                }
                if(empty($start_time) && empty($end_time))
                {
                    
                }
                else
                {
                    $start_time = date("H:i:s", strtotime($start_time));
                    $now = date("H:i:s", strtotime($now));
                    $end_time = date("H:i:s", strtotime($end_time));
                
                    $start = $now;
                    if($request->date == date('Y-m-d'))
                    {
                        $start = date("H:i:s", strtotime("+2 hour", strtotime($now)));
                    }
                    $end = $now;
                    if($request->date.' '.$start_time > date('Y-m-d').' '.$now)
                    {
                        $start = $start_time;
                    }
                    if(date('Y-m-d').' '.$now < $request->date.' '.$end_time)
                    {
                        $end = $end_time;
                    }
                
                    if($start != $end)
                    {
                        $startTime = date("H", strtotime($start)).":00:00";
                        $endTime = date("H", strtotime($end)).":00:00";;
                        
                        $interval = 1; // 1 hour interval
                        
                        $currentHour = strtotime($startTime); // Convert strings to timestamps
                        
                        // Loop through each hour within the interval
                        while ($currentHour <= strtotime($endTime)) {
                        // Format the timestamp to "H:i" format
                        $formattedHour = date("h:i A", $currentHour);
                        
                        // Add the hour to an array
                        $hours[] = array('time'=>$formattedHour);
                        
                        // Increment the timestamp by 1 hour
                        $currentHour += 3600 * $interval; // 3600 seconds in 1 hour
                        }
                        $available_time = $hours;
                    }
                    
                
                }

    
            }
       
           
        }

        $o_data['available_time'] = $available_time;
       

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }

    public function timeslote_new(Request $request)
    {
        
        $status = "1";
        $message = "";
        $o_data = array();
        $errors = [];
        $hours = [];
        $available_time = [];
        $validator = Validator::make($request->all(), [
            'date' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $today = strtolower(date('l',strtotime($request->date)));
            $now_date = time_to_uae(date($request->date.' H:i:s'), "Y-m-d");
            $now = time_to_uae(date($request->date.' H:i:s'), "H:i:s");
            $start = "09:00 AM";
            $end = "09:00 PM";
            if(date('Y-m-d') == $now_date){
                $start=(date("H", strtotime($now))+2).":00:00";
            }
            $time_list = [];
            if($start != $end)
            {
                $startTime = date("H", strtotime($start)).":00:00";
                $endTime = date("H", strtotime($end)).":00:00";;
                
                $interval = 2; // 1 hour interval
                
                $currentHour = strtotime($startTime); // Convert strings to timestamps
                
                // Loop through each hour within the interval
                while ($currentHour <= strtotime($endTime)) {
                    // Format the timestamp to "H:i" format
                    $formattedHour = date("h:i A", $currentHour);
                    
                    // Add the hour to an array
                    $hours[] = array('time'=>$formattedHour);
                    $time_list[]=$formattedHour;
                    
                    // Increment the timestamp by 1 hour
                    $currentHour += 3600 * $interval; // 3600 seconds in 1 hour
                }
                $available_time = $hours;
            }
       
           
        }

        $data = ServiceTimings::where(['vendor_id'=>1,'day'=>$today])->get();
        $time_lsit_from = $time_list_to=$time_list;
        array_pop($time_lsit_from);
        array_shift($time_list_to);
        $full_list=[];
        foreach($time_lsit_from as $i=>$k){
            $is_availble="0";
            foreach($data as $item){
                if(strtotime($k) >= strtotime($item->time_from) &&  strtotime($k) <= strtotime($item->time_to)){
                    $is_availble= "1";
                }
            }
            $full_list[]=[
                'slot' => $k."-".$time_list_to[$i],
                'slot_from'=>$k,
                'slot_to'=>$time_list_to[$i],
                'is_available'=>$is_availble
            ];
        }
        $o_data['available_slots'] = $full_list;
        
       

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function apply_ref_fun($access_token, $device_cart_id, $history,$latitude='',$longitude='')
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $user_id = $this->validateAccesToken($access_token);
        if ($history) {
            $o_data = $this->process_cart($user_id,$device_cart_id,$latitude,$longitude);
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
                $cart_count_find = count($o_data['cart_items']);
                foreach ($o_data['cart_items'] as $key => $val) {
                    $det = $val;
                  
                    ////
                    $dis = $amount;
                    $det['discount'] = number_format($dis/$cart_count_find, 2, '.', '');
                    if ($amount_type == 1) {
                        $dis = ($val->hourly_rate * $amount) / 100;
                        $det['discount'] = $dis;
                    }
                    //$det['coupon_discount'] = $dis;
                    $det['discount'] = $dis;
                    $discount += $dis;
                    ///
                    $o_data['cart_items'][$key] = $det;
                }
                $o_data['discount'] = number_format($discount, 2, '.', '');
                $o_data['coupon_code'] = (string) ($history->ref_code??'');
                $o_data['ref_code'] = (string) ($history->ref_code??'');
                $o_data['ref_code_name'] = (string) ($history->accepted_user->first_name??'').' '. ($history->accepted_user->last_name??'');
                $o_data['grand_total'] =  number_format($o_data['grand_total'] - $discount, 2, '.', '');
            }
         } else {
            $o_data = null;
            $status = (string) 0;
            $message = "Invalid Coupon9";
            $errors = "";
        }

        return [$status, $message, $o_data];
    }

    public function add_time_slote(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $default_address = (object)[];

        date_default_timezone_set('Asia/Dubai');
        $today=date('d-m-Y H:i:s', strtotime(date('Y-m-d H:i:s')));
        $start_date= date('Y-m-d H', strtotime($today. ' + 1 hours'));
        
        $request->request->add(['start_date' => $start_date]);


        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'device_cart_id' => 'required',
            'service_date' => 'required|date_format:Y-m-d H|after_or_equal:start_date'
        ],[
            'service_date.after_or_equal' => 'Kindly reschedule your time!'
             ]);

        if ($validator->fails()) {
            $status = "0";
            if($message == "Kindly reschedule your time!")
            {
            $status = "2";    
            }
            $errors = $validator->messages();
        } else {
            
            $user_id = $this->validateAccesToken($request->access_token);
            $o_data = $this->process_cart($user_id,$request->device_cart_id,$request->latitude,$request->longitude);
            $o_data = convert_all_elements_to_string($o_data);

            if(isset($o_data['cart_items']))
                {
                    date_default_timezone_set('Asia/Dubai');
                    $today=date('d-m-Y H:i:s', strtotime(date('Y-m-d H:i:s')));
                    $start_date= date('Y-m-d H', strtotime($today. ' + 1 hours'));

                    $cartitems = $o_data['cart_items'];
                    foreach ($cartitems as $key => $value) {
                        if($request->service_date < $start_date)
                        {
                            return response()->json(['status' => "2", 'message' => "Kindly reschedule your time!", 'errors' => (object) $errors, 'oData' => (object) $o_data]);
                        }
                    }
                     //update cart

                     $condition = [
                        "device_cart_id" => $request->device_cart_id,
                        ];
                        if(!empty($user_id))
                        {
                        $condition['user_id']    = $user_id;
                        }
                    $datains['task_description'] = $request->task_description;
                    if ($file = $request->file("doc")) {
                        $response = image_upload($request, 'document', 'doc');
                        if ($response['status']) {
                            $datains['doc'] = $response['link'];
                        }
                    }
                    $datains['booked_time'] = $request->service_date;

                    
    
                    ServiceCart::where($condition)->update($datains);

                    //update cart END
                }
                $o_data = $this->process_cart($user_id,$request->device_cart_id,$request->latitude,$request->longitude);
        }
        

        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' =>(object) $o_data]);
    }
}
