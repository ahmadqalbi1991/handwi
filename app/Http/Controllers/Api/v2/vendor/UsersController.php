<?php

namespace App\Http\Controllers\Api\v2\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAdress;
use App\Models\UserFollow;
use App\Models\WalletPaymentReport;
use App\Models\VendorDetailsModel;
use App\Models\Likes;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\DatabaseRule;
use Illuminate\Contracts\Validation\Rule;
use Kreait\Firebase\Contract\Database;
use Validator;

class UsersController extends Controller
{
    //
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
                'oData' => (object) array(),
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
                    'oData' => (object) array(),
                    'errors' => (object) [],
                ]);
                exit;
                return response()->json([
                    'status' => "0",
                    'message' => login_message(),
                    'oData' => (object) array(),
                    'errors' => (object) [],
                ], 401);
                exit;
            }
        }
    }

    

    public function search_user(REQUEST $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'search_key' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $search_text = $request->search_key;
            $page = (int) $request->page ?? 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $user_id = $this->validateAccesToken($request->access_token);

            $search_result = User::where(['active' => 1, 'deleted' => 0])->where('id', '!=', $user_id);
            $search_result->where(function ($query) use ($search_text) {
                $query->where('users.name', 'LIKE', $search_text . '%');
            });
            $search_result = $search_result->orderBy('name', 'asc')->orderBy('id', 'desc');
            $search_result = $search_result->skip($offset)->take($limit)->get();
            if ($search_result->count() > 0) {
                $status = "1";
                $message = "Data fetched Successfully";
                $o_data = $search_result->toArray();
                $o_data = convert_all_elements_to_string($o_data);
            } else {
                $message = "no data to show";
            }
        }

        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }

    public function my_profile(REQUEST $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);

            $data = User::withCount(['posts', 'follower', 'followed','cart'])->where(['users.id' => $user_id])->get();
            if ($data->count() > 0) {
                $o_data = $data->first();

                $vendordata = VendorDetailsModel::where('user_id',$user_id)->first();
                if ($vendordata->logo) {
                $img = $vendordata->logo;
                } else {
                $img = asset("storage/placeholder.png");
                }
                
                $o_data->user_image = (string) $img;
                $o_data = convert_all_elements_to_string($o_data);
                $status = "1";
                $message = "data fetched Successfully";
            } else {
                $message = "no data to show";
            }
        }
        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    public function view_profile(REQUEST $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $profile_id = $request->user_id;

            $data = User::withCount(['posts', 'follower', 'followed'])->where(['users.id' => $profile_id])

                ->selectRaw(DB::raw(
                    "
          CASE
            WHEN
              (select count(id) from user_follows where user_id='" . $user_id . "' and follow_user_id='" . $profile_id . "') >= 1 and
              (select count(id) from user_follows where user_id='" . $profile_id . "' and follow_user_id='" . $user_id . "') >= 1
            THEN 'followed'
            WHEN
              (select count(id) from user_follows where user_id='" . $user_id . "' and follow_user_id='" . $profile_id . "') = 0 and
              (select count(id) from user_follows where user_id='" . $profile_id . "' and follow_user_id='" . $user_id . "') >= 1
            THEN 'request_recived'
            WHEN
              (select count(id) from user_follows where user_id='" . $user_id . "' and follow_user_id='" . $profile_id . "') >= 1 and
              (select count(id) from user_follows where user_id='" . $profile_id . "' and follow_user_id='" . $user_id . "') = 0
            THEN 'request_sent'
            WHEN
              (select count(id) from user_follows where user_id='" . $user_id . "' and follow_user_id='" . $profile_id . "') = 0 and
              (select count(id) from user_follows where user_id='" . $profile_id . "' and follow_user_id='" . $user_id . "') = 0
            THEN 'not'
          END as is_followed


          "
                ))
                ->get();
            if ($data->count() > 0) {
                $o_data = $data->first();
                $o_data->posts_count = (string) thousandsCurrencyFormat($o_data->posts_count);
                $o_data->follower_count = (string) thousandsCurrencyFormat($o_data->follower_count);
                $o_data->followed_count = (string) thousandsCurrencyFormat($o_data->followed_count);
                $o_data = convert_all_elements_to_string($o_data);
                $status = "1";
                $message = "data fetched Successfully";
            } else {
                $message = "no data to show";
            }
        }

        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    public function add_address(Request $request)
    {

        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
            'location' => 'required',
            'building_name' => 'required',
            'address' => 'required',
            'access_token' => 'required',
            'is_default' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);

            if($request->is_default == 1)
            {
            $removedefault = UserAdress::where('user_id',$user_id)->update(['is_default'=>0]);    
            }
            $address = new UserAdress();
            $address->user_id = $user_id;
            $address->full_name = $request->full_name??"";
            $address->dial_code = $request->dial_code??" ";
            $address->phone = $request->phone??" ";
            $address->address = $request->address;
            $address->country_id = $request->country_id?? 0;
            $address->state_id = $request->state_id?? 0;
            $address->city_id = $request->city_id?? 0;
            $address->land_mark = $request->land_mark;
            $address->building_name = $request->building_name;
            $address->latitude = $request->latitude;
            $address->longitude = $request->longitude;
            $address->location = $request->location;
            $address->address_type = $request->address_type?? 0;
            $address->is_default = $request->is_default;
            $address->status = 1;
            $address->save();
            $status = 1;
            $message = "Address added successfully";
            $o_data = UserAdress::get_address_list($user_id);
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);

    }
    public function edit_address(Request $request)
    {

        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'location' => 'required',
            'building_name' => 'required',
            'address' => 'required',
            'access_token' => 'required',
            'is_default' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $address = UserAdress::find($request->address_id);
            if (!$address) {
                $status = "0";
                $message = "No data found";
            } else {
                if($request->is_default == 1)
                {
                $removedefault = UserAdress::where('user_id',$user_id)->update(['is_default'=>0]);    
                }
                $address->user_id = $user_id;
                $address->full_name = $request->full_name?? " ";
                $address->dial_code = $request->dial_code??" ";
                $address->phone = $request->phone??" ";
                $address->address = $request->address;
                $address->country_id = $request->country_id?? 0;
                $address->state_id = $request->state_id?? 0;
                $address->city_id = $request->city_id?? 0;
                $address->land_mark = $request->land_mark;
                $address->building_name = $request->building_name;
                $address->latitude = $request->latitude;
                $address->longitude = $request->longitude;
                $address->location = $request->location;
                $address->address_type = $request->address_type?? 0;
                $address->is_default = $request->is_default;
                $address->status = 1;
                $address->save();
                $status = "1";
                $message = "Address updated successfully";
                $o_data = UserAdress::get_address_list($user_id);
            }

        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' =>$errors, 'oData' => $o_data]);

    }
    public function setdefault(Request $request)
    {

        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $address = UserAdress::find($request->address_id);
            if (!$address) {
                $status = "0";
                $message = "No data found";
            } else {
                $removedefault = UserAdress::where('user_id',$user_id)->update(['is_default'=>0]);
                $address->is_default = 1;
                $address->save();
                $status = "1";
                $message = "Address set as default";
                $o_data = UserAdress::get_address_list($user_id);
            }

        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' =>$errors, 'oData' => $o_data]);

    }
    public function delete_address(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $address = UserAdress::find($request->address_id);
            if (!$address) {
                $status = "0";
                $message = "No data found";
            } else {
                $address->status = 0;
                $address->save();
                $status = "1";
                $message = "Address deleted successfully";
                $o_data = UserAdress::get_address_list($user_id);
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);

    }
    public function list_address(Request $request)
    {
        $status = 0;
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
            $status = 1;
            $message = "Address fetched successfully";
            $o_data = UserAdress::get_address_list($user_id);
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);

    }
    public function update_user_profile(REQUEST $request){
      $status = 0;
      $message = "";
      $o_data = [];
      $errors = [];
      $user_id = $this->validateAccesToken($request->access_token);
      
      $validator = Validator::make($request->all(), [
          'access_token' => 'required',
          'first_name'  => 'required',
          'last_name'   => 'required',
          'user_image'  => 'mimes:jpeg,png,jpg'
      ]);

      if ($validator->fails()) {
          $status = 0;
          $message = "Validation error occured";
          $errors = $validator->messages();
      } else {
        $user = User::find($user_id);


        
        if($file = $request->file("user_image")){
                    $response = image_upload($request,'company','user_image');
                    if($response['status']){
                    $vendordatils['logo'] = $response['link'];
                    VendorDetailsModel::where('user_id',$user_id)->update($vendordatils);
                    }
        }

        


        $user->first_name    = $request->first_name;
        $user->last_name      = $request->last_name;
        $user->name         = $request->first_name." ".$request->last_name;
        $user->save();

        $data = User::where(['users.id' => $user_id])->get();
        $userdata = $data->first();

        $vendordata = VendorDetailsModel::where('user_id',$user_id)->first();
        if(!empty($vendordata->logo))
        {
         $userdata->user_image = $vendordata->logo;   
        }
        else
        {
          $userdata->user_image = asset("storage/placeholder.png");   
        }
        $users = [
                'id' => $user->id,
                'name' => $user->name ?? $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'image' => $userdata->user_image,
                'dial_code' => $user->dial_code ? $user->dial_code : '',
                'phone' => isset($user->phone) ? $user->phone : '',
                'gender' => $user->gender,
                'about' => $vendordata->description,
                'address1' => $vendordata->address1,
                'address2' => $vendordata->address2,
                'street' => $vendordata->street,
                'txt_location' => $vendordata->txt_location,
            ];
        $o_data = $users;
        $o_data = convert_all_elements_to_string($o_data);
        $status = 1;
        $message = "Profile updated Successfully";

        //enable exec on server
        if( config('global.server_mode') == 'local'){
          \Artisan::call('update:firebase_node '.$user_id);
        }else{
          exec("php ".base_path()."/artisan update:firebase_node ".$user_id." > /dev/null 2>&1 & ");
        }

      }
      return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }

    public function change_phone_number(REQUEST $request){
      $status = 0;
      $message = "";
      $o_data = [];
      $errors = [];
      $user_id = $this->validateAccesToken($request->access_token);
      $validator = Validator::make($request->all(), [
          'access_token' => 'required',
          'dial_code'         => 'required',
          'phone'    => 'required'
      ]);

      if ($validator->fails()) {
          $status = 0;
          $message = "Validation error occured";
          $errors = $validator->messages();
      } else {
        $user = User::find($user_id);
        if($user->dial_code != $request->dial_code || $user->phone != $request->phone){
          $mobile = $request->dial_code.$request->phone;
          $otp = config("global.otp");
          $messagen = "OTP to confirm your mobile number at ".config('global.site_name')." is ".$otp;
          send_normal_SMS($messagen,$mobile);
          $status = 1;
          $message = "Please verify the otp ";
          $user->dial_code= $request->dial_code;
          $user->phone     = $request->phone;
          $user->user_phone_otp = $otp;
          $user->save();
        }else{
          $message = "There is no change in your phone number";
        }


      }
      return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function validate_otp_phone_email_update(REQUEST $request){
      $status = 0;
      $message = "";
      $o_data = [];
      $errors = [];
      $user_id = $this->validateAccesToken($request->access_token);
      $rule = [
          'access_token' => 'required',
          'type'         => 'required|in:1,2',
          'otp'          => 'required'
      ];

      if($request->type == 1){
        $rule['dial_code'] = 'required';
        $rule['phone'] = 'required';
      }else{
        $rule['email'] = 'required';
      }
      $validator = Validator::make($request->all(), $rule);
      if ($validator->fails()) {
          $status = 0;
          $message = "Validation error occured";
          $errors = $validator->messages();
      } else {
        $user = User::find($user_id);
        $sent_opt = $user->user_phone_otp;
        if($request->type == 2){
          $sent_opt = $user->user_email_otp;
        }
        if($sent_opt == $request->otp){

          if($request->type==1){
            $user->dial_code = $request->dial_code;
            $user->phone     = $request->phone;
            $user->user_phone_otp = '';
            $user->save();
            $status = 1;
            $message = "Phone number updated successfully";
          }else{
            $user->email     = $request->email;
            $user->user_email_otp = '';
            $user->save();
            $status = 1;
            $message = "email id updated successfully";
          }
          if( config('global.server_mode') == 'local'){
            \Artisan::call('update:firebase_node '.$user_id);
          }else{
            exec("php ".base_path()."/artisan update:firebase_node ".$user_id." > /dev/null 2>&1 & ");
          }

        }else{
          $message = "Invalid otp sent";
        }


      }
      return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }

    public function change_email(REQUEST $request){
      $status = 0;
      $message = "";
      $o_data = [];
      $errors = [];
      $user_id = $this->validateAccesToken($request->access_token);
      $validator = Validator::make($request->all(), [
          'access_token' => 'required',
          'email'         => 'required|unique:users,email,'.$user_id
      ]);

      if ($validator->fails()) {
          $status = 0;
          $message = "Validation error occured";
          $errors = $validator->messages();
      } else {
        $user = User::find($user_id);
        if($user->email != $request->email){

          $otp = generate_otp();
          $name = $user->name;
          $mailbody = view('emai_templates.change_email_otp', compact('otp', 'name'));
          $ret = send_email($request->email, config('global.site_name')." email change request", $mailbody);
          if($ret){
            $status = 1;
            $message = "Please verify the otp ";
            $o_data = [
              'email' => $request->email
            ];
            $user->user_email_otp = $otp;
            $user->save();
          }else{
            $message = "Faild to sent mail. please try again after some times";
          }
        }else{
          $message = "There is no change in your phone number";
        }
      }
      return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function change_password(REQUEST $request){
      $status = 0;
      $message = "";
      $o_data = [];
      $errors = [];
      $user_id = $this->validateAccesToken($request->access_token);
      $validator = Validator::make($request->all(), [
          'access_token' => 'required',
          'old_password'         => 'required',
          'new_password'         => 'required'
      ]);

      if ($validator->fails()) {
          $status = 0;
          $message = "Validation error occured";
          $errors = $validator->messages();
      } else {
        $user = User::find($user_id);
        if(Hash::check($request->old_password, $user->password)){
          $user->password = bcrypt($request->new_password);
          $user->save();
          $status = 1;
          $message = "Password Updated successfully";
        }else{
          $message = "Old password not match";
        }


      }
      return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    public function wallet_payment_init(Request $request)
    {
        $status = 0;
        $o_data = [];
        $errors = [];
        $message = "Unable to initialize the payment";

        $user_id = $this->validateAccesToken($request->access_token);
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'amount' => 'required|integer|min:1',
            'payment_type' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $amount = $request->amount;
            // $max_wallet_amount = 50000 - $User->wallet_amount;
            // if ($amount > $max_wallet_amount || $max_wallet_amount < 0) {
            //     if ($max_wallet_amount <= 0) {
            //         $message = 'you have reached maximum amount limit in your wallet';
            //     } else {
            //         $message = "Maximum rechargable amount is AED " . $max_wallet_amount;
            //     }

            // } else {
            $user = User::find($user_id);
            $address = UserAdress::get_user_default_address($user_id);
            if(empty($address))
            {
                $status = 0;
                $message = "You are not added any address, Please add address";
            }
            else
            {


            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $checkout_session = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => 'JOD',
                'description' => 'Wallet Recharge (via App)',
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

            $ref = $checkout_session->id;
            $invoice_id = $user_id . uniqid() . time();
            $paymentreport = [
                'transaction_id' => $invoice_id,
                'payment_status' => 'P',
                'user_id' => $user->id,
                'ref_id' => $ref,
                'amount' => $amount,
                'method_type' => $request->payment_type,
                'created_at' => gmdate('Y-m-d H:i:s'),
            ];

            WalletPaymentReport::insert($paymentreport);
            $o_data['payment_ref'] = $checkout_session->client_secret;
            $o_data['invoice_id'] = $invoice_id;
            $status = 1;
            $message = "";
             }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }

    public function wallet_recharge(Request $request)
    {
        $status = 0;
        $o_data = [];
        $errors = [];
        $message = "Failed to recharge the wallet";

        $user_id = $this->validateAccesToken($request->access_token);
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'invoice_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $payment_det = WalletPaymentReport::where(['transaction_id' => $request->invoice_id, 'user_id' => $user_id, 'payment_status' => 'P'])->first();
            if ($payment_det) {
                $payamount = $payment_det->amount;
                $user = User::find($user_id);
                if ($user !== null) {
                    $user->wallet_amount = $user->wallet_amount + $payamount;
                    if ($user->save()) {
                        $data = [
                            'user_id' => $user_id,
                            'wallet_amount' => $payamount,
                            'pay_type' => 'credited',
                            'pay_method' => $payment_det->method_type,
                            'description' => 'Wallet Top up (via App)',
                        ];

                        if (wallet_history($data)) {
                            WalletPaymentReport::where(['transaction_id' => $request->invoice_id, 'user_id' => $user_id])->update(['payment_status' => 'A']);
                            $status = 1;
                            $message = "Wallet recharged successfully";
                        }
                    }
                }
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }

    public function wallet_details(Request $request)
    {
        $status = 1;
        $o_data = [];
        $errors = [];
        $message = "";

        $user_id = $this->validateAccesToken($request->access_token);
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $last_payment_det = WalletPaymentReport::where(['user_id'=>$user_id,'payment_status'=>'A'])->orderBy('id','desc')->first();
            $last_history_det = \App\Models\WalletHistory::where(['user_id'=>$user_id])->orderBy('id','desc')->first();
            $user = User::find($user_id);
            $o_data['balance'] = $user->wallet_amount;
            $o_data['last_recharged'] = '';
            if($last_payment_det){
              $o_data['last_recharged'] = date('d F Y h:i A',strtotime($last_payment_det->created_at));
            }
            $o_data['transaction']['last_updated'] = '';
            if($last_history_det){
              $o_data['transaction']['last_updated'] = date('d F Y h:i A',strtotime($last_history_det->created_at));
            }

            $wallet_history = \App\Models\WalletHistory::where(['user_id'=>$user_id])->orderBy('id','desc')->get();
            foreach($wallet_history as $key=>$val){
              $wallet_history[$key]->transaction_id = 'WR'.date(date('Ymd', strtotime($val->created_at))).$val->id;
              $wallet_history[$key]->date = date('d F Y',strtotime($val->created_at));
              $pay_method = '';
              if($val->pay_method==1){
                $pay_method = 'Credit Card';
              }
              if($val->pay_method==2){
                $pay_method = 'Apple Pay';
              }
              if($val->pay_method==3){
                $pay_method = 'Google Pay';
              }
              $wallet_history[$key]->pay_method = $pay_method;
            }
            $o_data['transaction']['list'] = $wallet_history;
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }
    function favourites(Request $request) {
        $status = 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $user_id = $this->validateAccesToken($request->access_token);
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => 0,
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }

        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();
        $limit = isset($request->limit) ? $request->limit : 10;
        $offset = isset($request->page) ? ($request->page - 1) * $request->limit : 0;

        
        
        //favourites Pharmacies
        
        $favourites_pharmacies = VendorDetailsModel::select('vendor_details.user_id as id', 'company_name', 'location', 'logo','latitude','longitude')
        ->join('likes','likes.vendor_id','=','vendor_details.user_id')
        ->where('likes.user_id',$user_id)
        ->get();

        foreach ($favourites_pharmacies as $key => $val) {
            $favourites_pharmacies[$key]->logo = asset($val->logo);
            $favourites_pharmacies[$key]->is_liked = 0;
            $favourites_pharmacies[$key]->rating = 0;
            if ($user) {
                $is_liked = Likes::where(['vendor_id' => $val->id, 'user_id' => $user->id])->count();
                if ($is_liked) {
                    $favourites_pharmacies[$key]->is_liked = 1;
                }
            }
        }
        //favourites Pharmacies END

        
        $o_data['favourites']  = $favourites_pharmacies;
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function like_dislike(REQUEST $request)
    {
        $status = 0;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'id' => 'required|numeric',
            'type'      => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            if($request->type == 1)
            {
                $field =  "product_id";
                $product_id = $request->id;
            }
            else
            {
                $field =  "vendor_id";
                $vendor_id = $request->id;
            }




            $user_id = $this->validateAccesToken($request->access_token);
            $check_exist = Likes::where([$field => $request->id, 'user_id' => $user_id])->get();
            if ($check_exist->count() > 0) {
                Likes::where([$field => $request->id, 'user_id' => $user_id])->delete();
                $status = 1;
                $message = "disliked";
            } else {
                $like = new Likes();
                $like->vendor_id = $vendor_id??0;
                $like->product_id = $product_id??0;
                $like->user_id = $user_id;
                $like->created_at = gmdate('Y-m-d H:i:s');
                $like->save();
                if ($like->id > 0) {
                    $status = 1;
                    $message = "liked";
                } else {
                    $message = "faild to like";
                }
            }
        }
        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    
}
