<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MyShop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MyShopController extends Controller
{
    public function deleteProductShop(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'shop_id' => 'required|numeric',
            ], [
                'shop_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.shop_id')]),
                'shop_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.shop_id')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $status = MyShop::where('shop_id', $request->shop_id)->delete();
            if ($status) {
                $status = '1';
                $message = __('messages.success.shop_deleted');
                $code = 200;
            } else {
                $status = '0';
                $message = __('messages.errors.something_went_wrong');
                $code = 500;
            }

            return return_response($status, $code, $message);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function deleteAllShopProducts(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $my_shop_deleted = MyShop::where('user_id', $user->user_id)->delete();
            if ($my_shop_deleted) {
                return return_response('1', 200, __('messages.success.cleared_all_the_products'));
            } else {
                return return_response('0', 500, __('messages.errors.something_went_wrong'));
            }
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function clearFavourites()
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $delete_status = MyShop::where('user_id', $user->user_id)->delete();
            if ($delete_status) {
                $status = '1';
                $message = __('messages.success.all_favourites_deleted');
                $code = 200;
            } else {
                $status = '0';
                $message = __('messages.errors.something_went_wrong');
                $code = 500;
            }

            return return_response($status, $code, $message);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function toggleShop(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $validator = Validator::make($request->all(), [
                'product_id' => 'required|numeric',
                'campaigns_id' => 'required|numeric',
            ], [
                'product_id.required' => __('messages.validation.required', ['field' => 'messages.common_messages.product_id']),
                'product_id.numeric' => __('messages.validation.numeric', ['field' => 'messages.common_messages.product_id']),
                'campaigns_id.required' => __('messages.validation.required', ['field' => 'messages.common_messages.campaign_id']),
                'campaigns_id.numeric' => __('messages.validation.numeric', ['field' => 'messages.common_messages.campaign_id']),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $product_id = (int)$request->input('product_id');
            $product_attribute_id = (int)$request->input('product_attribute_id');

            $favourite_row = MyShop::where([
                'product_id' => $product_id,
                'product_attribute_id' => $product_attribute_id
            ])->first();
            $code = $product_id . "#" . $product_attribute_id . "#" . $user->user_id;
            $code = encryptor($code);

            if (!empty($favourite_row)) {
                if (!empty($favourite_row->shop_id)) {
                    $favourite_row->delete();
                    $is_favourite = "0";
                    $message = __('messages.success.shop_deleted');
                } else {
                    $favourite_row = MyShop::create([
                        "user_id" => $user->user_id,
                        "product_id" => $product_id,
                        "product_attribute_id" => $product_attribute_id,
                        "added_time" => gmdate("Y-m-d H:i:s"),
                        "code" => $code
                    ]);

                    $is_favourite = '1';
                    $message = __('messages.success.added_to_favourites');
                }
            } else {
                $favourite_row = MyShop::create([
                    "user_id" => $user->user_id,
                    "product_id" => $product_id,
                    "product_attribute_id" => $product_attribute_id,
                    "added_time" => gmdate("Y-m-d H:i:s"),
                    "code" => $code
                ]);

                $is_favourite = '1';
                $message = __('messages.success.added_to_favourites');
            }

            $o_data['shop'] = $favourite_row;
            $o_data['is_favourite'] = $is_favourite;
            return return_response('1', 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getAllShopProductsList(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $language = $request->has('language') ? $request->language : 1;
            $favourite_products_list = [];
            $shop_products = MyShop::getShopProducts($user->user_id, $language);
            foreach ($shop_products as $row) {
                $p_row = process_product_data_v2($row, $this->lang_code);
                $p_row['my_product_share_url'] = base_url() . "share/my_product/" . $row->code;
                $favourite_products_list[] = $p_row;
            }

            $o_data['my_earnings'] = MyShop::getMyEarnings($user->user_id);
            $o_data["products"] = $favourite_products_list;

            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getShop(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $language = $request->has('language') ? $request->language : 1;
            $favourite_products_data = MyShop::getShopProducts($user->user_id, $language);
            $favourite_products_list = [];
            foreach ($favourite_products_data as $row) {
                $favourite_products_list[] = process_product_data_v2($row, $language);
            }

            $o_data["products"] = $favourite_products_list;
            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function claimMarks(Request $request) {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $favourite_products_list = [];
            $type = $request->input('type');
            $rules = [];

//            $rules = [
//                'claim_ids' => 'required|array',
//                'claim_ids.*' => 'required',
//            ];

            if ($type == 1) {
                $rules = array_merge($rules, [
                    'bank_name' => 'required|string',
                    'bank_address' => 'required|string',
                    'acc_no' => 'required|string',
                    'iban_no' => 'required|string',
                    'ifsc_code' => 'required|string',
                    'country' => 'required|string',
                    'reciver_name' => 'required|string',
                ]);
            }
//            else {
//                $rules['user_name'] = 'required|string';
//            }

            $messages = [
                'claim_ids.required' => __('messages.validation.required', ['field' => __('messages.common_messages.claim_ids')]),
                'bank_name.required' => __('messages.validation.required', ['field' => __('messages.common_messages.bank_name')]),
                'bank_address.required' => __('messages.validation.required', ['field' => __('messages.common_messages.bank_address')]),
                'acc_no.required' => __('messages.validation.required', ['field' => __('messages.common_messages.acc_no')]),
                'iban_no.required' => __('messages.validation.required', ['field' => __('messages.common_messages.iban_no')]),
                'ifsc_code.required' => __('messages.validation.required', ['field' => __('messages.common_messages.ifsc_code')]),
                'country.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_country')]),
                'reciver_name.required' => __('messages.validation.required', ['field' => __('messages.common_messages.reciver_name')]),
                'user_name.required' => __('messages.validation.required', ['field' => __('messages.common_messages.user_name')]),
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $bank_name = $request->input('bank_name');
            $bank_address = $request->input('bank_address');
            $acc_no = $request->input('acc_no');
            $iban_no = $request->input('iban_no');
            $ifsc_code = $request->input('ifsc_code');
            $country = $request->input('country');
            $reciver_name = $request->input('reciver_name');
            $user_name = $request->input('user_name');
            $claim_ids = $request->input('claim_ids');
            $type = $request->input('type');

            $data = [
                'bank_name' => $bank_name,
                'bank_address' => $bank_address,
                'acc_no' => $acc_no,
                'iban_no' => $iban_no,
                'ifsc_code' => $ifsc_code,
                'country' => $country,
                'reciver_name' => $reciver_name,
                'pay_user_name' => $user_name,
                'earing_ids' => serialize($claim_ids),
                'claim_user_id' => $user->user_id,
                'reedemed_date' => Carbon::now(),
                'claim_type' => $type,
            ];

            $claim_mark_id = MyShop::markClaim($data, $claim_ids, $user->user_id);
            $o_data['invoice_id'] = $claim_mark_id;
            $o_data['user_points'] = $user->user_points;
            $status = "1";
            $message = __('messages.success.list_success');

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }
}
