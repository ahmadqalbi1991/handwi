<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartModel;
use App\Models\CategoryModel;
use App\Models\FavouriteModel;
use App\Models\MyShop;
use App\Models\OrderModel;
use App\Models\Product;
use App\Models\SpinnerModel;
use App\Models\TicketModel;
use App\Models\UserTable;
use Carbon\Carbon;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Mockery\Exception;
use Illuminate\Support\Facades\Validator;
use File,Auth;

class ProductController extends Controller
{
    public function getProductInfo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required'
            ], [
                'product_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_id')])
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => '0',
                    'product_status' => [],
                    'validationErrors' => $validator->errors(),
                    'message' => '',
                    'oData' => []
                ], 500);
            }

            $product_id = $request->product_id;
            $product_attribute_id = $request->product_attrib_id;
            $country_id = $request->country_id;
            $sel_attrib_id = (int)$request->sel_attrib_id;
            $other_sel_attrib_value_id = (string)$request->other_sel_attrib_value_id;
            $lang_code = $request->lang_code == 2 ? $request->lang_code : 1;

            $other_sel_attrib_value_id = explode(",", $other_sel_attrib_value_id);
            $other_sel_attrib_value_id = array_filter($other_sel_attrib_value_id, function ($val_filter) {
                return !empty($val_filter);
            });
            $product_attributes_data = [];
            $temp_product_attributes = [];
            $temp_product_attribute_values = [];
            $selected_attribute_value_data = [];
            $selected_attribute_variation_combinations = [];
            $products_info = [];
            $product_specification = [];
            $product_status = "";
            $message = "";
            $related_campaign_list = [];
            $sel_attrib_value_id = 0;
            $refer_code = "";
            if (empty($country_id)) {
                $country_id = 0;
            }

            if (is_numeric($product_id))
                $product_id = (int)$product_id;
            else {
                $refer_code = $product_id;
                $tproduct_ids = explode("#", decryptor($product_id));
                if (count($tproduct_ids) >= 2) {
                    $product_id = (int)$tproduct_ids[0];
                    $product_attribute_id = (int)$tproduct_ids[1];

                }

                $product_id = (int)$product_id;
            }

            $user_id = $request->has('user_id') ? $request->user_id : 0;
            $product_row = Product::getProduct($product_id, $user_id);
            if (!empty($product_row)) {
                if ($product_row->product_type == 1) {
                    $product_status = "1";
                } else {
                    if ($product_attribute_id > 0) {
                        $product_row = Product::getProductByAttributeIdForProductInfo($product_id, $product_attribute_id, $user_id);
                        if ($product_row) {
                            $res_variation = $this->get_selected_attribute_variations_and_combinations($product_id, $product_attribute_id);
                            $selected_attribute_value_data = $res_variation[0];
                            $selected_attribute_variation_combinations = $res_variation[1];
                            $product_status = "1";
                            $product_attribute_sel_variations_data = Product::getProductAttributeVariation($product_id, $product_attribute_id);

                            if (count($product_attribute_sel_variations_data) > 0) {
                                $sel_attrib_id = $product_attribute_sel_variations_data[0]->attribute_id;
                                $sel_attrib_value_id = $product_attribute_sel_variations_data[0]->attribute_values_id;
                            }
                        } else {
                            $product_status = "0";
                            $product_row = NULL;
                        }
                    } else {
                        $product_attribute_row = Product::getProductOnSelectedAttributes($product_id, array_merge([$sel_attrib_value_id], $other_sel_attrib_value_id));
                        if ($product_attribute_row) {
                            $product_row = Product::getProductByAttributeId($product_id, $product_attribute_row->product_attribute_id, $user_id);
                            $res_variation = $this->get_selected_attribute_variations_and_combinations($product_id, $product_attribute_row->product_attribute_id, $sel_attrib_id, $sel_attrib_value_id);
                            $selected_attribute_value_data = $res_variation[0];
                            $selected_attribute_variation_combinations = $res_variation[1];
                            $product_status = "1";
                        } else {
                            $product_row = NULL;
                            $product_status = "2";
                            $product_attribute_row = Product::getMinProductOnSelectedAttribute($product_id, $sel_attrib_value_id);
                            if ($product_attribute_row) {
                                $product_row = Product::getProductByAttributeId($product_id, $product_attribute_row->product_attribute_id, $user_id);
                                $res_variation = $this->get_selected_attribute_variations_and_combinations($product_id, $product_attribute_row->product_attribute_id, $sel_attrib_id, $sel_attrib_value_id);
                                $selected_attribute_value_data = $res_variation[0];
                                $selected_attribute_variation_combinations = $res_variation[1];
                                $product_status = "3";
                            }
                        }
                    }
                    $product_attributes_data = Product::getProductAttributes($product_id);
                }
            }

            foreach ($product_attributes_data as $pa_row) {
                $variation_selected = "0";
                $variation_available = "0";

                if (!isset($temp_product_attributes[$pa_row->attribute_id])) {
                    $temp_product_attributes[$pa_row->attribute_id] = ["attribute_id" => (string)$pa_row->attribute_id,
                        "attribute_name" => (string)($lang_code == 1) ? $pa_row->attribute_name : (!empty($pa_row->attribute_name_arabic) ? $pa_row->attribute_name_arabic : $pa_row->attribute_name),
                        "attribute_value_in" => (string)$pa_row->attribute_value_in,
                        "selected_attribute_value_id" => $variation_selected
                    ];
                }

                if (isset($selected_attribute_value_data[$pa_row->attribute_values_id])) {
                    $variation_selected = "1";
                    $temp_product_attributes[$pa_row->attribute_id]["selected_attribute_value_id"] = $pa_row->attribute_values_id;
                }


                if (isset($selected_attribute_variation_combinations[$pa_row->attribute_values_id]) || $sel_attrib_id == $pa_row->attribute_id) {
                    $variation_available = "1";
                }

                $temp_product_attribute_values[$pa_row->attribute_id][$pa_row->attribute_values_id] = ["attribute_value_id" => (string)$pa_row->attribute_values_id,
                    "attribute_value_name" => (string)($lang_code == 1) ? $pa_row->attribute_values : (!empty($pa_row->attribute_values_arabic) ? $pa_row->attribute_values_arabic : $pa_row->attribute_values),
                    "attribute_value_in" => (string)$pa_row->attribute_value_in,
                    "attribute_value_color" => (string)$pa_row->attribute_value_color,
                    "available" => $variation_available,
                    "selected" => $variation_selected
                ];
            }

            $temp_product_attributes = array_values($temp_product_attributes);

            for ($i = 0; $i < count($temp_product_attributes); $i++) {
                if (isset($temp_product_attribute_values[$temp_product_attributes[$i]['attribute_id']]))
                    $temp_product_attributes[$i]['attribute_values'] = array_values($temp_product_attribute_values[$temp_product_attributes[$i]["attribute_id"]]);
            }


            $product_attribute_list = $temp_product_attributes;
            if ($product_row) {
                $products_info = process_product_data_v2($product_row, $lang_code);
                $products_info["cart_quantity"] = "0";

                $device_cart_id = $request->device_cart_id;


                if ($user_id > 0)
                    $cart_condition = ["product_id" => $product_row->product_id, "product_attribute_id" => $product_row->product_attribute_id, "user_id" => $user_id];

                else
                    $cart_condition = ["product_id" => $product_row->product_id, "product_attribute_id" => $product_row->product_attribute_id,
                        "anonimous_id" => $device_cart_id, "user_id" => 0];

                $product_cart_row = CartModel::getProductCart($cart_condition);

                if ($product_cart_row)
                    $products_info["cart_quantity"] = $product_cart_row->quantity;

                $product_text = create_plink($product_row->product_name);

                if (!empty($product_row->pa_title))
                    $product_text .= "-" . create_plink($product_row->pa_title);

                $enc_product_id = encryptor($product_row->product_id . "#" . $product_row->product_attribute_id);

                $products_info["deeplinkUrl"] = url('/') . "product_detail/{$enc_product_id}";

                if ($products_info["stock_quantity"] <= 0)
                    $products_info["out_of_stock"] = "1";
                else
                    $products_info["out_of_stock"] = "0";

                $campaigns_id = $product_row->campaigns_id;
                $products_data = Product::getProductsByCampaignsIdForAll($country_id, $campaigns_id, $user_id);

                $category_id = null;
                foreach ($products_data as $row) {
                    $category_id = $row->category_id;
                }
                $related_campaign = Product::getProductsByCategoryId($country_id, $category_id, $user_id, $campaigns_id);

                foreach ($related_campaign as $row) {
                    $related_campaign_list[] = process_product_data($row, $lang_code);
                }

                $status = "1";
            } else {
                $status = "3";
                $message = __('messages.errors.product_invalid');
                $product_status = "0";
            }

            if ($products_info) {
                $condition = [
                    'user_id' => $user_id,
                    'product_id' => $products_info['product_id'],
                    'product_attribute_id' => $products_info['product_attrb_id']
                ];
                $products_info['in_my_shop'] = Product::checkInMyShop($condition);
            }
            $products_info['refer_code'] = $refer_code;
            $products_info['my_product_share_url'] = "";

            if ($refer_code) {
                $products_info['my_product_share_url'] = url('/') . "share/my_product/" . $refer_code;
            }

            $winner_list = [];
            if (isset($products_info['campaigns_id'])) {
                $winner_list = TicketModel::getWonTicketNumber($products_info['campaigns_id']);
                if (!empty($winner_list)) {
                    if ($products_info['draw_date'] != '') {
                        $winner_list->draw_date = (string)get_date_in_timezone(USERTIMEZONE, $products_info['draw_date'], "d M Y h:i A");
                    } else {
                        $winner_list->draw_date = "";
                    }
                }
            }

            $winner_list = (array) $winner_list;
            if (!empty($winner_list)) {
                $winner_list = convertNumbersToStrings($winner_list);
            } else {
                $winner_list = is_array($winner_list) ? (object) $winner_list : $winner_list;
            }

            $o_data["product"] = convertNumbersToStrings($products_info);
            $o_data["product_specification"] = convertNumbersToStrings($product_specification);
            $o_data["selected_attrib_value_id"] = (string)$sel_attrib_value_id;
            $o_data["product_attribute_list"] = convertNumbersToStrings($product_attribute_list);
            $o_data["related_products"] = convertNumbersToStrings($related_campaign_list);
            $o_data['product_status'] = $product_status;
            $o_data['winner_list'] = $winner_list;

            return return_response($status, 200, $message, [], $o_data);
        } catch (Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function toggleFavourite(Request $request)
    {
        try {
            $message = '';

            $validator = Validator::make($request->all(), [
                'product_id' => 'required|numeric',
                'product_attribute_id' => 'required|numeric',
            ], [
                'product_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_id')]),
                'product_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_id')]),
                'product_attribute_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_attribute_id')]),
                'product_attribute_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_attribute_id')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $product_id = $request->product_id;
            $product_attribute_id = $request->product_attribute_id;

            $favourite = FavouriteModel::where(['product_id' => $product_id, 'product_attribute_id' => $product_attribute_id])->first();
            if (!empty($favourite)) {
                $is_favourite = '0';
                FavouriteModel::where('id', $favourite->id)->delete();
            } else {
                FavouriteModel::create([
                    "user_id" => $user->user_id,
                    "product_id" => $product_id,
                    "product_attribute_id" => $product_attribute_id,
                    "favourate_added_time" => Carbon::now()
                ]);
                $is_favourite = '1';
                $message = '';
            }

            $o_data["isFavourite"] = $is_favourite;
            return return_response('1', 200, $message, [], $o_data);
        } catch (Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    private function get_selected_attribute_variations_and_combinations($product_id, $product_attribute_id, $sel_attrib_id = 0, $sel_attrib_value_id = 0)
    {

        $selected_attribute_value_data = [];
        $selected_attribute_variation_combinations = [];

        $product_attribute_sel_variations_data = Product::getProductAttributeVariation($product_id, $product_attribute_id);

        foreach ($product_attribute_sel_variations_data as $pvar_row) {
            $selected_attribute_value_data[$pvar_row->attribute_values_id] = $pvar_row->attribute_id;
        }

        if (count($product_attribute_sel_variations_data) > 0) {
            if ($sel_attrib_id == 0) {
                $sel_attrib_id = $product_attribute_sel_variations_data[0]->attribute_id;
                $sel_attrib_value_id = $product_attribute_sel_variations_data[0]->attribute_values_id;
            }

            $variation_combination_data = Product::getProductSelAttributeVariationCombinations($product_id, $sel_attrib_value_id, $sel_attrib_id);

            foreach ($variation_combination_data as $pvc_row) {
                $selected_attribute_variation_combinations[$pvc_row->attribute_values_id] = $pvc_row->attribute_id;
            }

        }

        return [$selected_attribute_value_data, $selected_attribute_variation_combinations];

    }

    public function getClosedCampaignByCategory(Request $request)
    {
        try {
            $status = '0';
            $validation_errors = [];
            $products_list = [];
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $categories = CategoryModel::where('category_language_code', 1)->get();
            if ($request->country_id > 0 && $request->category_id >= 0) {
                if ($request->category_id > 0) {
                    $products_data = Product::getClosedCampaignByCategoryId($request->country_id, $request->category_id, $user->user_id);
                } else {
                    $products_data = Product::getAllClosedCampaigns($request->country_id, $user->user_id);
                }

                foreach ($products_data as $row) {
                    $p_row = process_product_data_v2($row, 1);
                    $p_row["sel_attributes"] = (string)$row->attribute_ids;
                    $p_row["sel_attribute_variations"] = (string)$row->attribute_values_ids;
                    $products_list[] = $p_row;
                }
            } else {
                $status = "2";
                if ($request->country_id < 0) {
                    $validation_errors['country_id'] = __('messages.errors.invalid_country_id');
                }
                if ($request->category_id < 0) {
                    $validation_errors['category_id'] = __('messages.errors.invalid_category_id');
                }
            }
            $o_data["products"] = $products_list;
            $o_data["categories"] = $categories;

            return response()->json([
                "status" => $status,
                "message" => '',
                "validationErrors" => (object)$validation_errors,
                "oData" => $o_data
            ], 200);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getOrderByProductTicket(Request $request)
    {
        try {
            $message = '';
            $ticket_numbers = [];
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|numeric',
                'product_id' => 'required|numeric',
                'product_attribute_id' => 'required|numeric',
            ], [
                'order_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.order_id')]),
                'order_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.order_id')]),
                'product_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_id')]),
                'product_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_id')]),
                'product_attribute_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_attribute_id')]),
                'product_attribute_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_attribute_id')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $order = OrderModel::where('product_order_id', $request->order_id)->first();
            if (!empty($order)) {
                $status = "1";
                $ticket_numbers = TicketModel::where(["order_block_id" => $request->order_id, "product_id" => $request->product_id, "product_attribute_id" => $request->product_attribute_id])->get();
            } else {
                $status = '2';
                $message = __('messages.errors.no_order_found');
            }

            $o_data["orderTicketNumbers"] = $ticket_numbers;

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function get_order_stat_change($status)
    {
        if ($status == 1) {
            $status = 2;
        } elseif ($status == 2) {
            $status = 3;
        } elseif ($status == 3) {
            $status = 0;
        } elseif ($status == 0) {
            $status = 1;
        }

        return $status;
    }

    public function getProductListByCategoryId(Request $request)
    {
        try {
            $validation_errors = [];
            $o_data = [];
            $products_list = [];
            $campaigns_coming_soon_list = [];
            $language = $request->language ? $request->language : 1;
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $product_categories_data = CategoryModel::where('category_language_code', $language)->get();
            foreach ($product_categories_data as $key => $value) {
//                $product_categories_data[$key]->category_icon = get_image("category",$value->category_icon);
            }

            if ($request->country_id > 0 && $request->category_id >= 0) {
                if ($request->category_id > 0) {
                    $products_data = Product::getProductsByCategoryId($request->country_id, $request->category_id, $user->user_id, 0, $language, "","order by product_id desc");
                    $campaigns_coming_soon_data = Product::getComingProductsByCategoryId($request->country_id, $request->category_id, $user->user_id);
                } else {
                    $products_data = product::getAllProducts($request->country_id, $user->user_id, $language, "", "order by product_id desc");
                    $campaigns_coming_soon_data = Product::getAllProductsComingSoon($request->country_id, $user->user_id, $language, "", " order by campaigns_expiry_uts asc ");
                }

                foreach ($products_data as $row) {
                    if ($row->product_type == 1) {
                        $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                        if ($stock_available > 0) {
                            $p_row = process_product_data_v2($row, 1);
                            $p_row["sel_attributes"] = (string)$row->attribute_ids;
                            $p_row["sel_attribute_variations"] = (string)$row->attribute_values_ids;

                            if ($p_row) {
                                $condition = [
                                    'user_id' => $user->user_id,
                                    'product_id' => $p_row['product_id'],
                                    'product_attribute_id' => $p_row['product_attrb_id']
                                ];
                                $code = Product::checkInMyShop($condition);
                                $p_row['in_my_shop'] = "0";
                                $p_row['my_product_share_url'] = "";
                                if ($code) {
                                    $p_row['in_my_shop'] = "1";
                                    $p_row['my_product_share_url'] = url('/') . "share/my_product/" . $code;
                                }
                            }
                            $p_row['spinner_count'] = "0";
                            $p_row['user_spins'] = [];

                            if ($p_row['is_spinner']) {
                                $user_spins = CartModel::getUserSpinnerByProductAttributeId($p_row['product_id'], $p_row['product_attrb_id'], $user->user_id);
                                $p_row['spinner_count'] = (string)count($user_spins);
                                $p_row['user_spins'] = $user_spins;
                            }
                            $products_list[] = $p_row;
                        }
                    } else {
                        //its variable check for every variation
                        $attributes = Product::getProductAttributeId($row->product_id);
                        foreach ($attributes as $attrib_row) {
                            $subdata = Product::getAllProductsWithAttributes($request->country_id, $user->user_id, 1, "", " order by campaigns_expiry_uts asc ", $row->product_id, $attrib_row->product_attribute_id);
                            foreach ($subdata as $sub_row) {
                                $stock_available = ((float)$sub_row->stock_quantity - (float)$sub_row->product_on_process);
                                if ($stock_available > 0) {
                                    $p_row = process_product_data_v2($row, 1);
                                    $p_row["sel_attributes"] = (string)$row->attribute_ids;
                                    $p_row["sel_attribute_variations"] = (string)$row->attribute_values_ids;
                                    if ($p_row) {
                                        $condition = [
                                            'user_id' => $user->user_id,
                                            'product_id' => $p_row['product_id'],
                                            'product_attribute_id' => $p_row['product_attrb_id']
                                        ];
                                        $code = Product::checkInMyShop($condition);
                                        $p_row['in_my_shop'] = "0";
                                        $p_row['my_product_share_url'] = "";
                                        if ($code) {
                                            $p_row['in_my_shop'] = "1";
                                            $p_row['my_product_share_url'] = url('/') . "share/my_product/" . $code;
                                        }
                                    }
                                    $p_row['spinner_count'] = "0";
                                    $p_row['user_spins'] = [];
                                    if ($p_row['is_spinner']) {
                                        $user_spins = CartModel::getUserSpinnerByProductAttributeId($p_row['product_id'], $p_row['product_attrb_id'], $user->user_id);
                                        $p_row['spinner_count'] = (string)count($user_spins);
                                        $p_row['user_spins'] = $user_spins;
                                    }
                                    $products_list[] = $p_row;
                                    break 2;
                                }
                            }
                        }
                    }
                }
                foreach ($campaigns_coming_soon_data as $row) {
                    $p_row = process_product_data_v2($row, 1);
                    $p_row["sel_attributes"] = (string)$row->attribute_ids;
                    $p_row["sel_attribute_variations"] = (string)$row->attribute_values_ids;
                    $campaigns_coming_soon_list[] = $p_row;
                }
                $status = "1";
            } else {
                $status = "2";
                if ($request->country_id < 0) {
                    $validation_errors['country_id'] = __('messages.errors.invalid_country_id');
                }
                if ($request->category_id < 0) {
                    $validation_errors['category_id'] = __('messages.errors.invalid_category_id');
                }
            }

            foreach ($products_list as $key => $value) {
                $condition = [
                    'user_id' => $user->user_id,
                    'product_id' => $value['product_id'],
                    'product_attribute_id' => $value['product_attrb_id']
                ];
                $cc = Product::checkInMyShop($condition);
                $products_list[$key]['in_my_shop'] = "0";
                if ($cc) {
                    $products_list[$key]['in_my_shop'] = "1";
                }
            }

            $o_data["products"] = $products_list;
            $o_data["campaignsComingSoon"] = $campaigns_coming_soon_list;
            $o_data["categories"] = $product_categories_data;
            $o_data['spinner_data'] = SpinnerModel::where([
                'is_deleted' => 0,
                'spinner_language_code' => 1
            ])->get();

            return return_response($status, 200, '', $validation_errors, $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function addFavouritesToCart(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $favourites = UserTable::getFavourites($user->user_id);
            $quantity = 1;
            foreach ($favourites as $row) {
                $i_data['user_id'] = $user->user_id;
                $i_data['product_id'] = $row->product_id;
                $i_data['product_attribute_id'] = $row->product_attribute_id;
                $i_data['anonimous_id'] = 0;
                $i_data['order_placed'] = 0;
                $i_data['quantity'] = (int)$quantity;
                $i_data['cart_created_date'] = gmdate("Y-m-d H:i:s");
                $product_cart_data = CartModel::where([
                    "product_id" => $row->product_id,
                    "product_attribute_id" => $row->product_attribute_id,
                    "user_id" => $user->user_id
                ])->first();

                if ($product_cart_data) {
                    $quantity = $product_cart_data->quantity + $quantity;
                    CartModel::where("cart_id", $product_cart_data->cart_id)->update(["quantity" => $quantity]);
                } else {
                    CartModel::create($i_data);
                }

            }

            if (count($favourites) > 0) {
                $status = "1";
                $message = __("messages.success.all_product_fav_added");
            } else {
                $status = "3";
                $message = __("messages.errors.no_fav_product");
            }

            return return_response($status, 200, $message);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getAllWinners(Request $request) {
        try {
            $country_id = $request->has('country_id') ? $request->country_id : 188;
            $limit = $request->has('limit') ? $request->limit : 10;
            $page_number = $request->has('page') ? ($request->page ?? 1) : 1;
            $offset = ($page_number - 1) * $limit;
            $lang = 1;
            $user_list_won = [];
            $limit_query = "";
            if ($limit > 0)
                $limit_query = " limit {$offset}, {$limit}";

            $winner_list = TicketModel::getWinnerList($lang, "", "", $limit_query, $country_id);
            foreach ($winner_list as $row) {
                $product_image = '';
                if (File::exists(public_path('uploads/products/' . $row->campaigns_image)) && is_file(public_path('uploads/products/' . $row->campaigns_image))) {
                    $product_image = asset('uploads/products/' . $row->campaigns_image);
                } else {
                    $product_image = asset('images/dummy.jpg');
                }

                $product_image2 = '';
                if (File::exists(public_path('uploads/products/' . $row->product_image)) && is_file(public_path('uploads/products/' . $row->product_image))) {
                    $product_image2 = asset('uploads/products/' . $row->product_image);
                } else {
                    $product_image2 = asset('images/dummy.jpg');
                }

                $user_image = '';
                if (File::exists(public_path('uploads/products/' . $row->image)) && is_file(public_path('uploads/products/' . $row->image))) {
                    $user_image = asset('uploads/products/' . $row->image);
                } else {
                    $user_image = asset('images/dummy.jpg');
                }

                $processed_won_ticket["m_product_image"] = (string)$row->product_image;
                $processed_won_ticket["campaigns_home_image"] = (string)$row->campaigns_image;
                $processed_won_ticket["product_name"] = (string)$row->product_name;
                $processed_won_ticket["campaigns_title"] = (string)$row->campaigns_title;
                $processed_won_ticket["campaigns_id"] = (string)$row->campaigns_id;
                $processed_won_ticket["product_id"] = (string)$row->product_id;
                $processed_won_ticket["product_attribute_id"] = (string)$row->product_attribute_id;
                $processed_won_ticket["purchased_on"] = (string)get_date_in_timezone(USERTIMEZONE, $row->order_placed_date, "d M Y h:i A");

                if ($row->campaigns_draw_date != '') {
                    $processed_won_ticket["draw_date"] = (string)get_date_in_timezone(USERTIMEZONE, $row->campaigns_draw_date, "d M Y h:i A");
                } else {
                    $processed_won_ticket["draw_date"] = "";
                }

                if ($row->draw_date_manual != '') {
                    $processed_won_ticket["draw_date"] = (string)get_date_in_timezone(USERTIMEZONE, $row->draw_date_manual, "d M Y h:i A");
                } else {
                    $processed_won_ticket["draw_date"] = "";
                }

                $processed_won_ticket["won_ticket_number"] = $row->draw_slip_number;
                $processed_won_ticket["is_user_won_campaign"] = "0";
                $processed_won_ticket["user_first_name"] = (string)$row->user_first_name;
                $processed_won_ticket["user_last_name"] = (string)$row->user_last_name;
                $processed_won_ticket["campaigns_end_date"] = gmdate("d-m-Y", strtotime($row->campaigns_date . " " . $row->campaigns_time));
                $processed_won_ticket["user_image"] = (string)$user_image;
                $ticket_info = TicketModel::getTicketCount($row->draw_slip_number);
                $processed_won_ticket["ticket_number"] = (string)$row->draw_slip_number;
                $processed_won_ticket["ticket_count"] = (string)$ticket_info;
                $processed_won_ticket["user_id"] = (string)$row->user_id;

                $user_list_won[] = $processed_won_ticket;
            }

            $o_data['winnersList'] = $user_list_won;
            return return_response('1', 200, '', [], $o_data);
        }  catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getAllProducts(Request $request) {
        try {
            $country_id = $request->has('country_id') ? $request->country_id : 188;
            $user_id = $request->has('user_id') ? $request->user_id : 0;
            $limit = $request->has('limit') ? $request->limit : 10;
            $page_number = $request->has('page') ? ($request->page ?? 1) : 1;
            $offset = ($page_number - 1) * $limit;
            $lang = 1;
            $products_list = [];
            $campaigns_outofstock_list = [];
            $filter = '';
            if ($request->featured_products) {
                $filter = " and campaigns.is_featured = true";
            }
            $products_data = Product::getAllProducts($country_id, $user_id, '1', $filter, $sort = "order by product_id desc", $limit, $offset, true);

            foreach ($products_data as $row) {
                if ($row->product_type == 1) {
                    $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                    $total_stock_added = $row->stock_quantity + (int)$row->product_on_process + (int)$row->product_order_placed;
                    $order_placed = ((int)$row->product_on_process + (int)$row->product_order_placed);
                    $percentage = round(($order_placed / $total_stock_added) * 100);
                    if ($stock_available > 0) {
                        $p_data = process_product_data_v2($row, $lang);
                        $p_data['spinner_count'] = "0";
                        $p_data['user_spins'] = [];
                        if ($p_data['is_spinner']) {
                            $user_spins = CartModel::getUserSpinnerByProductAttributeId($p_data['product_id'], $p_data['product_attrb_id'], $user_id);
                            $p_data['spinner_count'] = (string)count($user_spins);
                            $p_data['user_spins'] = $user_spins;
                        }
                        $products_list[] = convertNumbersToStrings($p_data);
                    }
                } else {
                    $attributes = Product::getProductAttributeId($row->product_id);
                    foreach ($attributes as $attrib_row) {
                        $subdata = Product::getAllProductsWithAttributes($country_id, $user_id, $lang, "", " order by campaigns_expiry_uts asc ", $row->product_id, $attrib_row->product_attribute_id);
                        foreach ($subdata as $sub_row) {
                            $stock_available = ((float)$sub_row->stock_quantity - (float)$sub_row->product_on_process);
                            $total_stock_added = $row->stock_quantity + (int)$row->product_on_process + (int)$row->product_order_placed;
                            $order_placed = ((int)$row->product_on_process + (int)$row->product_order_placed);
                            $percentage = round(($order_placed / $total_stock_added) * 100);
                            if ($stock_available > 0) {
                                $p_data = process_product_data_v2($row, $lang);
                                $p_data['spinner_count'] = "0";
                                $p_data['user_spins'] = [];
                                if ($p_data['is_spinner']) {
                                    $user_spins = CartModel::getUserSpinnerByProductProductAttributeId($p_data['product_id'], $p_data['product_attrb_id'], $user_id);
                                    $p_data['spinner_count'] = (string)count($user_spins);
                                    $p_data['user_spins'] = $user_spins;
                                }
                                $products_list[] = convertNumbersToStrings($p_data);
                                break 2;
                            }
                        }
                    }
                }
            }

            if ($request->out_of_stock_products) {
                $campaigns_coming_soon_data = Product::getAllProductsComingSoon($country_id, $user_id, $lang, "", " order by campaigns_expiry_uts asc ");

                foreach ($products_data as $row) {
                    if ($row->product_type == 1) {
                        $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                        if ($stock_available <= 0) {
                            $campaigns_outofstock_list[] = process_product_data_v2($row, $lang);
                        }
                    } else {

                        $sold_out = 1;
                        $attributes = Product::getProductStock($row->product_id);

                        if ($attributes[0]->stock_quantity > 0) {
                            $sold_out = 0;
                        }

                        if ($sold_out == 1) {
                            $campaigns_outofstock_list[] = process_product_data_v2($row, $lang);
                        }
                    }

                }

                foreach ($campaigns_coming_soon_data as $row) {
                    if ($row->product_type == 1) {
                        $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                        if ($stock_available <= 0) {
                            $campaigns_outofstock_list[] = process_product_data_v2($row, $lang);
                        }
                    } else {
                        $sold_out = 1;
                        $attributes = Product::getProductStock($row->product_id);

                        if ($attributes[0]->stock_quantity > 0) {
                            $sold_out = 0;
                        }

                        if ($sold_out == 1) {
                            $campaigns_outofstock_list[] = process_product_data_v2($row, $lang);
                        }
                    }
                }

                $products_list = $campaigns_outofstock_list;
            }


            $o_data['product_list'] = $products_list;

            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function home(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'country_id' => 'gt:0'
            ], [
                'country_id.greater_than_zero' => __('messages.validation.required', ['field' => __('messages.common_messages.country_id')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }
            $message = "";
            $o_data = [];
            $products_list = $featured_products_list = [];
            $campaigns_closing_soon_list = [];
            $campaigns_coming_soon_list = [];
            $slider_list = [];
            $ads_list = [];
            $campaigns_outofstock_list = [];
            $user_list_won = [];
            $lang = $request->has('language') ? $request->language : 1;
            $user_id = $request->has('user_id') ? $request->user_id : 0;
            $limit = $request->limit;
            $offset = $request->offset;
            $country_id = $request->country_id;

            $images = [
                asset('images/red-car.png'),
                asset('images/blue-car.png'),
                asset('images/white-car.png'),
                asset('images/money.png'),
            ];

            $limit_query = "";
            if ($limit > 0)
                $limit_query = " limit {$offset}, {$limit}";

            $winner_list = TicketModel::getWinnerList($lang, "", "", $limit_query, $country_id);
            foreach ($winner_list as $row) {
                $product_image = '';
                if (File::exists(public_path('uploads/products/' . $row->campaigns_image)) && is_file(public_path('uploads/products/' . $row->campaigns_image))) {
                    $product_image = asset('uploads/products/' . $row->campaigns_image);
                } else {
                    $product_image = asset('images/dummy.jpg');
                }

                $product_image2 = '';
                if (File::exists(public_path('uploads/products/' . $row->product_image)) && is_file(public_path('uploads/products/' . $row->product_image))) {
                    $product_image2 = asset('uploads/products/' . $row->product_image);
                } else {
                    $product_image2 = asset('images/dummy.jpg');
                }

                $user_image = '';
                if (File::exists(public_path('uploads/products/' . $row->image)) && is_file(public_path('uploads/products/' . $row->image))) {
                    $user_image = asset('uploads/products/' . $row->image);
                } else {
                    $user_image = asset('images/dummy.jpg');
                }

                $processed_won_ticket["m_product_image"] = (string)$row->product_image;
                $processed_won_ticket["campaigns_home_image"] = (string)$row->campaigns_image;
                $processed_won_ticket["product_name"] = (string)$row->product_name;
                $processed_won_ticket["campaigns_title"] = (string)$row->campaigns_title;
                $processed_won_ticket["campaigns_id"] = (string)$row->campaigns_id;
                $processed_won_ticket["product_id"] = (string)$row->product_id;
                $processed_won_ticket["product_attribute_id"] = (string)$row->product_attribute_id;
                $processed_won_ticket["purchased_on"] = (string)get_date_in_timezone(USERTIMEZONE, $row->order_placed_date, "d M Y h:i A");

                if ($row->campaigns_draw_date != '') {
                    $processed_won_ticket["draw_date"] = (string)get_date_in_timezone(USERTIMEZONE, $row->campaigns_draw_date, "d M Y h:i A");
                } else {
                    $processed_won_ticket["draw_date"] = "";
                }

                if ($row->draw_date_manual != '') {
                    $processed_won_ticket["draw_date"] = (string)get_date_in_timezone(USERTIMEZONE, $row->draw_date_manual, "d M Y h:i A");
                } else {
                    $processed_won_ticket["draw_date"] = "";
                }

                $processed_won_ticket["won_ticket_number"] = $row->draw_slip_number;
                $processed_won_ticket["is_user_won_campaign"] = "0";
                $processed_won_ticket["user_first_name"] = (string)$row->user_first_name;
                $processed_won_ticket["user_last_name"] = (string)$row->user_last_name;
                $processed_won_ticket["campaigns_end_date"] = gmdate("d-m-Y", strtotime($row->campaigns_date . " " . $row->campaigns_time));
                $processed_won_ticket["user_image"] = (string)$user_image;
                $ticket_info = TicketModel::getTicketCount($row->draw_slip_number);
                $processed_won_ticket["ticket_number"] = (string)$row->draw_slip_number;
                $processed_won_ticket["ticket_count"] = (string)$ticket_info;

                $user_list_won[] = $processed_won_ticket;
            }

            $product_categories_data = CategoryModel::where('category_language_code', $lang)->get();
            foreach ($product_categories_data as $key => $value) {
                $product_categories_data[$key]->category_icon = $images[array_rand($images)];
//                $product_categories_data[$key]->category_icon = get_image("category", $value->category_icon);
            }

            $products_data = Product::getAllProducts($country_id, $user_id, $lang_code = "1", $filter = "", $sort = "order by product_id desc");
            $featured_products_data = Product::getAllProducts($country_id, $user_id, $lang_code = "1", $filter = " and campaigns.is_featured = true", $sort = "order by product_id desc");
            $campaigns_closing_soon_data = Product::getAllProducts($country_id, $user_id, $lang, "", " order by campaigns_expiry_uts asc ");
            $slider_data = Product::getActiveAppSliders($country_id, $user_id);
            $campaigns_coming_soon_data = Product::getAllProductsComingSoon($country_id, $user_id, $lang, "", " order by campaigns_expiry_uts asc ");

            foreach ($products_data as $row) {
                if ($row->product_type == 1) {
                    $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                    $total_stock_added = $row->stock_quantity + (int)$row->product_on_process + (int)$row->product_order_placed;
                    $order_placed = ((int)$row->product_on_process + (int)$row->product_order_placed);
                    $percentage = round(($order_placed / $total_stock_added) * 100);
                    if ($stock_available > 0) {
                        $p_data = process_product_data_v2($row, $lang);
                        $p_data['spinner_count'] = "0";
                        $p_data['user_spins'] = [];
                        if ($p_data['is_spinner']) {
                            $user_spins = CartModel::getUserSpinnerByProductAttributeId($p_data['product_id'], $p_data['product_attrb_id'], $user_id);
                            $p_data['spinner_count'] = (string)count($user_spins);
                            $p_data['user_spins'] = $user_spins;
                        }
                        $products_list[] = convertNumbersToStrings($p_data);
                    }
                } else {
                    $attributes = Product::getProductAttributeId($row->product_id);
                    foreach ($attributes as $attrib_row) {
                        $subdata = Product::getAllProductsWithAttributes($country_id, $user_id, $lang, "", " order by campaigns_expiry_uts asc ", $row->product_id, $attrib_row->product_attribute_id);
                        foreach ($subdata as $sub_row) {
                            $stock_available = ((float)$sub_row->stock_quantity - (float)$sub_row->product_on_process);
                            $total_stock_added = $row->stock_quantity + (int)$row->product_on_process + (int)$row->product_order_placed;
                            $order_placed = ((int)$row->product_on_process + (int)$row->product_order_placed);
                            $percentage = round(($order_placed / $total_stock_added) * 100);
                            if ($stock_available > 0) {
                                $p_data = process_product_data_v2($row, $lang);
                                $p_data['spinner_count'] = "0";
                                $p_data['user_spins'] = [];
                                if ($p_data['is_spinner']) {
                                    $user_spins = CartModel::getUserSpinnerByProductProductAttributeId($p_data['product_id'], $p_data['product_attrb_id'], $user_id);
                                    $p_data['spinner_count'] = (string)count($user_spins);
                                    $p_data['user_spins'] = $user_spins;
                                }
                                $products_list[] = convertNumbersToStrings($p_data);
                                break 2;
                            }
                        }
                    }
                }
            }

            foreach ($featured_products_data as $row) {
                if ($row->product_type == 1) {
                    $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                    $total_stock_added = $row->stock_quantity + (int)$row->product_on_process + (int)$row->product_order_placed;
                    $order_placed = ((int)$row->product_on_process + (int)$row->product_order_placed);
                    $percentage = round(($order_placed / $total_stock_added) * 100);
                    if ($stock_available > 0) {
                        $p_data = process_product_data_v2($row, $lang);
                        $p_data['spinner_count'] = "0";
                        $p_data['user_spins'] = [];
                        if ($p_data['is_spinner']) {
                            $user_spins = CartModel::getUserSpinnerByProductAttributeId($p_data['product_id'], $p_data['product_attrb_id'], $user_id);
                            $p_data['spinner_count'] = (string)count($user_spins);
                            $p_data['user_spins'] = $user_spins;
                        }
                        $featured_products_list[] = convertNumbersToStrings($p_data);
                    }
                } else {
                    $attributes = Product::getProductAttributeId($row->product_id);
                    foreach ($attributes as $attrib_row) {
                        $subdata = Product::getAllProductsWithAttributes($country_id, $user_id, $lang, "", " order by campaigns_expiry_uts asc ", $row->product_id, $attrib_row->product_attribute_id);
                        foreach ($subdata as $sub_row) {
                            $stock_available = ((float)$sub_row->stock_quantity - (float)$sub_row->product_on_process);
                            $total_stock_added = $row->stock_quantity + (int)$row->product_on_process + (int)$row->product_order_placed;
                            $order_placed = ((int)$row->product_on_process + (int)$row->product_order_placed);
                            $percentage = round(($order_placed / $total_stock_added) * 100);
                            if ($stock_available > 0) {
                                $p_data = process_product_data_v2($row, $lang);
                                $p_data['spinner_count'] = "0";
                                $p_data['user_spins'] = [];
                                if ($p_data['is_spinner']) {
                                    $user_spins = CartModel::getUserSpinnerByProductProductAttributeId($p_data['product_id'], $p_data['product_attrb_id'], $user_id);
                                    $p_data['spinner_count'] = (string)count($user_spins);
                                    $p_data['user_spins'] = $user_spins;
                                }
                                $featured_products_list[] = convertNumbersToStrings($p_data);
                                break 2;
                            }
                        }
                    }
                }
            }

            foreach ($products_data as $row) {
                if ($row->product_type == 1) {
                    $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                    $total_stock_added = $row->stock_quantity + (int)$row->product_on_process + (int)$row->product_order_placed;
                    $order_placed = ((int)$row->product_on_process + (int)$row->product_order_placed);
                    $percentage = round(($order_placed / $total_stock_added) * 100);
                    if ($stock_available > 0 && $percentage > 75) {
                        $campaigns_closing_soon_list[] = process_product_data_v2($row, $lang);
                    }
                } else {
                    $attributes = Product::getProductAttributeId($row->product_id);
                    foreach ($attributes as $attrib_row) {
                        $subdata = Product::getAllProductsWithAttributes($country_id, $user_id, $lang, "", " order by campaigns_expiry_uts asc ", $row->product_id, $attrib_row->product_attribute_id);
                        foreach ($subdata as $sub_row) {
                            $stock_available = ((float)$sub_row->stock_quantity - (float)$sub_row->product_on_process);
                            $total_stock_added = $row->stock_quantity + (int)$row->product_on_process + (int)$row->product_order_placed;
                            $order_placed = ((int)$row->product_on_process + (int)$row->product_order_placed);
                            $percentage = round(($order_placed / $total_stock_added) * 100);
                            if ($stock_available > 0 && $percentage > 75) {
                                $campaigns_closing_soon_list[] = process_product_data_v2($row, $lang);
                                break 2;
                            }
                        }
                    }
                }
            }

            foreach ($products_data as $row) {
                if ($row->product_type == 1) {
                    $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                    if ($stock_available <= 0) {
                        $campaigns_outofstock_list[] = process_product_data_v2($row, $lang);
                    }
                } else {

                    $sold_out = 1;
                    $attributes = Product::getProductStock($row->product_id);

                    if ($attributes[0]->stock_quantity > 0) {
                        $sold_out = 0;
                    }

                    if ($sold_out == 1) {
                        $campaigns_outofstock_list[] = process_product_data_v2($row, $lang);
                    }
                }

            }

            foreach ($campaigns_coming_soon_data as $row) {
                if ($row->product_type == 1) {
                    $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                    if ($stock_available <= 0) {
                        $campaigns_outofstock_list[] = process_product_data_v2($row, $lang);
                    }
                } else {
                    $sold_out = 1;
                    $attributes = Product::getProductStock($row->product_id);

                    if ($attributes[0]->stock_quantity > 0) {
                        $sold_out = 0;
                    }

                    if ($sold_out == 1) {
                        $campaigns_outofstock_list[] = process_product_data_v2($row, $lang);
                    }
                }

            }

            foreach ($campaigns_coming_soon_data as $row) {
                if ($row->product_type == 1) {
                    $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                    if ($stock_available > 0) {
                        $campaigns_coming_soon_list[] = process_product_data_v2($row, $lang);
                    }
                } else {
                    $attributes = Product::getProductAttributeId($row->product_id);
                    foreach ($attributes as $attrib_row) {
                        $subdata = Product::getAllProductsWithAttributes($country_id, $user_id, $lang, "", " order by campaigns_expiry_uts asc ", $row->product_id, $attrib_row->product_attribute_id);
                        foreach ($subdata as $sub_row) {
                            $stock_available = ((float)$sub_row->stock_quantity - (float)$sub_row->product_on_process);
                            if ($stock_available > 0) {
                                $campaigns_coming_soon_list[] = process_product_data_v2($row, $lang);
                                break 2;
                            }
                        }
                    }
                }
            }

            foreach ($slider_data as $row) {
//                $image = url('/') . 'uploads/banner/' . $row->si_image;
                $image = $images[array_rand($images)];
                $ads_list[] = array("ad_image" => $image,
                    "ad_url" => (string)$row->si_url,
                    "ad_type" => (string)$row->si_type,
                    "ad_type_id" => (string)$row->si_type_id);
            }

            $slider_data = Product::getWebSliders($lang, $country_id);
            $slider_data = $slider_data->take(4);
            foreach ($slider_data as $row) {
//                $image = asset('uploads/banner/' . $row->bi_image);
                $image = $images[array_rand($images)];
                $slider_list[] = array("slider_image" => $image,
                    "slider_url" => (string)$row->bi_image,
                    "product_id" => (string)$row->product_id,
                    "product_attrb_id" => (string)$row->product_attr_id,
                    "slider_id" => (string)$row->id,
                    "slider_type_id" => "");
            }

            if ($user_id > 0) {
                $condition = " and cart.user_id = '{$user_id}'";
            } else {
                $condition = " and cart.anonimous_id = '{$request->device_cart_id}' and cart.user_id = 0 ";
            }

            $product_cart_data = CartModel::getCartProducts($user_id, $lang, $condition);
            $status = "1";

            $o_data["winnerList"] = $user_list_won;
            $o_data["outOfStock"] = convertNumbersToStrings($campaigns_outofstock_list);
//            $o_data["categories"] = $product_categories_data;
            $o_data["allProducts"] = $products_list;
            $o_data["featuredProducts"] = $featured_products_list;
            $o_data["campaignsClosingSoon"] = $campaigns_closing_soon_list;
            $o_data["sliderList"] = $slider_list;
            $o_data["ads"] = $ads_list;
            $o_data["cartCount"] = (string)count($product_cart_data);
            $o_data["campaignsComingSoon"] = $campaigns_coming_soon_list;
            $o_data['spinner_data'] = CartModel::getSpinnerPrize($lang);

            $contacts = getContactDetails();
            $o_data["settings"]["twitter"] = "";
            $o_data["settings"]["whatsapp"] = "";
            $o_data["settings"]["instagram_url"] = "";
            $o_data["settings"]["facebook_url"] = "";
            $o_data["settings"]["call_us"] = "";
            $o_data["settings"]["contact_email"] = "";

            if (!empty($contacts)) {
                $o_data["settings"]["twitter"] = "https://twitter.com/" . $contacts->twitter_link;
                $o_data["settings"]["instagram_url"] = "https://instagram.com/" . $contacts->insta_link;
                $o_data["settings"]["facebook_url"] = "https://facebook.com/" . $contacts->fb_link;
                $o_data["settings"]["whatsapp"] = $contacts->watsup_link;
                $o_data["settings"]["call_us"] = $contacts->dial_code . " " . $contacts->phone_no;
                $o_data["settings"]["contact_email"] = $contacts->email;
            }

            return response()->json([
                'status' => $status,
                'message' => $message,
                'validationErrors' => [],
                'oData' => $o_data
            ], 200);
        } catch (\Exception $exception) {
            dd($exception);
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function searchProducts(Request $request)
    {
        try {
            $status = '0';
            $language = $request->has('language') ? $request->language : 1;
            $message = '';
            $products_list = [];

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $keyword = $request->has('keyword') ? $request->keyword : '';
            if (!empty($keyword)) {
                $filter = " and (lower(product.product_name) like '%{$keyword}%' or lower(campaigns_title) like '%{$keyword}%')";
                $products_data = Product::getAllProductsSearch($request->country_id, $user->user_id, $language, $filter);
            } else {
                $products_data = Product::getAllProductsSearch($request->country_id, $user->user_id, $language);
            }

            foreach ($products_data as $row) {
                $products_list[] = process_product_data_v2($row, $language);
            }

            $status = "1";
            $o_data["products"] = $products_list;

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getFeaturedProducts(Request $request) {
        try {
            $user_id = 0;
            $featured_products_data = Product::getAllProducts($request->country_id, $user_id, $lang_code = "1", $filter = " and campaigns.is_featured = true", $sort = "order by product_id desc");
            $lang = $request->has('language') ? $request->language : 1;
            $featured_products_list = [];

            $user = Auth::user();
            if ($user) {
                $user_id = $user->user_id;
            }

            foreach ($featured_products_data as $row) {
                if ($row->product_type == 1) {
                    $stock_available = ((float)$row->stock_quantity - (float)$row->product_on_process);
                    $total_stock_added = $row->stock_quantity + (int)$row->product_on_process + (int)$row->product_order_placed;
                    $order_placed = ((int)$row->product_on_process + (int)$row->product_order_placed);
                    $percentage = round(($order_placed / $total_stock_added) * 100);
                    if ($stock_available > 0) {
                        $p_data = process_product_data_v2($row, $lang);
                        $p_data['spinner_count'] = "0";
                        $p_data['user_spins'] = [];
                        if ($p_data['is_spinner']) {
                            $user_spins = CartModel::getUserSpinnerByProductAttributeId($p_data['product_id'], $p_data['product_attrb_id'], $user_id);
                            $p_data['spinner_count'] = (string)count($user_spins);
                            $p_data['user_spins'] = $user_spins;
                        }
                        $featured_products_list[] = $p_data;
                    }
                } else {
                    $attributes = Product::getProductAttributeId($row->product_id);
                    foreach ($attributes as $attrib_row) {
                        $subdata = Product::getAllProductsWithAttributes($$request->country_id, $user_id, $lang, "", " order by campaigns_expiry_uts asc ", $row->product_id, $attrib_row->product_attribute_id);
                        foreach ($subdata as $sub_row) {
                            $stock_available = ((float)$sub_row->stock_quantity - (float)$sub_row->product_on_process);
                            $total_stock_added = $row->stock_quantity + (int)$row->product_on_process + (int)$row->product_order_placed;
                            $order_placed = ((int)$row->product_on_process + (int)$row->product_order_placed);
                            $percentage = round(($order_placed / $total_stock_added) * 100);
                            if ($stock_available > 0) {
                                $p_data = process_product_data_v2($row, $lang);
                                $p_data['spinner_count'] = "0";
                                $p_data['user_spins'] = [];
                                if ($p_data['is_spinner']) {
                                    $user_spins = CartModel::getUserSpinnerByProductProductAttributeId($p_data['product_id'], $p_data['product_attrb_id'], $user_id);
                                    $p_data['spinner_count'] = (string)count($user_spins);
                                    $p_data['user_spins'] = $user_spins;
                                }
                                $featured_products_list[] = $p_data;
                                break 2;
                            }
                        }
                    }
                }
            }

            $o_data['featured_products'] = convertNumbersToStrings($featured_products_list);
            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }
}
