<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\User;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;
use Kreait\Firebase\Contract\Database;

class ChangeMobileController extends Controller
{
   
    public function get_mobile_otp(Request $request)
    {
        $rules = [
            'access_token' => 'required',
            'mobile' => 'required',
            'dial_code' => 'required',
        ];
        $messages = [
            'access_token.required' => trans('validation.access_token_required'),
            'mobile.required' => trans('validation.mobile_required'),
            'dial_code.required' => trans('validation.dial_code_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $status = 0;
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $request->is_web ? $validator->messages()->first() :$message,
                'error' => (object) $errors,
            ], 200);
        }

        if (User::where('phone', $request->mobile)->first()) {
            return response()->json([
                'status' => "0",
                'error' => (object) array(),
                'message' => 'Phone is already exist.',
            ], 201);
        }
        if($request->user_id){
            $user = User::where(['id' => $request->user_id])->first();
        }else{
            $user = User::where(['user_access_token' => $request->access_token])->first();
        }

        if (!$user) {
            return response()->json([
                'status' => "0",
                'error' => (object) array(),
                'message' => trans('validation.invalid_access_token'),
            ], 201);
        }
        if ($user ) {           
            $user->temp_dial_code           = $request->dial_code;
            $user->temp_mobile              = $request->mobile;
            $user->user_phone_otp           = (string)get_otp();
            // $user->dial_code           = $request->dial_code;
            // $user->mobile              = $request->mobile;
            $user->phone_verified      = 0;
            $user->save();

            $name = $user->first_name . ' ' . $user->last_name;

            if (config('global.server_mode') == 'local') {
                \Artisan::call("send:send_verification_email --uri=" . urlencode("Verify your OTP") . " --uri2=" . urlencode($user->email) . " --uri3=" . $user->user_phone_otp . " --uri4=" . urlencode($name));
            } else {
                exec("php " . base_path() . "/artisan send:send_verification_email --uri=" . urlencode("Verify your OTP") . " --uri2=" . urlencode($user->email) . " --uri3=" . $user->user_phone_otp . " --uri4=" . urlencode($name) . " > /dev/null 2>&1 & ");
            }

            $message = "OTP has been send successfully on your mobile";
            return response()->json([
                'status' => "1",
                'error' => (object) array(),
                'message' => $message,
            ], 200);

        }
        return response()->json([
            'status' => "0",
            'error' => (object) array(),
            'message' => trans('validation.invalid_access_token'),
        ], 201);
    }

    public function resend_mobile_otp(Request $request)
    {
        $rules = [
            'access_token' => 'required',
        ];
        $messages = [
            'access_token.required' => trans('validation.access_token_required'),
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

        $user = User::where(['user_access_token' => $request->access_token])->first();
        if (!$user) {
            return response()->json([
                'status' => "0",
                'error' => (object) array(),
                'message' => trans('validation.invalid_access_token'),
            ], 201);
        }

        if($user){
            $user->user_phone_otp = (string)get_otp();
            $user->save();
            if($user->temp_dial_code && $user->temp_mobile){
                $message    = $user->user_phone_otp.'is verification code for change mobile request.';
                // $phone      = $user->temp_dial_code.$user->temp_mobile;
                // $sms_res    = send_SMS($message, $phone);

                $message = "OTP has been send successfully on your mobile";
                return response()->json([
                    'status' => "1",
                    'error' => (object) array(),
                    'message' => $message,
                ], 200);
            }
        }
        return response()->json([
            'status' => "0",
            'error' => (object) array(),
            'message' => 'Unable to send otp. Please try again.',
        ], 201);
    }
    
    public function change_mobile(Request $request)
    {
        $rules = [
            'access_token' => 'required',
            'otp' => 'required',
        ];
        $messages = [
            'access_token.required' => trans('validation.access_token_required'),
            'otp.required' => trans('validation.otp_required'),

        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $status = 0;
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $request->is_web ? $validator->messages()->first() :$message,
                'error' => (object) $errors,
            ], 200);
        }

        if($request->user_id){
            $user = User::where(['id' => $request->user_id])->first();
        }else{
            $user = User::where(['user_access_token' => $request->access_token])->first();
        }
        if (!$user) {
            return response()->json([
                'status' => "0",
                'error' => (object) array(),
                'message' => trans('validation.invalid_access_token'),
            ], 201);
        }
        if ($user && $user->user_phone_otp == $request->otp) {
            $user->dial_code        = $user->temp_dial_code;
            $user->phone           = $user->temp_mobile;
            $user->phone_verified   = 1;
            $user->user_phone_otp = null;
            $user->save();
            
            return response()->json([
                'status' => "1",
                'error' => (object) array(),
                'message' =>'Your mobile number has been updated.',

            ], 200);
        } 
        return response()->json([
            'status' => "0",
            'error' => (object) array(),
            'message' => 'Unable to verify otp. Please try again.',
        ], 201);
    }

}
