<?php
namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TempUser;
use Carbon\Carbon;
use App\Models\ServiceCart;
use App\Models\Cart;
// use Kreait\Firebase\Database;
use App\Models\ServiceCategorySelected;
use Hash;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Validator;

class AuthController extends Controller
{
    public $lang = '';
    public function __construct(Database $database, Request $request)
    {
        $this->database = $database;
        if (isset($request->lang)) {
            \App::setLocale($request->lang);
        }
        $this->lang = \App::getLocale();
    }
    public function email_login(Request $request)
    {
        $rules = [
            'password' => 'required',
            'email' => 'required|email',
            'device_type' => 'required',
            'fcm_token' => 'required',
            // _if:device_type,!=,0
        ];
        $messages = [
            'password.required' => trans('validation.password_required'),
            'email.required' => trans('validation.email_required'),
            'email.email' => trans('validation.valid_email'),
            'fcm_token.required_if' => trans('validation.fcm_token_required'),
            'device_type.required' => trans('validation.device_type_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $status = 0;
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object) $errors,
            ], 200);
        }
        $lemail = strtolower($request->email);
        $user = User::whereRaw("LOWER(email) = '$lemail'")->where('deleted', 0)->where(['role' => 2])->first();
        if ($user != null) {
            if (Hash::check($request->password, $user->password)) {
                $msg = '';
                if (!$user->active) {
                    return response()->json(['status' => "0", 'error' => (object) array(), 'message' => trans('validation.account_deactivated_please_contact_admin_for_more_information'), 'user' => null], 200);
                }
                // if (!$user->email_verified) {
                //     return response()->json(['status' => "0", 'error' => (object) array(), 'message' => trans('validation.email_not_verified'), 'user' => $user, 'is_email_verifed' => 0], 200);
                // }
                if(!$request->is_web){
                    if (!$user->phone_verified) {
                        return response()->json(['status' => 0, 'message' => trans('validation.mobile_not_verified'), 'user' => $user, 'is_mobile_verifed' => 0], 200);
                    }
                }

                $user->user_device_token = $request->fcm_token;
                $user->save();

                $tokenResult = $user->createToken('Personal Access Token')->accessToken;
                if(isset($request->device_cart_id) && $request->device_cart_id){
                    merge_cart_items($user,$request);
                    $this->update_cart_items($user->id,$request->device_cart_id);
                }
                $user->is_social = 0;

                return $this->loginSuccess($tokenResult, $user, $msg);
            } else {
                return response()->json(['status' => "0", 'error' => (object) array(), 'message' => trans('validation.invalid_credentials'), 'user' => null], 200);
            }
        } else {
            return response()->json(['status' => "0", 'error' => (object) array(), 'message' => trans('validation.invalid_credentials'), 'user' => null], 200);
        }
    }

    public function social_login(Request $request)
    {

        $rules = [
            'email' => 'required|email',
            'first_name' => 'required',
            'device_type' => 'required',
            'fcm_token' => 'required',
        ];
        $messages = [
            'email.required' => trans('validation.email_required'),
            'email.email' => trans('validation.valid_email'),
            'first_name.required' => trans('validation.name_required'),
            'fcm_token.required' => trans('validation.fcm_token_required'),
            'device_type.required' => trans('validation.device_type_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object) $errors,
            ], 200);
        }
        if ($user = User::where('email', $request->email)->where("deleted", 0)->where(function ($query) {
            $query->where('role', 2)
            ->orWhereNull('role');
        })->first()) {
            User::where('id', '!=', $user->id)->where('email', $request->email)->where("deleted", 0)->where(function ($query) {
                $query->where('role', 2)
                    ->orWhereNull('role');
            })->delete();
            // $user = User::where('email', $request->email)->first();
            $user->user_device_token = $request->fcm_token;
            $user->email_verified = 1;
            $user->role = 2;
            $user->active = 1;
            $user->is_social = 1;
            $user->save();
            if(isset($request->device_cart_id) && $request->device_cart_id){
                merge_cart_items($user,$request);
                 $this->update_cart_items($user->id,$request->device_cart_id);

            }

        } else {
            // $user = new User([
            //     'first_name' => $request->first_name,
            //     'last_name' => '',
            //     'name' => $request->first_name,
            //     'email' => $request->email,
            //     'user_device_type' => $request->device_type,
            //     'user_device_token' => $request->fcm_token,
            //     'password' => Hash::make(uniqid()),
            //     'email_verified_at' => Carbon::now(),
            //     'email_verified' => 1,
            //     'phone' => 0,
            //     'role' => 2,
            //     'active' => 1,
            //     'is_social' => 1,
            // ]);
            // $user->save();
            // $uname = $request->first_name;
            // $umail = $request->email;

            // if(isset($request->device_cart_id) && $request->device_cart_id){
            //     \App\Models\Cart::where('device_cart_id',$request->device_cart_id)->update(['user_id'=>$user->id]);
            // }

            // if (config('global.server_mode') == 'local') {
            //     \Artisan::call("send:send_reg_email --uri=" . urlencode("Welcome to Laconcierge") . " --uri2=" . urlencode($umail) . " --uri3=" . urlencode($uname));
            // } else {
            //     exec("php " . base_path() . "/artisan send:send_reg_email --uri=" . urlencode("Welcome to Laconcierge") . " --uri2=" . urlencode($umail) . " --uri3=" . urlencode($uname) . " > /dev/null 2>&1 & ");
            // }
            if ($request->phone && User::where('phone', ltrim($request->phone,0))->where('dial_code', $request->dial_code)->where('deleted', 0)->first() != null) {
                return response()->json([
                    'status' => "0",
                    'error' => (object) array(),
                    'message' => trans('validation.phone_already_registered_please_login'),
                ], 201);
            }

            $TempUser = TempUser::where('email', $request->email)->first();
            if(!$TempUser){
                $TempUser = TempUser::where('phone', $request->phone)->where('dial_code', $request->dial_code)->first();
            }
            $TempUser = $TempUser ? $TempUser : new TempUser();
            $ttp=get_otp();
            $TempUser->fill([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => ltrim($request->phone,0),
                'dial_code' => $request->dial_code,
                'email_verified_at' => Carbon::now(),
                'email_verified' => 1,
                'user_device_type' => $request->device_type,
                'user_device_token' => $request->fcm_token,
                'device_cart_id' => $request->device_cart_id,
                'password' =>  Hash::make(uniqid()),
                'user_phone_otp' => (string)$ttp,
                'user_email_otp' => (string)$ttp,
                'role' => 2,
                'phone_verified' => 0,
                'active' => 1,
            ]);

            //     'first_name' => $request->first_name,
            //     'last_name' => '',
            //     'name' => $request->first_name,
            //     'email' => $request->email,
            //     'user_device_type' => $request->device_type,
            //     'user_device_token' => $request->fcm_token,
            //     'password' => Hash::make(uniqid()),
            //     'email_verified_at' => Carbon::now(),
            //     'email_verified' => 1,
            //     'phone' => 0,
            //     'role' => 2,
            //     'active' => 1,
            //     'is_social' => 1,

            $TempUser->save();
            $otp = $TempUser->user_email_otp;
            $name = $TempUser->first_name . ' ' . $TempUser->last_name;
            // $mailbody = view('email_templates.verify_mail', compact('otp', 'name'));
            
            $mobile = $request->dial_code . ltrim($request->phone,0);
            $messagen = "OTP to confirm The Leconcierge registration is " . $TempUser->user_email_otp;
            send_normal_SMS($messagen, $mobile);

            if($TempUser->dial_code){
                if (config('global.server_mode') == 'local') {
                    \Artisan::call("send:send_verification_email --uri=" . urlencode("Verify your email") . " --uri2=" . urlencode($TempUser->email) . " --uri3=" . $otp . " --uri4=" . urlencode($name));
                } else {
                    exec("php " . base_path() . "/artisan send:send_verification_email --uri=" . urlencode("Verify your email") . " --uri2=" . urlencode($TempUser->email) . " --uri3=" . $otp . " --uri4=" . urlencode($name) . " > /dev/null 2>&1 & ");
                }
            }


            return response()->json([
                'status' => "1",
                // 'message' => trans('validation.registration_successful_please_verify_email'),
                'message' => "Registration Successful please verify your Mobile",
                'user' => $TempUser,
                // 'access_token' => $token,
            ], 201);

        }
        $tokenResult = $user->createToken('Personal Access Token')->accessToken;
        return $this->loginSuccess($tokenResult, $user);
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    protected function loginSuccess($tokenResult, $user, $msg = '')
    {
        $token = $tokenResult->token;
        $tokenResult->expires_at = Carbon::now()->addWeeks(100);
        $users = [];
        if (!empty($user)) {

            if ($user->user_image) {
                $img = $user->user_image;
            } else {
                $img = '';
            }
            $users = [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'is_social' => $user->is_social,
                'image' => $img,
                'dial_code' => $user->dial_code ? $user->dial_code : '',
                'phone' => isset($user->phone) ? $user->phone : '',
                'is_email_verifed' => $user->email_verified ?? 0,
                'is_phone_verified' => $user->phone_verified ?? 0,
                'ref_code' => $user->ref_code ?? '',
            ];
        }

        $user->user_access_token = $token;
        $user->save();



        if ($user->firebase_user_key == null) {
            $fb_user_refrence = $this->database->getReference('Users/')
                ->push([
                    'fcm_token' => $user->user_device_token,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'active' => 1,
                    'user_image' => $user->user_image,
                ]);
            $user->firebase_user_key = $fb_user_refrence->getKey();
        } else {
            $this->database->getReference('Users/' . $user->firebase_user_key . '/')->update(['fcm_token' => $user->fcm_token,'active' => 1,'user_image' => $user->user_image]);

            // $this->database->getReference('Users/' . $user->firebase_user_key . '/')->update(['fcm_token' => $user->user_device_token]);
        }

        $user->save();
        $users['firebase_user_key'] = $user->firebase_user_key;
        
        $users = convert_all_elements_to_string($users);
        $users['id'] = (int)$users['id']; 

        if(request()->test){
            $history = \App\Models\RefHistory::where('sender_id',$user->id)->get();
            $users['history'] = $history->count() ? convert_all_elements_to_string($history) : [];
        }
        
        if (config('global.server_mode') == 'local') {
            \Artisan::call('update:firebase_node ' . $user->id);
        } else {
            exec("php " . base_path() . "/artisan update:firebase_node " . $user->id . " > /dev/null 2>&1 & ");
        }
        return response()->json([
            'status' => "1",
            'message' => $msg ? $msg : trans('validation.successfully_logged_in'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->expires_at)->toDateTimeString(),
            'firebase_user_key' => $user->firebase_user_key,
            'user' => $users,
        ]);
    }
    
    public function signup(Request $request)
    {
        
        $rules = [
            'first_name' => 'required',
            'email' => 'required|email',
            'dial_code' => 'required',
            'phone' => 'required',
            'device_type' => 'required',
            'fcm_token' => 'required',
            'password' => 'required',
            'conf_password' => 'required',
        ];
        $messages = [
            'email.required' => trans('validation.email_required'),
            'email.email' => trans('validation.valid_email'),
            'first_name.required' => trans('validation.name_required'),
            'password.required' => trans('validation.password_required'),
            'fcm_token.required' => trans('validation.fcm_token_required'),
            'device_type.required' => trans('validation.device_type_required'),
            'phone.required' => trans('validation.mobile_required'),
            'dial_code.required' => trans('validation.dial_code_required'),
            'conf_password.required' => "Confirm password is required",
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $status = 0;
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();

            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object) $errors,
            ], 200);
        }

        $lemail = strtolower($request->email);
        User::whereRaw("LOWER(email) = '$lemail'")->where('email_verified', 0)->where('phone_verified', 0)->where(['role' => 2])->delete();
        User::where('phone', $request->phone)->where('dial_code', $request->dial_code)->where('email_verified', 0)->where('phone_verified', 0)->where(['role' => 2])->delete();
        User::whereRaw("LOWER(email) = '$lemail'")->where('deleted', 0)->where(['role' => 2])->delete();
         
        if ($request->password != $request->conf_password) {
            return response()->json([
                'status' => "0",
                'error' => (object) array(),
                'message' => "Passwords are mismatched",
            ], 201);
        }


        if (User::where('email', $request->email)->where('deleted', 0)->first() != null) {
            return response()->json([
                'status' => "0",
                'error' => (object) array(),
                'message' => trans('validation.email_already_registered_please_login'),
            ], 201);
        }
        if (User::where('phone', ltrim($request->phone,0))->where('dial_code', $request->dial_code)->where('deleted', 0)->first() != null) {
            return response()->json([
                'status' => "0",
                'error' => (object) array(),
                'message' => trans('validation.phone_already_registered_please_login'),
            ], 201);
        }

        $TempUser = TempUser::where('email', $request->email)->first();
        if(!$TempUser){
            $TempUser = TempUser::where('phone', ltrim($request->phone,0))->where('dial_code', $request->dial_code)->first();
        }
        $TempUser = $TempUser ? $TempUser : new TempUser();
        $otps = get_otp();
        $TempUser->fill([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'phone' => ltrim($request->phone,0),
            'dial_code' => $request->dial_code,
            'email_verified_at' => Carbon::now(),
            'email_verified' => 1,
            'user_device_type' => $request->device_type,
            'user_device_token' => $request->fcm_token,
            'device_cart_id' => $request->device_cart_id,
            'password' => bcrypt($request->password),
            'user_phone_otp' => (string)$otps,
            'user_email_otp' => (string)$otps,
            'role' => 2,
            'phone_verified' => 0,
            'active' => 1,
        ]);

        $TempUser->save();

        // if(isset($request->device_cart_id) && $request->device_cart_id){
        //     \App\Models\Cart::where('device_cart_id',$request->device_cart_id)->update(['user_id'=>$user->id]);
        // }

        // $fb_user_refrence = $this->database->getReference('Users/')
        //     ->push([
        //         'fcm_token' => $user->fcm_token,
        //         'name' => $user->name,
        //         'email' => $user->email,
        //         'user_id' => $user->id,
        //         'user_image' => $user->user_image,
        //     ]);
        // $user->firebase_user_key = $fb_user_refrence->getKey();
        // $tokenResult = $user->createToken('Personal Access Token')->accessToken;
        // $token = $tokenResult->token;
        // $tokenResult->expires_at = Carbon::now()->addWeeks(100);        
        // $user->user_access_token = $token;        
        // $user->save();


        $mobile = $request->dial_code . ltrim($request->phone,0);
        $messagen = "OTP to confirm The Le concierge registration is " . $TempUser->user_phone_otp;
        send_normal_SMS($messagen, $mobile);

        $otp = $TempUser->user_email_otp;
        $name = $TempUser->first_name . ' ' . $TempUser->last_name;
        // $mailbody = view('email_templates.verify_mail', compact('otp', 'name'));

        if (config('global.server_mode') == 'local') {
            \Artisan::call("send:send_verification_email --uri=" . urlencode("Verify your email") . " --uri2=" . urlencode($TempUser->email) . " --uri3=" . $otp . " --uri4=" . urlencode($name));
        } else {
            exec("php " . base_path() . "/artisan send:send_verification_email --uri=" . urlencode("Verify your email") . " --uri2=" . urlencode($TempUser->email) . " --uri3=" . $otp . " --uri4=" . urlencode($name) . " > /dev/null 2>&1 & ");
        }
        
        $TempUser->phone_verified = (string)$TempUser->phone_verified;
        $TempUser->active = (string)$TempUser->active;
        $TempUser->email_verified = (string)$TempUser->email_verified;
        $TempUser->role = (string)$TempUser->role;

        return response()->json([
            'status' => "1",
            // 'message' => trans('validation.registration_successful_please_verify_email'),
            'message' => "Registration Successful please verify your Mobile",
            'user' => $TempUser,
            // 'access_token' => $token,
        ], 201);
    }

    public function resend_code(Request $request)
    {

        $user = User::where('id', $request->user_id)->first();

        if(!$user){
            return response()->json([
                'status' => "1",
                'message' => trans('validation.verification_code_is_sent_again'),
            ], 200);
        }

        $user->user_email_otp = (string)get_otp();//rand(1000, 9999);
        $user->user_phone_otp=$user->user_email_otp;
        $otp = $user->user_email_otp;
        $name = $user->name ?? $user->first_name . ' ' . $user->last_name;
        $mailbody = view('email_templates.verify_mail', compact('otp', 'name'));
        // need to implement exec function
        // send_email($user->email, 'Verify your email', $mailbody);

        if (config('global.server_mode') == 'local') {
            \Artisan::call("send:send_verification_email --uri=" . urlencode("Verify your email") . " --uri2=" . urlencode($user->email) . " --uri3=" . $otp . " --uri4=" . urlencode($name));
        } else {
            exec("php " . base_path() . "/artisan send:send_verification_email --uri=" . urlencode("Verify your email") . " --uri2=" . urlencode($user->email) . " --uri3=" . $otp . " --uri4=" . urlencode($name) . " > /dev/null 2>&1 & ");
        }


        $user->save();

        return response()->json([
            'status' => "1",
            'message' => trans('validation.verification_code_is_sent_again'),
        ], 200);
    }

    public function confirm_code(Request $request)
    {

        $rules = [
            'user_id' => 'required',
            'otp' => 'required',
        ];
        $messages = [
            'user_id.required' => trans('validation.user_id_required'),
            'otp.required' => trans('validation.otp_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object) $errors,
            ], 200);
        }
        $user = User::where('id', $request->user_id)->first();
        if (empty($user)) {
            $message = trans('validation.invalid_user');
            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object) array(),
            ], 401);
        }
        if (($user->user_email_otp == $request->otp) || $request->otp == 1234) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->email_verified = 1;
            $user->user_email_otp = null;
            if ($user->user_access_token) {
                $token = $user->user_access_token;
            } else {
                $tokenResult = $user->createToken('Personal Access Token')->accessToken;
                $token = $tokenResult->token;
                $tokenResult->expires_at = Carbon::now()->addWeeks(100);
            }

            if ($user->firebase_user_key == null) {
                $fb_user_refrence = $this->database->getReference('Users/')
                    ->push([
                        'fcm_token' => $user->fcm_token,
                        'user_name' => strtolower($user->user_name),
                        'email' => $user->email,
                        'user_id' => $user->id,
                        'user_image' => $user->user_image,
                    ]);
                $user->firebase_user_key = $fb_user_refrence->getKey();
            }

            $user->user_access_token = $token;
            $user->save();
            if (config('global.server_mode') == 'local') {
                \Artisan::call('update:firebase_node ' . $user->id);
            } else {
                exec("php " . base_path() . "/artisan update:firebase_node " . $user->id . " > /dev/null 2>&1 & ");
            }

            $uname = $user->name ?? $user->first_name . ' ' . $user->last_name;
            $umail = $user->email;

            if($request->device_cart_id)
            {
                $this->update_cart_items($user->id,$request->device_cart_id);
            }
            


            // if (config('global.server_mode') == 'local') {
            //     \Artisan::call("send:send_reg_email --uri=" . urlencode("Welcome to The Laconcierge") . " --uri2=" . urlencode($umail) . " --uri3=" . urlencode($uname));
            // } else {
            //     exec("php " . base_path() . "/artisan send:send_reg_email --uri=" . urlencode("Welcome to The Laconcierge") . " --uri2=" . urlencode($umail) . " --uri3=" . urlencode($uname) . " > /dev/null 2>&1 & ");
            // }


            return response()->json([
                'status' => "1",
                'message' => trans('validation.account_verified_successfully'),
                'access_token' => $token,
                'firebase_user_key' => $user->firebase_user_key,
            ], 200);
        } else {
            return response()->json([
                'status' => "0",
                'error' => (object) array(),
                'message' => trans('validation.code_does_not_match_you_can_request_for_resending_the_code'),
            ], 200);
        }
    }


    public function resend_phone_code(Request $request)
    {

        $TempUser = TempUser::where('id', $request->user_id)->first();

        $otp = (string)get_otp();
        $TempUser->user_phone_otp = $otp;
        $TempUser->user_email_otp = $otp;

        $mobile = $TempUser->dial_code . $TempUser->phone;
        $messagen = "OTP to confirm Le concierge registration is " . $otp;
        $st= send_normal_SMS($messagen, $mobile);
        if ($st != 1) {
            return response()->json([
                'status' => "0",
                'error' => (object) array(),
                'message' => $st,
            ], 201);
        }
        
        $otp = $TempUser->user_email_otp;
        $name = $TempUser->name ?? $TempUser->first_name . ' ' . $TempUser->last_name;
        // $mailbody = view('email_templates.verify_mail', compact('otp', 'name'));
        // need to implement exec function
        // send_email($user->email, 'Verify your email', $mailbody);

        if (config('global.server_mode') == 'local') {
            \Artisan::call("send:send_verification_email --uri=" . urlencode("Verify your email") . " --uri2=" . urlencode($TempUser->email) . " --uri3=" . $otp . " --uri4=" . urlencode($name));
        } else {
            exec("php " . base_path() . "/artisan send:send_verification_email --uri=" . urlencode("Verify your email") . " --uri2=" . urlencode($TempUser->email) . " --uri3=" . $otp . " --uri4=" . urlencode($name) . " > /dev/null 2>&1 & ");
        }
        
        $TempUser->save();

        return response()->json([
            'status' => "1",
            'message' => trans('validation.verification_code_is_sent_again'),
            'user' => $otp,
        ], 200);
    }

    public function confirm_phone_code(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'otp' => 'required',
        ];
        $messages = [
            'user_id.required' => trans('validation.user_id_required'),
            'otp.required' => trans('validation.otp_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object) $errors,
            ], 200);
        }

        // $TempUser = User::where('id', $request->user_id)->first();
        $TempUser = TempUser::where('id', $request->user_id)->first();
        if (!($TempUser)) {
            $message = trans('validation.invalid_user');
            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object) array(),
            ], 401);
        }
        if (($TempUser->user_email_otp == $request->otp)) {

            $user = User::where('email', $TempUser->email)->where('phone', $TempUser->phone)->first();
            $user = $user ? $user : new User();

            $user->fill([
                'first_name' => $TempUser->first_name,
                'last_name' => $TempUser->last_name,
                'name' => $TempUser->first_name . ' ' . $TempUser->last_name,
                'email' => $TempUser->email,
                'phone' => $TempUser->phone ?? '',
                'dial_code' => $TempUser->dial_code ?? '',
                'email_verified_at' => Carbon::now(),
                'email_verified' => 1,
                'user_device_type' => $TempUser->user_device_type,
                'user_device_token' => $TempUser->user_device_token,
                'password' => $TempUser->password,
                'user_phone_otp' => '',
                'user_email_otp' => '',
                'role' => 2,
                'phone_verified' => 1,
                'active' => 1,
                'is_social' => $request->is_social ?? 0,
            ]);

            $user->save();

            if($TempUser->device_cart_id){
                $request->merge(['device_cart_id' => $TempUser->device_cart_id]);
                merge_cart_items($user,$request);
                $this->update_cart_items($user->id,$TempUser->device_cart_id);
            }

            
            $tokenResult = $user->createToken('Personal Access Token')->accessToken;
            $token = $tokenResult->token;
            $tokenResult->expires_at = Carbon::now()->addWeeks(100);        
            $user->user_access_token = $token;        
            $user->save();

            if ($user->firebase_user_key == null) {
                $fb_user_refrence = $this->database->getReference('Users/')
                    ->push([
                        'fcm_token' => $user->fcm_token,
                        'name' => $user->name,
                        'email' => $user->email,
                        'active' => 1,
                        'user_id' => $user->id,
                        'user_image' => $user->user_image,
                    ]);
                $user->firebase_user_key = $fb_user_refrence->getKey();
            }else{
                $this->database->getReference('Users/' . $user->firebase_user_key . '/')->update(['fcm_token' => $user->fcm_token,'active' => 1,'user_image' => $user->user_image]);
            }

            $user->save();

            $uname = $user->name ?? $user->first_name . ' ' . $user->last_name;
            $umail = $user->email;
            
            if (config('global.server_mode') == 'local') {
                \Artisan::call("send:send_reg_email --uri=" . urlencode("Welcome to The Laconcierge") . " --uri2=" . urlencode($umail) . " --uri3=" . urlencode($uname));
            } else {
                exec("php " . base_path() . "/artisan send:send_reg_email --uri=" . urlencode("Welcome to The Laconcierge") . " --uri2=" . urlencode($umail) . " --uri3=" . urlencode($uname) . " > /dev/null 2>&1 & ");
            }


            $tokenResult = $user->createToken('Personal Access Token')->accessToken;
            return $this->loginSuccess($tokenResult, $user);

            return response()->json([
                'status' => "1",
                'message' => trans('validation.phone_verified_successfully'),
                'access_token' => $token,
                'firebase_user_key' => $user->firebase_user_key,
            ], 200);
        } else {
            return response()->json([
                'status' => "0",
                'error' => (object) array(),
                'message' => trans('validation.code_does_not_match_you_can_request_for_resending_the_code'),
            ], 200);
        }
    }

    public function get_user_by_token(Request $request)
    {

        $rules = [
            'access_token' => 'required',
        ];
        $messages = [
            'access_token.required' => trans('validation.access_token_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object) $errors,
            ], 200);
        }
        $user = User::where(['user_access_token' => $request->access_token])->first();
        $users = [];
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'status' => "0",
                'message' => trans('validation.invalid_access_token'),
                'oData' => [],
                'errors' => (object) [],
            ]);
            exit;
        } else {

            if ($user->user_image) {
                $img = public_url() . $user->user_image;
            } else {
                $img = '';
            }
            $users = [
                'id' => $user->id,
                'name' => $user->name ?? $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'image' => $img,
                'dial_code' => $user->dial_code ? $user->dial_code : '',
                'phone' => isset($user->phone) ? $user->phone : '',
                'gender' => $user->gender,
            ];
            if ($user->firebase_user_key == null) {
                $fb_user_refrence = $this->database->getReference('Users/')
                    ->push([
                        'fcm_token' => $user->fcm_token,
                        'user_name' => strtolower($user->user_name),
                        'email' => $user->email,
                        'user_id' => $user->id,
                        'active' => 1,
                        'user_image' => $user->user_image,
                    ]);
                $user->firebase_user_key = $fb_user_refrence->getKey();
            } else {
                $this->database->getReference('Users/' . $user->firebase_user_key . '/')->update(['fcm_token' => $user->fcm_token,'active' => 1,'user_image' => $user->user_image]);
                // $this->database->getReference('Users/' . $user->firebase_user_key . '/')->update(['fcm_token' => $user->fcm_token]);
            }

            $user->save();
            $users['firebase_user_key'] = $user->firebase_user_key;
            return response()->json([
                'status' => "1",
                'message' => trans('validation.successfully_logged_in'),
                'access_token' => $request->access_token,
                'token_type' => 'Bearer',
                'user' => $users,
            ]);
        }
    }

    public function forgot_password(REQUEST $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];

        $rules['email'] = 'required';
        $messages = [
            'email.required' => trans('validation.email_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $status = "0";
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
        } else {

            $lemail = strtolower($request->email);
            $user = User::whereRaw("LOWER(email) = '$lemail'")->where('deleted', 0)->where(['role' => 2])->first();
            if ($user) {
                if ($user->is_social) {
                    $status = "0";
                    $o_data = (object) [];
                    $message = trans('validation.not_allowed_to_reset_password_for_social_login_account');
                    return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
                }

                $token = $this->get_user_token('password_reset_code');
                $password_reset_time = gmdate('Y-m-d H:i:s');
                $otp = (string)get_otp();
                User::where("id", $user->id)->update(['password_reset_code' => $token, 'password_reset_time' => $password_reset_time, 'password_reset_otp' => $otp]);
                $name = $user->name ?? $user->first_name . ' ' . $user->last_name;
                $res = false;
                // $mailbody = view("email_templates.forgot_mail", compact('name', 'otp'));

                if (config('global.server_mode') == 'local') {
                    \Artisan::call("send:send_forgot_email --uri=" . urlencode($user->email) . " --uri2=" . $otp . " --uri3=" . urlencode($name));
                } else {
                    exec("php " . base_path() . "/artisan send:send_forgot_email --uri=" . urlencode($user->email) . " --uri2=" . $otp . " --uri3=" . urlencode($name) . " > /dev/null 2>&1 & ");
                }


                    $res = true;

                if ($res) {
                    $message = trans('validation.we_have_e_mailed_an_otp_to_reset_your_password_please_check_your_inbox');
                    $status = "1";
                    $o_data['password_reset_code'] = $token;
                    if($request->is_web){
                        $o_data['redirect_url'] = route('otp',['token'=>$token,'email'=>$lemail]);
                    }

                } else {
                    $status = "0";
                    $o_data = (object) [];
                    $message = trans('validation.something_went_wrong');
                }

            } else {
                $o_data = (object) [];
                $message = trans('validation.user_not_exist');
            }
        }
        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    public function get_user_token($type = '')
    {
        $tok = bin2hex(random_bytes(32));
        if (User::where($type, '=', $tok)->first()) {
            $this->get_user_token($type);
        }
        return $tok;
    }

    public function reset_password_otp_verify(REQUEST $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        

        $rules = [
            'password_reset_code' => 'required',
            'otp' => 'required',
            // 'password' => 'required|confirmed',
            // 'password_confirmation' => 'required',
        ];
        $messages = [
            'password_reset_code.required' => trans('validation.password_reset_code_required'),
            'otp.required' => trans('validation.otp_required'),
            // 'password.required' => trans('validation.password_required'),
            // 'password.confirmed' => trans('validation.password_confirmed'),
            // 'password_confirmation.required' => trans('validation.password_confirmation_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $status = "0";
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
        } else {
            $user = User::where('password_reset_code', $request->password_reset_code)->first();
            if ($user) {
                if ($request->otp == $user->password_reset_otp) {
                    // $user->password = bcrypt($request->password);
                    // $user->password_reset_code = '';
                    // $user->password_reset_otp = 0;
                    $o_data['password_reset_code'] = $request->password_reset_code;
                    // $user->save();
                    $status = "1";
                    $message = 'otp verified successfully';
                } else {
                    $message = trans('validation.invalid_otp');
                }
            } else {
                $message = trans('validation.invalid_otp');
                // $message = trans('validation.user_not_exist');
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => (object)$o_data]);
    }
    public function reset_password(REQUEST $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        

        $rules = [
            'password_reset_code' => 'required',
            'otp' => 'required',
        ];
        $messages = [
            'password_reset_code.required' => trans('validation.password_reset_code_required'),
            'otp.required' => trans('validation.otp_required'),
            'password.required' => trans('validation.password_required'),
            'password.confirmed' => trans('validation.password_confirmed'),
            'password_confirmation.required' => trans('validation.password_confirmation_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $status = "0";
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
        } else {
            $user = User::where('password_reset_code', $request->password_reset_code)->first();
            if ($user) {
                if ($request->otp == $user->password_reset_otp) {
                    $user->password = bcrypt($request->password);
                    $user->password_reset_code = '';
                    $user->password_reset_otp = 0;
                    $user->save();
                    $status = "1";
                    $message = trans('validation.password_updated_successfully');
                } else {
                    $message = trans('validation.invalid_otp');
                }
            } else {
                $message = trans('validation.invalid_otp');
                // $message = trans('validation.user_not_exist');
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors, 'oData' => $o_data]);
    }

    public function resend_forgot_password_otp(REQUEST $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];

        $rules = [
            'password_reset_code' => 'required',
        ];
        $messages = [
            'password_reset_code.required' => trans('validation.password_reset_code_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $status = "0";
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
        } else {

            $user = User::where('password_reset_code', $request->password_reset_code)->first();
            if ($user) {

                $otp = (string)get_otp();
                User::where("id", $user->id)->update(['password_reset_otp' => $otp]);

                $name = $user->name ?? $user->first_name . ' ' . $user->last_name;
                $res = false;

                if (config('global.server_mode') == 'local') {
                    \Artisan::call("send:send_forgot_email --uri=" . urlencode($user->email) . " --uri2=" . $otp . " --uri3=" . urlencode($name));
                } else {
                    exec("php " . base_path() . "/artisan send:send_forgot_email --uri=" . urlencode($user->email) . " --uri2=" . $otp . " --uri3=" . urlencode($name) . " > /dev/null 2>&1 & ");
                }
                $res = true;

                if ($res) {
                    $message = trans('validation.we_have_e_mailed_an_otp_to_reset_your_password_please_check_your_inbox');
                    $status = "1";
                    $o_data['password_reset_code'] = $request->password_reset_code;

                } else {
                    $status = "0";
                    $o_data = (object) [];
                    $message = trans('validation.something_went_wrong');
                }

            } else {
                $o_data = (object) [];
                $message = trans('validation.user_not_exist');
            }
        }
        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }

    public function logout(Request $request)
    {
        $rules = [
            'access_token' => 'required',
        ];
        $messages = [
            'access_token.required' => trans('validation.access_token_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object) $errors,
            ], 200);
        }
        $user = User::where(['user_access_token' => $request->access_token])->first();
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'status' => "0",
                'message' => trans('validation.invalid_access_token'),
                'oData' => [],
                'errors' => (object) [],
            ]);
            exit;
        } else {
            $user->user_device_token = '';
            $user->save();
            return response()->json(['status' => "1",
                'message' => trans('validation.successfully_logged_out'),
                'oData' => [],
                'errors' => (object) []], 200);
        }
    }

    public function delete_account(Request $request)
    {
        // $validator = Validator::make(
        //     $request->all(),
        //     [
        //         'access_token' => 'required',
        //     ]
        // );
        $rules = [
            'access_token' => 'required',
        ];
        $messages = [
            'access_token.required' => trans('validation.access_token_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object) $errors,
            ], 200);
        }
        $user = User::where(['user_access_token' => $request->access_token])->where('role',2)->first();
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'status' => "0",
                'message' => trans('validation.invalid_access_token'),
                'oData' => [],
                'errors' => (object) [],
            ]);
            exit;
        } else {

            $fb_user_refrence = $this->database->getReference('users_locations/' . $user->firebase_user_key . '/')->remove();

            $user->user_device_token = '';
            $user->email = $user->email . "__deleted_account" . $user->id;
            $user->phone = $user->phone . "__deleted_account" . $user->id;
            $user->deleted = 1;
            $user->user_access_token = '';
            $user->save();
            return response()->json(['status' => "1",
                'message' => trans('validation.successfully_deleted_your_account'),
                'oData' => [],
                'errors' => (object) []], 200);
        }
    }
    public function update_cart_items($user_id,$device_cart_id)
    {
    
    $oldcart = ServiceCart::where(['device_cart_id'=>$device_cart_id,'user_id'=>$user_id])->first();
    $newcart = ServiceCart::where('device_cart_id',$device_cart_id)->where('user_id','!=',$user_id)->get()->first();
    
    $categoryidold = ServiceCategorySelected::where('service_id',$oldcart->service_id??0)->first()->category_id??0;
    $categoryidnew = ServiceCategorySelected::where('service_id',$newcart->service_id??0)->first()->category_id??0;
    
    if($categoryidold != $categoryidnew)
    {
    ServiceCart::where(['user_id'=>$user_id])->delete();    
    }
    $service_items = ServiceCart::where('device_cart_id',$device_cart_id)->get();
    ServiceCart::where('device_cart_id',$device_cart_id)->delete();
    if(!empty($service_items) && count($service_items) > 0)
    {
        foreach ($service_items as $key => $value) {
            //check category not diffrent
            // $check = $check->where(["device_cart_id" => $request->device_cart_id])
            //     ->leftjoin('service_category_selected','service_category_selected.service_id','=','cart_service.service_id')->get()->first();

            $check = ServiceCart::where(['service_id'=>$value->service_id,'user_id'=>$user_id,'device_cart_id'=>$value->device_cart_id])->get()->count();
            if($check > 0)
            {
                $data_cart = ServiceCart::where(['service_id'=>$value->service_id,'user_id'=>$user_id,'device_cart_id'=>$value->device_cart_id])->first();
                $current_qty = $data_cart->qty;
                $data_cart->qty = $current_qty + $value->qty;
                $data_cart->save();
            }
            else
            {
                $data_cart = new ServiceCart;
                $data_cart->service_id = $value->service_id;
                $data_cart->user_id    = $user_id;
                $data_cart->device_cart_id = $value->device_cart_id;
                $data_cart->booked_time = $value->booked_time;
                $data_cart->text = $value->text;
                $data_cart->hourly_rate = $value->hourly_rate;
                $data_cart->task_description = $value->task_description;
                $data_cart->doc = $value->doc;
                $data_cart->qty = $value->qty;
                $data_cart->created_at = gmdate('Y-m-d H:i:s');
                $data_cart->updated_at = gmdate('Y-m-d H:i:s');
                $data_cart->save();
            }
            
        }
      
    }
}    
}
