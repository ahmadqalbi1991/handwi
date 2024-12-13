<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserReffer;
use App\Models\UserTable;
use App\Models\UserTemp;
use App\Services\JWTService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_name' => 'required|string|max:100',
                'user_last_name' => 'required|string|max:100',
                'user_gender' => 'required|numeric',
                'user_email' => 'required|email|max:100',
                'user_password' => 'required|string|min:8|max:20',
                'user_country' => 'required|numeric',
                'user_dial_code' => 'required|string',
                'user_phone' => 'required|string|min:6|max:12',
                'user_device_token' => 'required|string|max:500',
                'user_device_type' => 'required|string|max:20',
            ], [
                'user_name.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_name')]),
                'user_last_name.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_last_name')]),
                'user_gender.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_gender')]),
                'user_email.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_email')]),
                'user_email.email' => __('messages.validation.email', ['field' => __('messages.common_messages.user_email')]),
                'user_email.unique' => __('messages.validation.unique', ['field' => __('messages.common_messages.user_email')]),
                'user_password.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_password')]),
                'user_password.min' => __('messages.validation.min_length', [
                    'field' => __('messages.common_messages.user_password'),
                    'min_length' => 8]),
                'user_password.max' => __('messages.validation.max_length', [
                    'field' => __('messages.common_messages.user_password'),
                    'max_length' => 20]),
                'user_country.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_country')]),
                'user_dial_code.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_dial_code')]),
                'user_phone.min' => __('messages.validation.min_length', [
                    'field' => __('messages.common_messages.user_phone'),
                    'min_length' => 6]),
                'user_phone.max' => __('messages.validation.max_length', [
                    'field' => __('messages.common_messages.user_phone'),
                    'max_length' => 12]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }
            
            $temp_user = UserTemp::where(['user_email_id' => $request->user_email, 'otp_verified' => true])->first();
            if ($temp_user) {
                return return_response('0', 200, __('messages.validation.unique', ['field' => __('messages.common_messages.user_email')]));
            }

            $password = $request->user_password;
            $invitation_code = $request->invitation_code;
            $invited_user_row =UserTable::getUserByEmail(["referal_code" => $invitation_code]);
            $referral_code = time() + rand(111111, 999999);
//            $otp = rand(1111, 9999);
            $otp = 1111;

            $i_data['user_first_name'] = trim($request->user_name);
            $i_data['user_middle_name'] = trim($request->user_middle_name);
            $i_data['user_last_name'] = trim($request->user_last_name);
            $i_data['user_gender'] = trim($request->user_gender);
            $i_data['user_email_id'] = trim($request->user_email);
            $i_data['user_password'] = bcrypt($password);
            $i_data['user_country_id'] = trim($request->user_country);
            $i_data['phone_number'] = trim($request->user_phone);
            $i_data['dial_code'] = trim($request->user_dial_code);
            $i_data['user_type'] = "U";
            $i_data['referal_code'] = (string) $referral_code;
            $i_data['invited_user_id'] = "0";
            $i_data['user_status'] = "1";
            $i_data['user_created_by'] = "0";
            $i_data['user_created_date'] = gmdate("Y-m-d H:i:s");
            $i_data['user_phone_otp'] = (string) $otp;
            $i_data['mazouz_customer'] = 1;
            $i_data['firebase_user_key'] = 'Maz' . time() . $referral_code;

            if ($invited_user_row)
                $i_data['invited_user_id'] = (string) $invited_user_row->user_id;

            $user_id = UserTemp::createUserTemp($i_data);

            if ($invited_user_row) {
               UserTable::updateUser(["user_points" => ($invited_user_row->user_points + 1)], $invited_user_row->user_id);
               UserReffer::createUserReferrer(["user_id" => $user_id, "refered_user_id" => $invited_user_row->user_id, "points_earned" => 1]);
            }


            if ($user_id > 0) {
                $payload = json_encode([
                    "temp_user_id" => $user_id,
                    "user_email" => $i_data['user_email_id'],
                ]);
                $access_token = encrypt($payload . env('APP_KEY'));

                $u_data = array(
                    'user_device_token' => trim($request->user_device_token),
                    'user_device_type' => trim($request->user_device_type),
                    'user_last_login' => gmdate('Y-m-d H:i:s'),
                    "user_access_token" => md5($access_token),
                    "login_type" => 'N'
                );

                $user = UserTemp::updateUserTemp($u_data, $user_id);

                if ($user) {
                    $status = "1";
                    $message = __('messages.success.phone_otp');
                    $i_data['user_id'] = (string) $user_id;
                } else {
                    $status = "3";
                    $message = __('messages.errors.reg_fails');
                }
            } else {
                $status = "3";
                $message = __('messages.errors.reg_fails');
            }

            return  return_response($status, 200, $message, [], $i_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }
}
