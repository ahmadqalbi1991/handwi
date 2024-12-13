<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Models\CountriesModel;
use App\Models\CountryModel;
use App\Models\LoginInfo;
use App\Models\MyShop;
use App\Models\PasswordReset;
use App\Models\UserTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Models\UserTemp;
use App\Models\UserReffer;
use Illuminate\Support\Facades\Validator;
use Auth, Session;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_email' => 'required|email',
                'user_password' => 'required'
            ], [
                'user_email.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_email')]),
                'user_email.email' => __('messages.validation.email', ['field' => __('messages.common_messages.user_email')]),
                'user_password.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_password')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $email = $request->user_email;
            $password = $request->user_password;
            $device_cart_id = $request->device_cart_id;

            if (Auth::attempt(['user_email_id' => $email, 'password' => $password])) {
                $user_data = Auth::user();
                $access_token = $user_data->createToken('API Token')->accessToken;
                $user_data->user_access_token = $access_token;

                $i_data = array(
                    'user_device_token' => trim($request->user_device_token),
                    'user_device_type' => trim($request->user_device_type),
                    'user_last_login' => Carbon::now(),
                    "login_type" => 'N',
                    'mazouz_customer' => 1
                );

                UserTable::updateUser($i_data, $user_data->user_id);
                LoginInfo::create([
                    'user_id' => $user_data->user_id,
                    'auth_token' => $access_token
                ]);

                if (session()->has('country_id')) {
                    if ($user_data->country_id === session()->get('country_id')) {
                        process_user_cart_data($user_data->user_id, $device_cart_id);
                        $country = CountriesModel::where(['countries_id' => session()->get('country_id')])->first();
                    } else {
                        $country = CountriesModel::where(['countries_id' => $user_data->user_country_id])->first();
                        session(['country_id' => $country->id]);
                    }
                } else {
                    $country = CountriesModel::where(['countries_id' => $user_data->user_country_id])->first();
                    session(['country_id' => $country->countries_id]);
                }

                $status = '1';
                $message = __('messages.success.login_success');
                $user_data = convertNumbersToStrings($user_data->toArray());

                return return_response($status, 200, $message, [], $user_data);
            } else {
                $status = '0';
                $message = __('messages.errors.invalid_credentials');

                return return_response($status, 200, $message);
            }
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $token = $request->user()->token();
            $token->revoke();

            return return_response('1', 200, __('messages.success.logout'));
        } else {
            return return_response('0', 200, __('messages.errors.invalid_token'));
        }
    }

    public function social_login(Request $request)
    {

        $rules = [
            'user_email' => 'required|email',
            'user_device_token' => 'required',
            'user_device_type' => 'required',
        ];
        $messages = [
            'user_email.required' => trans('validation.email_required'),
            'user_email.email' => trans('validation.valid_email'),
            'user_device_token.required' => trans('validation.user_device_token'),
            'user_device_type.required' => trans('validation.user_device_type'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = trans('validation.validation_error_occured');
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'error' => (object)$errors,
            ], 200);
        }
        $user = UserTable::where('user_email_id', $request->user_email)->first();


        if ($user) {
            if (!$user->is_social) {
                return return_response('0', 200, __('messages.errors.please_login_with_normal_login'));
            }
            Auth::setUser($user);
            // $user = User::where('email', $request->email)->first();
            $user->user_device_token = $request->user_device_token;
            $user->user_device_type = $request->user_device_type;
            // $user->device_cart_id = $request->device_cart_id;
            $user->is_social = 1;
            $user->login_type = 'N';
            $user->mazouz_customer = 1;
            $user->save();

            $access_token = $user->createToken('API Token')->accessToken;
            LoginInfo::create([
                'user_id' => $user->user_id,
                'auth_token' => $access_token
            ]);
            $user->access_token = $access_token;

            $status = '1';
            $message = __('messages.success.login_success');
            $user_data = convertNumbersToStrings($user->toArray());

            return return_response($status, 200, $message, [], $user_data);

        } else {
            if ($request->user_phone && UserTable::where('phone_number', $request->user_phone)->where('dial_code', $request->user_dial_code)->first() != null) {
                return response()->json([
                    'status' => "0",
                    'error' => (object)array(),
                    'message' => trans('validation.phone_already_registered_please_login'),
                ], 201);
            }
            $validator = Validator::make($request->all(), [
                'user_name' => 'required|string|max:100',
                'user_last_name' => 'required|string|max:100',
                'user_gender' => 'required|numeric',
                'user_email' => 'required|email|max:100',
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
                return return_response('3', 200, '', $validator->errors());
            }
            $invitation_code = $request->invitation_code;
            $invited_user_row = UserTable::getUserByEmail(["referal_code" => $invitation_code]);
            $referral_code = time() + rand(111111, 999999);
//            $otp = rand(1111, 9999);
            $otp = 1111;

            $i_data['user_first_name'] = trim($request->user_name);
            $i_data['user_middle_name'] = trim($request->user_middle_name);
            $i_data['user_last_name'] = trim($request->user_last_name);
            $i_data['user_gender'] = trim($request->user_gender);
            $i_data['user_email_id'] = trim($request->user_email);
            $i_data['user_country_id'] = trim($request->user_country);
            $i_data['phone_number'] = trim($request->user_phone);
            $i_data['dial_code'] = trim($request->user_dial_code);
            $i_data['user_type'] = "U";
            $i_data['referal_code'] = (string)$referral_code;
            $i_data['invited_user_id'] = "0";
            $i_data['user_status'] = "1";
            $i_data['user_created_by'] = "0";
            $i_data['user_created_date'] = gmdate("Y-m-d H:i:s");
            $i_data['user_phone_otp'] = (string)$otp;
            $i_data['mazouz_customer'] = '1';
            $i_data['is_social'] = true;
            $i_data['firebase_user_key'] = 'Maz' . time() . $referral_code;

            if ($invited_user_row)
                $i_data['invited_user_id'] = (string)$invited_user_row->user_id;

            $user_id = UserTemp::createUserTemp($i_data);

            if ($invited_user_row) {
                UserTable::updateUser(["user_points" => ($invited_user_row->user_points + 1)], $invited_user_row->user_id);
                UserReffer::createUserReferrer(["user_id" => $user_id, "refered_user_id" => $invited_user_row->user_id, "points_earned" => 1]);
            }
            if ($user_id > 0) {
                $u_data = array(
                    'user_device_token' => trim($request->user_device_token),
                    'user_device_type' => trim($request->user_device_type),
                    'user_last_login' => gmdate('Y-m-d H:i:s'),
                    "login_type" => 'N'
                );

                $user = UserTemp::updateUserTemp($u_data, $user_id);

                if ($user) {
                    $status = "1";
                    $message = __('messages.success.phone_otp');
                    $i_data['user_id'] = (string)$user_id;
                } else {
                    $status = "3";
                    $message = __('messages.errors.reg_fails');
                }
            } else {
                $status = "3";
                $message = __('messages.errors.reg_fails');
            }

            return return_response($status, 200, $message, [], convertNumbersToStrings($i_data));

        }

    }

    public function forgetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_email' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^\+?[0-9]{7,15}$/', $value)) {
                            $fail(__('messages.validation.email_or_phone', ['field' => __('messages.common_messages.user_email')]));
                        }
                    }
                ],
            ], [
                'user_email.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_email')])
            ]);


            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $reset_code = '';
            $user_data = UserTable::where(function ($query) use ($request) {
                if (filter_var($request->user_email, FILTER_VALIDATE_EMAIL)) {
                    $query->where('user_email_id', $request->user_email);
                } else {
                    $query->whereRaw('CONCAT(dial_code, phone_number) = ?', [$request->user_email]);
                }
            })->first();
            if ($user_data) {
                if ($user_data->login_type == "S") {
                    $status = "3";
                    $message = __('messages.errors.user_pass_reset_social_fail');
                } else {
                    $user_data->user_phone_otp = 1111;
                    $user_data->save();
                    $i_data["user_id"] = $user_data->user_id;
                    $i_data["session_start_time"] = Carbon::now();
                    $current_date = strtotime($i_data['session_start_time']);
                    $future_date = $current_date + (60 * 5);
                    $format_date = date("Y-m-d H:i:s", $future_date);
                    $i_data['session_end_time'] = $format_date;
                    $reset_code = md5(Carbon::now()->format('d-M-Y h:i:s') . $user_data->user_id);
                    $i_data['reset_code'] = $reset_code;
                    PasswordReset::where(['user_id' => $user_data->user_id])->delete();
                    PasswordReset::create($i_data);

                    $status = "1";
                    if (filter_var($request->user_email, FILTER_VALIDATE_EMAIL)) {
                        $subject = 'Reset Password';
                        $data = [
                            'user_name' => $user_data->user_first_name,
                            'otp' => '1111'
                        ];
                        $view = 'emails.reset-password';
                        Mail::to($user_data->user_email_id)->send(new SendMail($data, $subject, $view));
                        $message = __('messages.success.password_reset_link_sent');
                    } else {
                        $message = __('messages.success.password_reset_link_sent_phone');
                    }
                }
            } else {
                $status = "3";
                $message = __('messages.errors.no_user_found');
            }

            return return_response($status, 200, $message, [], ['password_reset_code' => $reset_code]);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'user_old_password' => 'required|string|min:8|max:20',
                'user_new_password' => 'required|string|min:8|max:20',
            ], [
                'user_old_password.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_old_password')]),
                'user_old_password.min' => __('messages.validation.required', [
                    'field' => __('messages.common_messages.user_old_password'),
                    'min_length' => 8
                ]),
                'user_old_password.max' => __('messages.validation.required', [
                    'field' => __('messages.common_messages.user_old_password'),
                    'max_length' => 20
                ]),

                'user_new_password.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_new_password')]),
                'user_new_password.min' => __('messages.validation.required', [
                    'field' => __('messages.common_messages.user_new_password'),
                    'min_length' => 8
                ]),
                'user_new_password.max' => __('messages.validation.required', [
                    'field' => __('messages.common_messages.user_new_password'),
                    'max_length' => 20
                ]),
                'user_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_id')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user = UserTable::where('user_id', $request->user_id)->first();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            if ((Hash::check($request->user_old_password, $user->user_password)) || (password_verify($request->user_old_password, $user->user_password))) {
                if (!password_verify($request->user_new_password, $user->user_password)) {
                    $data = [
                        'user_password' => bcrypt($request->user_new_password)
                    ];
                    UserTable::where('user_id', $user->user_id)->update($data);

                    $message = __('messages.success.pwd_change_success');
                    $status = "1";
                    $subject = 'Change Password';
                    $data = [
                        'user_name' => $user->user_first_name,
                        'email' => 'ahmadqalbi1991@gmail.com',
                    ];
                    $view = 'emails.reset-password';

                    Mail::to($user->user_email_id)->send(new SendMail($data, $subject, $view));
                } else {
                    $status = '0';
                    $message = __('messages.errors.you_cannot_use_previous_password');
                }
            } else {
                $status = '0';
                $message = __('messages.errors.incorrect_password');
            }

            return return_response($status, 200, $message, []);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }
}
