<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Models\CartModel;
use App\Models\CategoryModel;
use App\Models\CountryModel;
use App\Models\LoginInfo;
use App\Models\MyShop;
use App\Models\PasswordReset;
use App\Models\SpinnerModel;
use App\Models\TicketModel;
use App\Models\User;
use App\Models\UserTable;
use App\Models\UserTemp;
use App\Services\JWTService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Auth, Storage;
use Kreait\Firebase\Contract\Database;

class UserController extends Controller
{

    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    public function getMyTickets(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $page = ($request->page) ? $request->page : 1;
            $limit = ($request->limit) ? $request->limit : 10;
            $offset = ($page - 1) * $limit;
            $tickets = TicketModel::getMyTickets($user->user_id, $limit, $offset, $request->status);
            $list = [];
            foreach ($tickets as $key => $ticket) {
                $arrayTicket = (array)$ticket; // Convert stdClass to array
                $arrayTicket['purchase_date'] = Carbon::parse($arrayTicket['order_placed_date'])->format('d M Y h:i A');
                $arrayTicket['campaigns_draw_date'] = Carbon::parse($arrayTicket['campaigns_draw_date'])->format('d M Y h:i A');
                $list[] = convertNumbersToStrings($arrayTicket);
            }
            $o_data['tickets'] = $list;

            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getShippingAddress(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $address_data = UserTable::getUserAddress($user->user_id);
            $user_address_list = [];
            foreach ($address_data as $row) {

                $address_row["shiping_details_id"] = (string)$row->user_shiping_details_id;
                $address_row["first_name"] = (string)$row->s_first_name;
                $address_row["middle_name"] = (string)$row->s_middle_name;
                $address_row["last_name"] = (string)$row->s_last_name;
                $address_row["cutomer_name"] = (string)$row->s_first_name . " " . (string)$row->s_last_name;
                $address_row["street_name"] = (string)$row->user_shiping_details_street;
                $address_row["building_name"] = (string)$row->user_shiping_details_building;
                $address_row["floor_no"] = (string)$row->user_shiping_details_floorno;
                $address_row["flat_no"] = (string)$row->user_shiping_details_flatno;
                $address_row["location"] = (string)$row->user_shiping_details_loc;
                $address_row["land_mark"] = (string)$row->user_shiping_details_landmark;
                if ($row->user_shiping_details_city == '-1') {
                    $address_row["city_name"] = (string)$row->user_shiping_details_other_city;
                } else {
                    $address_row["city_name"] = (string)$row->city_name;
                }
                $address_row["country_name"] = (string)$row->country_name;
                $address_row["dial_code"] = (string)$row->user_shiping_details_dial_code;
                $address_row["phone_no"] = (string)$row->user_shiping_details_phone;
                $address_row["default_address"] = (string)$row->default_address_status;
                $address_row["address_type"] = (string)$row->user_shiping_details_loc_type;
                $address_row["city_id"] = (string)$row->user_shiping_details_city;
                $address_row["country_id"] = (string)$row->user_shiping_country_id;
                $address_row["latitude"] = (string)$row->user_shiping_details_latitude;
                $address_row["longitude"] = (string)$row->user_shiping_details_longitude;

                $user_address_list[] = $address_row;
            }

            $o_data["userAddress"] = $user_address_list;
            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function createPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => [
                    'required',
                    'confirmed', // Adds the password confirmation check
                    'min:8', // Ensures minimum length of 8 characters
                    'regex:/[a-z]/', // At least one lowercase letter
                    'regex:/[A-Z]/', // At least one uppercase letter
                    'regex:/[0-9]/', // At least one number
                    'regex:/[@$!%*#?&]/' // At least one special character
                ],
                'password_confirmation' => 'required' // Field for password confirmation
            ], [
                'email.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_email')]),
                'password.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_password')]),
                'password.confirmed' => __('messages.validation.password_mismatch'),
                'password.min' => __('messages.validation.min_length', ['field' => __('messages.common_messages.user_password'), 'min_length' => 8]),
                'password.regex' => __('messages.validation.password_strength'),
                'password_confirmation.required' => __('messages.validation.required', ['field' => __('messages.common_messages.password_confirmation')]),
                'email.email' => __('messages.validation.email', ['field' => __('messages.common_messages.user_email')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user = UserTable::where(['user_id' => $request->user_id, 'user_email_id' => $request->email])->first();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $user->password = bcrypt($request->password);
            $user->save();

            return return_response('1', 200, __('messages.errors.password_created_successfully'));
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function checkExistingEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ], [
                'email.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_email')]),
                'email.email' => __('messages.validation.email', ['field' => __('messages.common_messages.user_email')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user = UserTable::where('user_email_id', $request->email)->count();
            if ($user) {
                $status = '1';
                $o_data['exists'] = '1';
                $message = __('messages.success.user_found');
                $code = 200;
            } else {
                $status = '1';
                $o_data['exists'] = '0';
                $message = __('messages.success.user_found');
                $code = 200;
            }

            return return_response($status, $code, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getUserInfo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_email' => 'required|email',
            ], [
                'user_email.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_email')]),
                'user_email.email' => __('messages.validation.email', ['field' => __('messages.common_messages.user_email')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user_data = UserTable::where(['user_email_id' => $request->user_email])->first();
            if (empty($user_data)) {
                return return_response('0', 200, __('messages.errors.no_user_found'), []);
            }

            $o_data['user_full_name'] = (string)$user_data->user_first_name;
            $o_data['user_middle_name'] = (string)$user_data->user_middle_name;
            $o_data['user_last_name'] = (string)$user_data->user_last_name;
            $o_data['user_email'] = (string)$user_data->user_email_id;
            $o_data['user_type'] = (string)$user_data->user_type;
            $o_data['user_gender'] = (string)$user_data->user_gender;

            $user_image = public_path("uploads/user/" . $user_data->image);

            if (!(file_exists($user_image) && is_file($user_image)))
                $user_image = asset('images/user_dummy.png');

            $o_data['user_image'] = $user_image;
            $o_data['login_type'] = "N";
            $o_data['user_country_id'] = (string)$user_data->user_country_id;
            $o_data['country_name'] = (string)$user_data->country_name;
            $o_data['referal_code'] = (string)$user_data->referal_code;
            $o_data['dial_code'] = (string)$user_data->dial_code;
            $o_data['phone_number'] = (string)$user_data->phone_number;
            $o_data['firebase_user_key'] = (string)$user_data->firebase_user_key;
            $o_data['points'] = (string)($user_data->user_points - $user_data->used_points);
            $flag = CountryModel::where(["country_language_code" => 1, "country_id" => $user_data->user_country_id, "country_status" => 1])->first();

            $flag_name = "";
            if ($flag) {
                $flag_name = asset("uploads/flags/" . $flag->dial_code . ".png");
            }

            $o_data['flag'] = $flag_name;
            $shop_product_exist = MyShop::where('user_id', $user_data->user_id)->count();
            $o_data['shop_product_exist'] = $shop_product_exist;
            $o_data['shop_share_link'] = '';
            if ($shop_product_exist) {
                $o_data['shop_share_link'] = url('/') . 'share/my_shop/' . encryptor($user_data->user_id);
            }
            $status = "1";

            return return_response($status, 200, '', [], convertNumbersToStrings($user_data->toArray()));
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_name' => 'required|string|max:100',
                'user_last_name' => 'required|string|max:100',
                'user_country' => 'required|string',
                'user_gender' => 'required|numeric|min:0|max:1'
            ], [
                'user_name.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_name')]),
                'user_name.email' => __('messages.validation.email', ['field' => __('messages.common_messages.user_name')]),
                'user_last_name.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_last_name')]),
                'user_last_name.email' => __('messages.validation.email', ['field' => __('messages.common_messages.user_last_name')]),
                'user_country.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_country')]),
                'user_gender.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_gender')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $i_data = [
                'user_first_name' => trim($request->input('user_name', '')),
                'user_last_name' => trim($request->input('user_last_name', '')),
                'user_country_id' => trim($request->input('user_country', '')),
                'user_gender' => trim($request->input('user_gender', '')),
                'dob' => $request->dob
            ];

            if ($request->hasFile('user_image')) {
                $file = $request->file('user_image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $s3Path = 'users/' . $fileName;
                $filePath = \Storage::disk('s3')->put($s3Path, file_get_contents($file));
                $i_data['image'] = \Storage::disk('s3')->url($s3Path);
            }

            $userAddress = trim($request->input('user_address', ''));
            if (!empty($userAddress)) {
                $i_data['user_address'] = $userAddress;
            }

            UserTable::where('user_id', $user->user_id)->update($i_data);
            $user = UserTable::where('user_id', $user->user_id)->first();
            $user->dob = $user->dob ? Carbon::parse($user->dob)->format('d M Y') : '';
            return return_response('1', 200, __('messages.success.user_update_success'), [], convertNumbersToStrings($user->toArray()));
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function addShippingAddress(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'location' => 'required|string',
                'address_type' => 'nullable|numeric',
                'city' => 'required|numeric',
                'country_id' => 'required|numeric',
                'dial_code' => 'required|string',
                'user_phone' => 'required',
                'default_address' => 'required|numeric',
            ], [
                'first_name.required' => __('messages.validation.required', ['field' => __('messages.common_messages.first_name')]),
                'last_name.required' => __('messages.validation.required', ['field' => __('messages.common_messages.last_name')]),
                'location.required' => __('messages.validation.required', ['field' => __('messages.common_messages.location')]),
                'city.required' => __('messages.validation.required', ['field' => __('messages.common_messages.city')]),
                'country_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.country_id')]),
                'dial_code.required' => __('messages.validation.required', ['field' => __('messages.common_messages.dial_code')]),
                'user_phone.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_phone')]),
                'default_address.required' => __('messages.validation.required', ['field' => __('messages.common_messages.default_address')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $i_data['first_name'] = trim($request->input('first_name', ''));
            $i_data['middle_name'] = trim($request->input('middle_name', ''));
            $i_data['last_name'] = trim($request->input('last_name', ''));
            $i_data['user_shiping_details_floorno'] = trim($request->input('floor_no', ''));
            $i_data['user_shiping_details_flatno'] = trim($request->input('flat_no', ''));
            $i_data['user_shiping_details_building'] = trim($request->input('building_name', ''));
            $i_data['user_shiping_details_street'] = trim($request->input('street_name', ''));
            $i_data['user_shiping_details_loc'] = trim($request->input('location', ''));
            $i_data['user_shiping_details_landmark'] = trim($request->input('land_mark', ''));
            $i_data['user_shiping_zipcode'] = trim($request->input('zipcode', ''));
            $i_data['user_shiping_details_city'] = (int)trim($request->input('city', 0));
            $i_data['user_shiping_country_id'] = (int)trim($request->input('country_id', 0));
            $i_data['user_shiping_details_dial_code'] = trim($request->input('dial_code', ''));
            $i_data['user_shiping_details_phone'] = trim($request->input('user_phone', ''));
            $i_data['user_shiping_details_user_id'] = $user->user_id;
            $i_data['user_shiping_details_latitude'] = (string)trim($request->input('latitude', ''));
            $i_data['user_shiping_details_longitude'] = (string)trim($request->input('longitude', ''));
            $i_data['user_shiping_details_loc_type'] = (int)trim($request->input('address_type', 0));
            $i_data['user_shiping_details_other_city'] = (string)trim($request->input('other_city', ''));
            $default_address = (int)$request->input('default_address', 0);

            if ($default_address == 1)
                UserTable::updateUserAddress(["default_address_status" => 0], ["user_shiping_details_user_id" => $user->user_id]);

            $i_data["default_address_status"] = ($default_address > 0) ? 1 : 0;
            if ($request->has('address_id')) {
                $message = __('messages.success.address_updated');
                UserTable::updateUserAddress($i_data, ["user_shiping_details_user_id" => $user->user_id, "user_shiping_details_id" => $request->address_id]);
            } else {
                $message = __('messages.success.address_added');
                UserTable::createUserAddress($i_data);
            }
            $address_data = UserTable::getUserAddress($user->user_id);

            foreach ($address_data as $row) {
                $address_row["shiping_details_id"] = (string)$row->user_shiping_details_id;
                $address_row["first_name"] = (string)$row->s_first_name;
                $address_row["middle_name"] = (string)$row->s_middle_name;
                $address_row["last_name"] = (string)$row->s_last_name;
                $address_row["cutomer_name"] = (string)$row->s_first_name . " " . (string)$row->s_last_name;
                $address_row["street_name"] = (string)$row->user_shiping_details_street;
                $address_row["building_name"] = (string)$row->user_shiping_details_building;
                $address_row["floor_no"] = (string)$row->user_shiping_details_floorno;
                $address_row["flat_no"] = (string)$row->user_shiping_details_flatno;
                $address_row["location"] = (string)$row->user_shiping_details_loc;
                $address_row["land_mark"] = (string)$row->user_shiping_details_landmark;
                if ($row->user_shiping_details_city == '-1') {
                    $address_row["city_name"] = (string)$row->user_shiping_details_other_city;
                } else {
                    $address_row["city_name"] = (string)$row->city_name;
                }
                $address_row["country_name"] = (string)$row->country_name;
                $address_row["dial_code"] = (string)$row->user_shiping_details_dial_code;
                $address_row["phone_no"] = (string)$row->user_shiping_details_phone;
                $address_row["default_address"] = (string)$row->default_address_status;
                $address_row["address_type"] = (string)$row->user_shiping_details_loc_type;
                $address_row["city_id"] = (string)$row->user_shiping_details_city;
                $address_row["country_id"] = (string)$row->user_shiping_country_id;
                $address_row["latitude"] = (string)$row->user_shiping_details_latitude;
                $address_row["longitude"] = (string)$row->user_shiping_details_longitude;

                $user_address_list[] = $address_row;
            }

            $o_data["userAddress"] = $user_address_list;

            return return_response('1', 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getMySpinners(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $limit_query = "";
            if ($request->limit > 0) {
                $limit_query = " limit {$request->offset}, {$request->limit}";
            }

            $o_data['Spinner_list'] = SpinnerModel::getMyAllSpinners($user->user_id, 1, '', '', $limit_query);
            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function saveSpinnerResult(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'spinner_his_id' => 'required|integer',
                'prize' => 'required|string',
            ], [
                'spinner_his_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.spinner_his_id')]),
                'prize.required' => __('messages.validation.required', ['field' => __('messages.common_messages.prize')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $spinner_his_id = $request->input('spinner_his_id');
            $prize = $request->input('prize');
            $spinner_price_id = $request->input('spinner_price_id');

            $get_prize = SpinnerModel::where(['spinner_price_id' => $spinner_price_id, 'spinner_language_code' => 1])->first();
            $extra_ticket = 0;
            if (!empty($get_prize)) {
                $extra_ticket = $get_prize->extra_ticket_count;
            }

            $spinner_details = SpinnerModel::getSpinnerDetails(['spinner_his_id' => $spinner_his_id, 'spinner_status' => 1]);
            if (!empty($spinner_details)) {
                $get_alloted = SpinnerModel::getAlloted('*', ['order_block_id' => $spinner_details->order_block_id, 'history_id' => $spinner_details->history_id]);
                $insert_data = array();
                $insert_data['spinner_status'] = 2;
                $insert_data['prize'] = $prize;
                $insert_data['spinner_redeemdate'] = gmdate("Y-m-d");
                SpinnerModel::updateWhere('user_spinner_history', array('spinner_his_id' => $spinner_his_id), $insert_data);

                $get_product_from_history = SpinnerModel::getAlloted(['product_id', 'product_attribute_id'], ['history_id' => $spinner_details->history_id]);
                $product_id = 0;
                $product_attribute_id = 0;
                if ($get_product_from_history) {
                    $product_id = $get_product_from_history->product_id;
                    $product_attribute_id = $get_product_from_history->product_attribute_id;
                }
                $time_val = time();
                $product_tickets = [];
                if ($extra_ticket > 0) {
                    $alloted_count = $get_alloted->count;
                    for ($i = 0; $i < $extra_ticket; $i++) {
                        $alloted_count++;
                        $ticket_number = $time_val . $spinner_details->history_id . $alloted_count;
                        $product_tickets[] = [
                            "order_block_id" => $spinner_details->order_block_id,
                            "history_id" => $spinner_details->history_id,
                            "product_id" => $product_id,
                            "product_attribute_id" => $product_attribute_id,
                            "ticket_number" => $ticket_number
                        ];

                        $draw_slip_tickets[] = [
                            "user_id" => $spinner_details->spinner_user_id,
                            "order_block_id" => $spinner_details->order_block_id,
                            "campaign_id" => $spinner_details->campaign_id,
                            "product_attribute_id" => $product_attribute_id,
                            "draw_slip_number" => $ticket_number
                        ];
                    }
                    CartModel::createBatchOrderTicketNumber($product_tickets);
                    CartModel::createBatchDrawSlip($draw_slip_tickets);
                }
            } else {
                return return_response('0', 200, __('messages.errors.spinner_already_used'));
            }

            return return_response('1', 200, __('messages.success.point_success_got', ['points' => $prize]));
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getFavourites(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $favourite_products_data = UserTable::getFavourites($user->user_id, 1);
            $favourite_products_list = [];
            foreach ($favourite_products_data as $row) {
                $favourite_products_list[] = process_product_data_v2($row, 1);
            }

            $o_data["products"] = $favourite_products_list;

            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            dd($exception);
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function resendOtp(Request $request)
    {
        try {
            $user = UserTemp::where('user_id', $request->user_id)->first();
            if (empty($user)) {
                $status = '0';
                $message = __('messages.errors.session_expired');
            } else {
//                    $i_data['user_phone_otp'] = rand(1111, 9999);
                $i_data['user_phone_otp'] = 1111;
                UserTemp::where('user_id', $user->user_id)->update($i_data);

                $status = '1';
                $message = __('messages.success.phone_otp');
            }

            return return_response($status, 200, $message, [], convertNumbersToStrings($user->toArray()));
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getTickets(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $product_categories_data = CategoryModel::where('category_language_code', 1)->get();
            $limit = (int)$request->input('limit', 0);
            $offset = (int)$request->input('offset', 0);
            $category_id = (int)$request->input('category_id', 0);
            $tickets_list = [];

            $limit_query = '';
            if ($limit > 0) {
                $limit_query = "LIMIT {$offset}, {$limit}";
            }

            if ($category_id > 0)
                $product_order_list_data = TicketModel::getTicketsByOrderProductCategoryAll($user->user_id, $category_id, 1, "", "", $limit_query);
            else
                $product_order_list_data = TicketModel::getTicketsByOrderProductAll($user->user_id, 1, "", "", $limit_query);

            foreach ($product_order_list_data as $row) {

                //$processed_product_order_data = process_product_data($row, $this->lang_code);
                $product_image = '';
                $campaigns_home_image = '';
                $productImagePath = public_path('uploads/products/' . $row->campaigns_image);
                $defaultImage = url('images/dummy.jpg');

                if (file_exists($productImagePath) && is_file($productImagePath)) {
                    $productImage = url('uploads/products/' . $row->campaigns_image);
                } else {
                    $productImage = $defaultImage;
                }

                $campaignsHomeImagePath = public_path('uploads/products/' . $row->campaigns_image2);
                if (file_exists($campaignsHomeImagePath) && is_file($campaignsHomeImagePath)) {
                    $campaignsHomeImage = url('uploads/products/' . $row->campaigns_image2);
                } else {
                    $campaignsHomeImage = $defaultImage;
                }

                $processed_product_order_data["m_product_image"] = (string)$product_image;
                $processed_product_order_data["campaigns_home_image"] = (string)$campaigns_home_image;
                $processed_product_order_data["product_name"] = (string)$row->product_name;
                $processed_product_order_data["campaigns_title"] = (string)$row->campaigns_title;
                $processed_product_order_data["campaigns_id"] = (string)$row->campaigns_id;
                $processed_product_order_data["product_id"] = (string)$row->product_id;
                $processed_product_order_data["product_attribute_id"] = (string)$row->product_attribute_id;
                $processed_product_order_data["purchased_on"] = (string)get_date_in_timezone(USERTIMEZONE, gmdate("d-m-Y H:i:s", strtotime($row->order_placed_date)), "d M Y h:i A");
                $processed_product_order_data["draw_date"] = "";
                $processed_product_order_data["won_ticket_number"] = "";
                $processed_product_order_data["is_user_won_campaign"] = "0";
                $processed_product_order_data["campaigns_end_date"] = (string)get_date_in_timezone(USERTIMEZONE, $row->campaigns_date . " " . $row->campaigns_time, "d M Y h:i A");
                $ticket_info = TicketModel::getTicketCount($row->ticket_number);
                $processed_product_order_data["ticket_number"] = (string)$row->ticket_number;
                $processed_product_order_data["ticket_count"] = (string)$ticket_info;

                $tickets_list[] = $processed_product_order_data;
            }

            $o_data["ticketsList"] = $tickets_list;
            $o_data["categories"] = $product_categories_data;

            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function deleteShippingAddress(Request $request)
    {
        try {
            $user_address_list = [];
            $language = $request->has('language') ? $request->language : 1;
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $validator = Validator::make($request->all(), [
                'address_id' => 'required|numeric'
            ], [
                'address_id.required' => __('messages.validation.required', ['field' => 'messages.common_messages.address_id'])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $is_shipping_valid = UserTable::checkShippingExists($request->address_id);
            if ($is_shipping_valid) {
                $status = '3';
                $message = __('messages.errors.shipping_address_cannot_be_delete');
                $o_data = [];
            } else {
                UserTable::deleteUserAddress($request->address_id);
                $address_data = UserTable::getUserAddress($user->user_id, $language);
                foreach ($address_data as $row) {
                    $address_row["shiping_details_id"] = (string)$row->user_shiping_details_id;
                    $address_row["first_name"] = (string)$row->s_first_name;
                    $address_row["last_name"] = (string)$row->s_last_name;
                    $address_row["cutomer_name"] = (string)$row->s_first_name . " " . (string)$row->s_last_name;
                    $address_row["street_name"] = (string)$row->user_shiping_details_street;
                    $address_row["building_name"] = (string)$row->user_shiping_details_building;
                    $address_row["floor_no"] = (string)$row->user_shiping_details_floorno;
                    $address_row["flat_no"] = (string)$row->user_shiping_details_flatno;
                    $address_row["location"] = (string)$row->user_shiping_details_loc;
                    $address_row["land_mark"] = (string)$row->user_shiping_details_landmark;
                    $address_row["city_name"] = (string)$row->city_name;
                    $address_row["country_name"] = (string)$row->country_name;
                    $address_row["dial_code"] = (string)$row->user_shiping_details_dial_code;
                    $address_row["phone_no"] = (string)$row->user_shiping_details_phone;
                    $address_row["default_address"] = (string)$row->default_address_status;
                    $address_row["address_type"] = (string)$row->user_shiping_details_loc_type;
                    $address_row["city_id"] = (string)$row->user_shiping_details_city;
                    $address_row["country_id"] = (string)$row->user_shiping_country_id;
                    $address_row["latitude"] = (string)$row->user_shiping_details_latitude;
                    $address_row["longitude"] = (string)$row->user_shiping_details_longitude;

                    $user_address_list[] = $address_row;
                }

                $o_data["userAddress"] = $user_address_list;

                $status = "1";
                $message = '';
            }

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $validators = Validator::make($request->all(), [
                'user_id' => 'required',
                'otp' => 'required|numeric'
            ], [
                'user_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_id')]),
                'otp.required' => __('messages.validation.required', ['field' => __('messages.common_messages.otp')]),
                'otp.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.otp')]),
            ]);

            if ($validators->fails()) {
                return return_response('0', 200, '', $validators->errors());
            }

            $user_temp = UserTemp::where('user_id', $request->user_id)->first();
            if ($user_temp) {
                if ($user_temp->user_phone_otp == $request->otp) {
                    $user = UserTable::where([
                        'user_email_id' => $user_temp->user_email_id,
                        'dial_code' => $user_temp->dial_code,
                        'phone_number' => $user_temp->phone_number,
                        'phone_verified' => 1
                    ])->first();

                    if (!empty($user)) {
                        return return_response('0', 200, __('messages.errors.user_is_already_register_with_email_phone'));
                    }

                    $user_temp->otp_verified = true;
                    $user_temp->save();
                    $i_data = $user_temp->toArray();
                    $i_data['user_status'] = 1;
                    $i_data['mazouz_customer'] = 1;
                    $i_data['phone_verified'] = 1;
                    $i_data['user_created_date'] = gmdate("Y-m-d H:i:s");
                    unset($i_data['user_id']);

                    $user = UserTable::create($i_data);
                    Auth::setUser($user);
                    $user_data = Auth::user();
                    $access_token = $user_data->createToken('API Token')->accessToken;
                    $user_data->user_access_token = $access_token;
                    LoginInfo::create([
                        'user_id' => $user_data->user_id,
                        'auth_token' => $access_token
                    ]);
                    if (!empty($user)) {
                        $u_data = [
                            'user_last_login' => gmdate('Y-m-d H:i:s'),
                        ];

                        $user_update = UserTable::where('user_id', $user->user_id)->update($u_data);

                        if ($user_update) {
                            process_user_cart_data($user->user_id, $request->device_cart_id);
                            $status = "1";
                            $message = __("messages.success.signup_success");

                            $subject = __("messages.common_messages.user_welcome");
                            $data = [
                                'user_name' => $user->user_first_name . " " . $user->user_last_name,
                                'message' => __('messages.common_messages.signup_messages')
                            ];
                            $view = 'emails.signup';

                            Mail::to($user->user_email_id)->send(new SendMail($data, $subject, $view));
                        } else {
                            $status = "3";
                            $message = __("messages.errors.user_signup_failed");
                            $access_token = "";
                        }

                        $oData['user'] = convertNumbersToStrings($user_data->toArray());

                        return return_response($status, 200, $message, [], $oData);
                    } else {
                        return return_response('0', 200, __('messages.errors.invalid_otp'));
                    }
                } else {
                    return return_response('0', 200, __('messages.errors.invalid_otp'));
                }
            } else {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }
        } catch (\Exception $exception) {
            dd($exception);
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function resetPasswordVerifyOtp(Request $request)
    {
        try {
            $validators = Validator::make($request->all(), [
                'password_reset_token' => 'required',
                'otp' => 'required|numeric'
            ], [
                'user_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_id')]),
                'otp.required' => __('messages.validation.required', ['field' => __('messages.common_messages.otp')]),
                'otp.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.otp')]),
            ]);

            if ($validators->fails()) {
                return return_response('0', 200, '', $validators->errors());
            }

            $user_reset_passwprd_object = PasswordReset::where('reset_code', $request->password_reset_token)->first();
            if ($user_reset_passwprd_object) {
                $user_temp = UserTable::where('user_id', $user_reset_passwprd_object->user_id)->first();
                if ($user_temp) {
                    if ($user_temp->user_phone_otp == $request->otp) {
                        return return_response('1', 200, __('messages.success.otp_valid'), [], ['password_reset_code' => $request->password_reset_token, 'otp' => $request->otp]);
                    } else {
                        return return_response('0', 200, __('messages.errors.invalid_otp'));
                    }
                } else {
                    return return_response('0', 401, __('messages.errors.invalid_token'));
                }
            } else {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validators = Validator::make($request->all(), [
                'password_reset_token' => 'required',
                'otp' => 'required|numeric',
                'password' => [
                    'required',
                    'confirmed', // Adds the password confirmation check
                    'min:8', // Ensures minimum length of 8 characters
                    'regex:/[a-z]/', // At least one lowercase letter
                    'regex:/[A-Z]/', // At least one uppercase letter
                    'regex:/[0-9]/', // At least one number
                    'regex:/[@$!%*#?&]/' // At least one special character
                ],
                'password_confirmation' => 'required'
            ], [
                'user_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_id')]),
                'password.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_password')]),
                'password.confirmed' => __('messages.validation.password_mismatch'),
                'password.min' => __('messages.validation.min_length', ['field' => __('messages.common_messages.user_password'), 'min_length' => 8]),
                'password.regex' => __('messages.validation.password_strength'),
                'password_confirmation.required' => __('messages.validation.required', ['field' => __('messages.common_messages.password_confirmation')]),
                'otp.required' => __('messages.validation.required', ['field' => __('messages.common_messages.otp')]),
                'otp.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.otp')]),
            ]);

            if ($validators->fails()) {
                return return_response('0', 200, '', $validators->errors());
            }

            $user_reset_passwprd_object = PasswordReset::where('reset_code', $request->password_reset_token)->first();
            if ($user_reset_passwprd_object) {
                $user_temp = UserTable::where('user_id', $user_reset_passwprd_object->user_id)->first();
                if ($user_temp) {
                    if ($user_temp->user_phone_otp == $request->otp) {
                        $user_temp->user_password = bcrypt($request->password);
                        $user_temp->save();

                        return return_response('1', 200, __('messages.success.password_created_successfully'));
                    } else {
                        return return_response('0', 200, __('messages.errors.invalid_otp'));
                    }
                } else {
                    return return_response('0', 401, __('messages.errors.invalid_token'));
                }
            } else {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }
        } catch (\Exception $exception) {
            dd($exception);
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function updateDefaultShippingAddress(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'address_id' => 'required|numeric'
            ], [
                'address_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.address_id')]),
                'address_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.address_id')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $language = $request->has('language') ? $request->language : 1;
            UserTable::updateUserAddress(["default_address_status" => 0], ["user_shiping_details_user_id" => $user->user_id]);
            $status = UserTable::updateUserAddress(["default_address_status" => 1], ["user_shiping_details_id" => $request->address_id, 'user_shiping_details_user_id' => $user->user_id]);

            if ($status) {
                $address_data = UserTable::getUserAddress($user->user_id, $language);
                foreach ($address_data as $row) {
                    $address_row["shiping_details_id"] = (string)$row->user_shiping_details_id;
                    $address_row["first_name"] = (string)$row->s_first_name;
                    $address_row["last_name"] = (string)$row->s_last_name;
                    $address_row["cutomer_name"] = (string)$row->s_first_name . " " . (string)$row->s_last_name;
                    $address_row["street_name"] = (string)$row->user_shiping_details_street;
                    $address_row["building_name"] = (string)$row->user_shiping_details_building;
                    $address_row["floor_no"] = (string)$row->user_shiping_details_floorno;
                    $address_row["flat_no"] = (string)$row->user_shiping_details_flatno;
                    $address_row["location"] = (string)$row->user_shiping_details_loc;
                    $address_row["land_mark"] = (string)$row->user_shiping_details_landmark;
                    $address_row["city_name"] = (string)$row->city_name;
                    $address_row["country_name"] = (string)$row->country_name;
                    $address_row["dial_code"] = (string)$row->user_shiping_details_dial_code;
                    $address_row["phone_no"] = (string)$row->user_shiping_details_phone;
                    $address_row["default_address"] = (string)$row->default_address_status;
                    $address_row["address_type"] = (string)$row->user_shiping_details_loc_type;
                    $address_row["city_id"] = (string)$row->user_shiping_details_city;
                    $address_row["country_id"] = (string)$row->user_shiping_country_id;
                    $address_row["latitude"] = (string)$row->user_shiping_details_latitude;
                    $address_row["longitude"] = (string)$row->user_shiping_details_longitude;

                    $user_address_list[] = $address_row;
                }

                $o_data["userAddress"] = $user_address_list;
                return return_response('1', 200, __('messages.errors.address_updated'), [], $o_data);
            } else {
                return return_response('0', 200, __('messages.errors.no_address_found'));
            }
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function testNotification(Request $request){

        $title="test notification form web";
        $description = "Got assigned a new order";
        $notification_id = time();
        $ntype = 'new_order_assigned';
        $order_id=445566;
        $status=1;
        $notification_data["Notifications/testuser23/" . $notification_id] = [
            "title" => $title,
            "description" => $description,
            "notificationType" => $ntype,
            "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
            "orderId" => (string) $order_id,
            "status" => (string) $status,
            "url" => "",
            "imageURL" => '',
            "read" => "0",
            "seen" => "0",
        ];
        $this->database->getReference()->update($notification_data);
        $user_device_token='eQS-_QYITX2GBMke0NcO-D:APA91bGuyvS4WDvmjbFnLNNeRQvU6xE-BHufSYwYK8_VS9VrRWoqFLI9tvf-BO8OsxTg7jwLRVQXC6DtuHsuFZL1e0Sysi8ok85syFhqh_T_sHC42E37ldtZ9h7haNf9Z2TwE3ztI9hq';
        if (!empty($user_device_token)) {
           
            $res = send_single_notification(
                $user_device_token,
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
                    "status" => (string) $status,
                    "imageURL" => "",
                ]
            );
         
        }

    }
}
