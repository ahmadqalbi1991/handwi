<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignModel;
use App\Models\Cart;
use App\Models\CartModel;
use App\Models\ConfigModel;
use App\Models\CountryModel;
use App\Models\MyShop;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\PromoCodeCampaign;
use App\Models\UserTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Kreait\Firebase\Contract\Database;

class CartController extends Controller
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function applyCode(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'promo_code' => 'required',
                'product_id' => 'required',
            ], [
                'promo_code.required' => __('messages.validation.required', ['field' => __('messages.common_messages.promo_code')]),
                'product_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_id')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $o_data = [];
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $promo_code = PromoCode::where('promo_code', $request->promo_code)->first();
            $campaign = Product::getProduct($request->product_id);
            $discount_price = $discount = 0;

            if ($promo_code) {
                $end_date = Carbon::parse($promo_code->end_date);

                if (!Carbon::now()->isAfter($end_date)) {
                    if ($promo_code->all_campaigns) {
                        if ($promo_code->type === 'percentage') {
                            $discount_price = ($campaign->sale_price * $promo_code->value) / 100;
                        } else {
                            $discount_price = $campaign->sale_price - $promo_code->value;
                        }
                        $discount = $promo_code->value;
                        $status = '1';
                        $message = __('messages.success.promo_code_applied');
                    } else {
                        $campaign_exists = PromoCodeCampaign::where([
                            'promo_code_id' => $promo_code->id,
                            'campaign_id' => $campaign->campaigns_id
                        ])->first();

                        if ($campaign_exists) {
                            if ($promo_code->type === 'percentage') {
                                $discount_price = ($campaign->sale_price * $promo_code->value) / 100;
                            } else {
                                $discount_price = $campaign->sale_price - $promo_code->value;
                            }
                            $discount = $promo_code->value;
                            $status = '1';
                            $message = __('messages.success.promo_code_applied');
                        } else {
                            $status = '0';
                            $message = __('messages.errors.promo_code_not_found');
                        }
                    }
                } else {
                    $status = '0';
                    $message = __('messages.errors.promo_code_expire');
                }
            } else {
                $status = '0';
                $message = __('messages.errors.promo_code_not_found');
            }

            $o_data['discount'] = $discount;
            $o_data['discount_price'] = $discount_price;
            $o_data['promo_code'] = convertNumbersToStrings($promo_code->toArray());

            return return_response($status, 200, $message, [], convertNumbersToStrings($o_data));
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function donateProducts(Request $request)
    {
        try {
            $access_token = $request->access_token;
            $user_id = null;
            $message = '';
            $status = '0';

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $user_id = $user->user_id;

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

            $device_cart_id = $request->device_cart_id;
            $product_id = $request->product_id;
            $product_attribute_id = $request->product_attribute_id;
            $lang_code = $request->lang_code ? $request->lang_code : 1;

            $i_data['user_id'] = $user_id > 0 ? $user_id : 0;
            $i_data['product_id'] = $product_id;
            $i_data['product_attribute_id'] = $product_attribute_id;
            $i_data['anonimous_id'] = $device_cart_id;

            $product_data = Product::getProductByAttributeId($product_id, $product_attribute_id, $user_id, $lang_code);
            if (!empty($product_data)) {
                if ($product_data->product_status == 0) {
                    $status = "4";
                    $message = __('messages.errors.product_inactive');
                } else {
                    if ($user_id > 0) {
                        $product_cart_data = Cart::get_product_cart([
                            "product_id" => $i_data['product_id'],
                            "product_attribute_id" => $i_data['product_attribute_id'],
                            "user_id" => $user_id
                        ]);
                    } else {
                        $product_cart_data = Cart::get_product_cart([
                            "product_id" => $i_data['product_id'],
                            "product_attribute_id" => $i_data['product_attribute_id'],
                            "anonimous_id" => $i_data['anonimous_id'],
                            "user_id" => 0
                        ]);
                    }

                    if (!empty($product_cart_data)) {
                        $donate = $request->is_donate;
                        Cart::where(["cart_id" => $product_cart_data->cart_id])->update(["is_donate" => $donate]);
                        $status = "1";
                        if ($donate == 0) {
                            $message = __('messages.errors.not_donate');
                        } else {
                            $message = __('messages.success.donated');
                        }
                    }
                }

                if ($user_id > 0) {
                    $condition = " and cart.user_id = '{$user_id}' and cart.product_id =" . $i_data['product_id'];
                } else {
                    $condition = " and cart.anonimous_id     = '{$device_cart_id}' and cart.user_id = 0 and cart.product_id =" . $i_data['product_id'];
                }

                $product_cart_data = Cart::get_cart_products($user_id, $lang_code, $condition);
                $data_donate = '';
                foreach ($product_cart_data as $row) {
                    $data_donate = $row->is_donate;
                }

                $o_data["id_donate"] = (string)$data_donate;
                return return_response($status, 200, $message, [], $o_data);
            } else {
                return return_response('0', 200, __('messages.errors.no_product'));
            }
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function addToCart(Request $request)
    {
        try {
            $user_id = 0;
            $total_products = 0;
            $total_tickets = 0;
            $total_quantity = 0;

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $user_id = $user->user_id;

            $validator = Validator::make($request->all(), [
                'product_id' => 'required|numeric',
                'product_attribute_id' => 'required|numeric',
                'quantity' => 'required|integer|min:1',
                'device_cart_id' => 'required|string|max:100',
            ], [
                'product_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_id')]),
                'product_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_id')]),
                'product_attribute_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_attribute_id')]),
                'product_attribute_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_attribute_id')]),
                'quantity.required' => __('messages.validation.required', ['field' => __('messages.common_messages.quantity')]),
                'quantity.min' => __('messages.validation.min_length', ['field' => __('messages.common_messages.quantity'), 'min_length' => 1]),
                'quantity.integer' => __('messages.validation.integer', ['field' => __('messages.common_messages.quantity')]),
                'device_cart_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.device_cart_id')]),
                'device_cart_id.max' => __('messages.validation.min_length', ['field' => __('messages.common_messages.device_cart_id'), 'max_length' => 100]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $quantity = $request->quantity;
            $i_data['user_id'] = $user_id;
            $i_data['product_id'] = $request->product_id;
            $i_data['product_attribute_id'] = $request->product_attribute_id;
            $i_data['anonimous_id'] = $request->device_cart_id;
            $i_data['order_placed'] = 0;
            $i_data['quantity'] = (int)$quantity;
            $i_data['cart_created_date'] = gmdate("Y-m-d H:i:s");
            $i_data['share_redeem_code'] = $request->share_redeem_code;

            $product_data = Product::getProductByAttributeId($request->product_id, $request->product_attribute_id, $user_id, $request->language);
            if (!empty($product_data)) {
                if ($product_data->product_status == 0) {
                    $status = "4";
                    $message = __('messages.errors.product_inactive');
                } else {
                    $order_placed = ((int)$product_data->product_on_process);
                    $in_stock = $product_data->stock_quantity - $order_placed;

                    if ($user_id > 0) {
                        $product_cart_data = Cart::get_product_cart([
                            "product_id" => $i_data['product_id'],
                            "product_attribute_id" => $i_data['product_attribute_id'],
                            "user_id" => $user_id
                        ]);

                    } else {
                        $product_cart_data = Cart::get_product_cart(["product_id" => $i_data['product_id'],
                            "product_attribute_id" => $i_data['product_attribute_id'],
                            "anonimous_id" => $i_data['anonimous_id'],
                            "user_id" => 0]);
                    }

                    if ($product_cart_data) {
                        if (!$request->update_qty)
                            $quantity = $product_cart_data->quantity + $quantity;

                        if ($quantity <= $in_stock) {
                            $updatedate = [];
                            $updatedate['quantity'] = $quantity;

                            if ($product_cart_data->share_redeem_code == '' && $request->share_redeem_code) {
                                $updatedate['share_redeem_code'] = $request->share_redeem_code;
                            }

                            Cart::where("cart_id", $product_cart_data->cart_id)->update($updatedate);
                            $status = "1";
                            $message = __('messages.success.added_cart');
                        } else {
                            $status = "3";
                            $message = __('messages.errors.unable_increase');
                        }
                    } else {
                        if ($quantity <= $in_stock) {
                            Cart::create($i_data);
                            $status = "1";
                            $message = __('messages.success.added_cart');
                        } else {
                            $status = "3";
                            $message = __('messages.errors.unable_add');
                        }
                    }
                }

                if ($user_id > 0) {
                    $condition = " and cart.user_id = '{$user_id}'";
                } else {
                    $condition = " and cart.anonimous_id	 = '{$request->device_cart_id}' and cart.user_id = 0 ";
                }

                $product_cart_data = Cart::get_cart_products($user_id, $request->language, $condition);

                foreach ($product_cart_data as $row) {
                    $total_products++;
                    $total_quantity += $row->cart_quantity;
                    $total_tickets += $row->cart_quantity;
                }
            } else {
                $status = '3';
                $message = __('messages.errors.no_product');
            }

            $o_data["totalProducts"] = (string)$total_products;
            $o_data["totalTickets"] = (string)$total_tickets;
            $o_data["totalQuanity"] = (string)$total_quantity;

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function placeOrder(Request $request)
    {
        try {
            $status = "0";
            $message = "";
            $validation_errors = [];
            $spinnerdata = [];
            $user_id = '';
            $user_email_id = '';
            $o_data = [];
            $sub_total = 0;
            $tax = 0;
            $p_tax = 0;
            $promo_code = null;
            $total_shipping_charge = 0;
            $grand_total = 0;
            $discount_price = 0;
            $order_block_id = 0;
            $delivery_type = 2;
            $discount = 0;
            $user_available_points = 0;
            $promo_code_applied = '0';
            $balance_points = 0;
            $user_available_points = 0;
            $cart_products_list = [];
            $language = $request->has('language') ? $request->language : 1;
            $config = ConfigModel::where(['config_key' => 'single_point_bc_value', 'config_status' => 1])->first();

            $validator = Validator::make($request->all(), [
                'points_to_redeem' => 'gt:0',
                'shipping_address_id' => 'required',
                'payment_type' => 'required'
            ], [
                'points_to_redeem.gt' => __('messages.validation.greater_than_zero', ['field' => __('messages.common_messages.points_to_redeem')]),
                'shipping_address_id.required' => __('messages.validation.greater_than_zero', ['field' => __('messages.common_messages.shipping_address_id')]),
                'payment_type.required' => __('messages.validation.greater_than_zero', ['field' => __('messages.common_messages.payment_type')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 500, '', $validator->errors());
            }

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $invoiceId = 0;
            $address_id = $request->shipping_address_id;
            $PStatus = 'A';
            $pay_with_points = 0;
            if ($request->has('pay_with_points')) {
                $pay_with_points = $request->pay_with_points;
            }

            $points_to_redeem = 0;
            if ($request->has('pay_with_points')) {
                $points_to_redeem = $request->pay_with_points;
            }

            if ($request->has('promo_code_id')) {
                $promo_code_exists = PromoCode::where('id', $request->promo_code_id)->first();
                if (!$promo_code_exists) {
                    return return_response('0', 200, 'Promo code is invalid');
                } else {
                    $end_date = Carbon::parse($promo_code_exists->end_date);
                    $promo_code_applied = '1';

                    if (Carbon::now()->isAfter($end_date)) {
                        return return_response('0', 200, 'Promo code is expired');
                    }
                    $promo_code = $promo_code_exists->promo_code;
                }
            }

            $total_used_points = $user->used_points;
            $user_available_points = $user->user_points - $user->used_points;
            $used_points = 0;
            $donate = $request->donate;

            DB::beginTransaction();

            $country_id = $request->country_id != '' ? $request->country_id : 11;
            $country = CountryModel::where('country_id', $country_id)->first();
            $condition = " and cart.user_id = '{$user->user_id}'";

            $product_cart_data = CartModel::getCartProducts($user->user_id, $language, $condition);

            if (count($product_cart_data) > 0) {
                $is_any_product_out_of_stock = false;
                $is_any_campaign_expired = false;
                $is_donated = 1;
                $spinnerdata = [];

                foreach ($product_cart_data as $row) {
                    $process_product_cart_data = process_product_data($row, $language, $promo_code);
                    $process_product_cart_data['cart_quantity'] = (string)$row->cart_quantity;
                    $process_product_cart_data['out_of_stock'] = "0";
                    $discount += ($process_product_cart_data['discount'] * $row->cart_quantity);
                    $spinner = [];
                    if ($process_product_cart_data['is_spinner'] == 1) {
                        $spinner['spinner_user_id'] = $user->user_id;
                        $spinner['created_spinner_date'] = date("Y-m-d H:i:s");
                        $spinner['spinner_product_id'] = $process_product_cart_data['product_id'];
                        $spinner['campaigns_id'] = $process_product_cart_data['campaigns_id'];
                        $spinnerdata[] = $spinner;
                    }

                    if ($row->is_donate == 0) {
                        $is_donated = 0;
                    }

                    if ($process_product_cart_data["campaigns_remaining_uts"] <= 0) {
                        $is_any_campaign_expired = true;
                    }

                    if ($row->cart_quantity > $process_product_cart_data['stock_available']) {
                        if (!$row->allow_back_order) {
                            $is_any_product_out_of_stock = true;
                            $process_product_cart_data['out_of_stock'] = "1";
                        }
                    }

                    $temp_total = $row->sale_price * $row->cart_quantity;

                    $product_tax = 0;
                    $shipping_charge = 0;
                    $product_total = 0;

                    if ($row->product_taxable) {
                        $tax_array = calculate_tax($country_id, $temp_total);
                        $product_tax = $tax_array['tax_amount'];
                        $tax += $product_tax;
                        $temp_total = $tax_array['product_without_tax'];
                    }

                    $product_total = $temp_total + $shipping_charge + $product_tax;
                    $process_product_cart_data["is_donate"] = (string)$row->is_donate;
                    $process_product_cart_data["sub_total"] = (string)$temp_total;
                    $process_product_cart_data["tax"] = (string)$product_tax;
                    $process_product_cart_data["shipping_charge"] = (string)$shipping_charge;
                    $process_product_cart_data["product_total"] = (string)$product_total;

                    $sub_total += $temp_total;
                    $total_shipping_charge += $shipping_charge;
                    $cart_products_list[] = $process_product_cart_data;
                }

                $grand_total = ($sub_total - $discount) + $tax + $shipping_charge;

                if ($is_any_product_out_of_stock) {
                    $status = "3";
                    $message = __("messages.errors.some_out_of_stock");
                } elseif ($is_any_campaign_expired) {
                    $status = "3";
                    $message = __("messages.errors.some_campaign_expired");
                } else if ($user_available_points <= ORDER_MIN_POINT && $pay_with_points == 1) {
                    $status = "3";
                    $message = " Minimum " . ORDER_MIN_POINT . " Ma7zouz Points Required ";
                } else {
                    $actual_amount_paid = $grand_total;
                    $redeemed_amount = 0;
                    $balance_points = 0;
                    $used_points = 0;
                    if ($pay_with_points && $points_to_redeem > 0) {
                        if ($pay_with_points > $user_available_points)
                            $pay_with_points = $user_available_points;

                        $user_points_to_reedem = $points_to_redeem * $config->config_value;
                        $user_points_to_reedem_bc = $points_to_redeem * $config->config_value;

                        if (($grand_total - $user_points_to_reedem_bc) < 0) {
                            $actual_amount_paid = 0;
                            $actual_amount_paid_bc = 0;
                            $redeemed_amount = $grand_total;
                            $balance_points = ($user_points_to_reedem_bc - $grand_total) / $config->config_value;
                            $used_points = $grand_total / $config->config_value;
                        } else if (($grand_total - $user_points_to_reedem_bc) == 0) {
                            $actual_amount_paid = 0;
                            $actual_amount_paid_bc = 0;
                            $redeemed_amount = $grand_total;
                            $balance_points = 0;
                            $used_points = $grand_total / $config->config_value;
                        } else {
                            $redeemed_amount = $user_points_to_reedem;
                            $actual_amount_paid = $grand_total - $user_points_to_reedem;
                            $balance_points = 0;
                            $used_points = $redeemed_amount / $config->config_value;
                        }
                    } else {
                        $pay_with_points = 0;
                    }

                    $i_data['shipping_type'] = "0";
                    $i_data['sub_total'] = $sub_total;
                    $i_data['shipping_charge'] = $total_shipping_charge;
                    $i_data['discount_price'] = $discount;

                    $i_data['vat_price'] = $tax;
                    $i_data['total_price'] = $grand_total;
                    $i_data['payment_status'] = $request->payment_status;
                    $i_data['delivery_status'] = 0;
                    $i_data['payment_type'] = 1;
                    $i_data['invoiceId'] = $invoiceId;
                    $i_data['payment_date'] = date("Y-m-d H:i:s");
                    $i_data['order_placed_date'] = date("Y-m-d H:i:s");
                    $i_data['payment_ref'] = $request->payment_ref;

                    $i_data['shipping_address_id'] = $address_id;
                    $i_data['order_placed_date'] = date("Y-m-d H:i:s");

                    $i_data['transaction_id_no'] = (string)time();

                    $i_data['user_id'] = $user->user_id;
                    $i_data['coupon_code'] = "";
                    $i_data['actual_amount_paid'] = $actual_amount_paid;
                    $i_data['redeemed_amount'] = $redeemed_amount;
                    $i_data['donation'] = $donate;
                    $i_data['used_points'] = $used_points;

                    CartModel::deleteTempOrder($user->user_id);
                    $order_block_id = CartModel::createTempOrder($i_data);
                    CartModel::updateTempOrder(["order_block_id" => $order_block_id], ["product_order_id" => $order_block_id]);

                    $i_product_data = [];
                    foreach ($cart_products_list as $key => $row) {
                        $p_data = [];
                        $p_data['product_id'] = $row['product_id'];
                        $p_data['product_attribute_id'] = $row['product_attrb_id'];
                        $p_data['purchase_qty'] = $row['cart_quantity'];
                        $p_data['teyar_commission'] = 0;
                        $p_data['category_commission'] = 0;
                        $p_data['unit_price'] = $row['sale_price'];
                        $p_data['unit_shipping_charge'] = 0;
                        $p_data['product_sub_price'] = $row['sub_total'];
                        $p_data['product_total_tax'] = $row['tax'];
                        $p_data['product_total_shipping_charge'] = $row["shipping_charge"];
                        $p_data['product_total'] = $row['product_total'];
                        $p_data['order_block_id'] = $order_block_id;
                        $p_data['shipping_type'] = 0;
                        $p_data['user_id'] = $user->user_id;
                        $p_data['delivery_type'] = $delivery_type;
                        $p_data['is_donate'] = $row['is_donate'];
                        $p_data['is_spinner'] = $row['is_spinner'];
                        $p_data['campaign_id'] = $row['campaigns_id'];

                        $i_product_data[] = $p_data;
                    }

                    CartModel::createBatchTempOrderProducts($i_product_data);
                    CartModel::where('user_id', Auth::user()->user_id)->delete();
                    DB::commit();

                    $this->payment_success($i_data['transaction_id_no'], $spinnerdata);
                    $status = "1";
                    $message = __("messages.success.cart_order_success");

                    $title="Payment Transaction";
                    $description = "You have successfully done the payment";
                    $notification_id = time();
                    $ntype = 'new_order_placed';
                    $order_id=$order_block_id;
                    $status=1;
                    if(!empty($user->firebase_user_key)){
                    $notification_data["Notifications/" .$user->firebase_user_key."/" . $notification_id] = [
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
                }

                    $user_device_token=$user->user_device_token;
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
            } else {
                $status = "3";
                $message = __("messages.errors.one_required");
            }

            $o_data["order_id"] = (string) $order_block_id;
            $o_data["order_no"] = "MZ".date("Ymd").$order_block_id;

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            dd($exception);
            DB::rollBack();
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function payment_success($transaction_id_no = "", $spinner = array())
    {
        $config = ConfigModel::where(['config_key' => 'products_share_cashpoint', 'config_status' => 1])->first();
        $temp_order_row = CartModel::getTempOrderDetails(["transaction_id_no" => $transaction_id_no]);
        if ($temp_order_row) {
            $i_data['order_block_id'] = $temp_order_row->order_block_id;
            $i_data['shipping_type'] = $temp_order_row->shipping_type;
            $i_data['sub_total'] = $temp_order_row->sub_total;
            $i_data['shipping_charge'] = $temp_order_row->shipping_charge;
            $i_data['discount_price'] = $temp_order_row->discount_price;
            $i_data['vat_price'] = $temp_order_row->vat_price;
            $i_data['total_price'] = $temp_order_row->total_price;
            $i_data['payment_status'] = $temp_order_row->payment_status;
            $i_data['delivery_status'] = $temp_order_row->delivery_status;
            $i_data['payment_type'] = $temp_order_row->payment_type;
            $i_data['invoiceId'] = $temp_order_row->invoiceId;
            $i_data['payment_date'] = $temp_order_row->payment_date;
            $i_data['shipping_address_id'] = $temp_order_row->shipping_address_id;
            $i_data['order_placed_date'] = $temp_order_row->order_placed_date;
            $i_data['transaction_id_no'] = $temp_order_row->transaction_id_no;
            $i_data['user_id'] = $temp_order_row->user_id;
            $i_data['coupon_code'] = $temp_order_row->coupon_code;
            $i_data['actual_amount_paid'] = $temp_order_row->actual_amount_paid;
            $i_data['redeemed_amount'] = $temp_order_row->redeemed_amount;
            $i_data['order_placed'] = 1;
            $i_data['donation'] = $temp_order_row->donation;
            $i_data['used_points'] = $temp_order_row->used_points;
            $temp_order_product_details = CartModel::getTempOrderProductDetails(array("order_block_id" => $temp_order_row->order_block_id));

            $i_product_data = [];
            CartModel::createOrder($i_data);//bimal

            $sel_user_id = $temp_order_row->user_id;
            $order_block_id = $temp_order_row->order_block_id;
            $spinnerdata = [];
            foreach ($temp_order_product_details as $key => $row) {
                $p_data = [];
                $p_data['product_id'] = $row->product_id;
                $p_data['product_attribute_id'] = $row->product_attribute_id;
                $p_data['purchase_qty'] = $row->purchase_qty;
                $p_data['teyar_commission'] = $row->teyar_commission;
                $p_data['category_commission'] = $row->category_commission;
                $p_data['unit_price'] = $row->unit_price;
                $p_data['order_block_id'] = $temp_order_row->order_block_id;   //$new_order_block_id
                $p_data['unit_shipping_charge'] = $row->unit_shipping_charge;
                $p_data['product_sub_price'] = $row->product_sub_price;
                $p_data['product_total_tax'] = $row->product_total_tax;
                $p_data['product_total_shipping_charge'] = $row->product_total_shipping_charge;
                $p_data['product_total'] = $row->product_total;
                $p_data['shipping_type'] = $row->shipping_type;
                $p_data['user_id'] = $row->user_id;
                $p_data['delivery_type'] = $row->delivery_type;
                $p_data['is_donate'] = $row->is_donate;
                $p_data['is_spinner'] = $row->is_spinner;
                $p_data['campaign_id'] = $row->campaign_id;
                $product_data = CartModel::getProduct($row->product_id);
                $product_data = $product_data[0];
                $history_id = CartModel::createOrderProducts($p_data);//bimal
                $share_code_data = CartModel::getShopProductsByCode($row->share_redeem_code);

                if (count((array)$share_code_data) != 0) {
                    $earn_user_id = $share_code_data->user_id;
                    $total_share_cashpoint = ($row->unit_price * $config->config_value) / 100;
                    CartModel::createCashPointHistory(["cash_points_user_id" => $earn_user_id,
                        "cash_points_status" => 1,
                        "cash_points_redeem_date" => $temp_order_row->order_placed_date,
                        "cash_points_total" => $total_share_cashpoint,
                        "order_block_id" => $temp_order_row->order_block_id,
                        "description" => 'You have Earn' . $total_share_cashpoint . ' Points from share',
                        "from_refer" => 1,
                        'history_id' => $history_id
                    ]);
                    $user_data = UserTable::where('user_id', $earn_user_id)->first();
                    $my_points = round($user_data->user_points + $total_share_cashpoint, 2);
                    UserTable::where(["user_id" => $earn_user_id])->update(["user_points" => $my_points]);
                }

                $spinner = [];
                $spinner['spinner_user_id'] = Auth::user()->user_id;
                $spinner['created_spinner_date'] = date("Y-m-d H:i:s");
                $spinner['order_block_id'] = $temp_order_row->order_block_id;
                $spinner['spinner_product_id'] = $row->product_id;
                $spinner['campaign_id'] = $row->campaign_id;
                $spinner['history_id'] = $history_id;
                $spinnerdata[] = $spinner;
                $product_tickets = [];
                $draw_slip_tickets = [];
                $product_tickets1 = [];
                $draw_slip_tickets1 = [];
                $count = 0;
                $timeval = time();
                for ($i = 0; $i < ($row->purchase_qty); $i++) {
                    $ticket_number = $timeval . $row->history_id . $i;
                    $product_tickets[] = ["order_block_id" => $temp_order_row->order_block_id,
                        "history_id" => $history_id,
                        "product_id" => $row->product_id,
                        "product_attribute_id" => $row->product_attribute_id,
                        "ticket_number" => $ticket_number];

                    $draw_slip_tickets[] = ["user_id" => $row->user_id,
                        "order_block_id" => $temp_order_row->order_block_id,
                        "campaign_id" => $product_data->campaigns_id,
                        "product_attribute_id" => $row->product_attribute_id,
                        "draw_slip_number" => $ticket_number];
                    $count++;
                }


                if ($row->is_donate == 1) {
                    for ($i = 0; $i < ($row->purchase_qty); $i++) {
                        $ticket_number = $timeval . $row->history_id . $i;
                        $product_tickets1[] = ["order_block_id" => $temp_order_row->order_block_id,
                            "history_id" => $history_id,
                            "product_id" => $row->product_id,
                            "product_attribute_id" => $row->product_attribute_id,
                            "ticket_number" => $ticket_number];

                        $draw_slip_tickets1[] = ["user_id" => $row->user_id,
                            "order_block_id" => $temp_order_row->order_block_id,
                            "campaign_id" => $product_data->campaigns_id,
                            "product_attribute_id" => $row->product_attribute_id,
                            "draw_slip_number" => $ticket_number];
                    }
                }

                CartModel::createBatchOrderTicketNumber($product_tickets);
                CartModel::createBatchDrawSlip($draw_slip_tickets);
                if ($row->is_donate > 0) {
                    CartModel::createBatchOrderTicketNumber($product_tickets1);
                    CartModel::createBatchDrawSlip($draw_slip_tickets1);
                }
                $product_data = Product::getProductByAttributeId($row->product_id, $row->product_attribute_id);
                if ($product_data) {
                    $quantity = $product_data->stock_quantity - $row->purchase_qty;
                    CartModel::updateStock(["stock_quantity" => $quantity], ["product_id" => $row->product_id]);
                }
            }

            if (!empty($spinnerdata)) {
                CartModel::spinnerHistory($spinnerdata);
            }
            $user_data = UserTable::where('user_id', $temp_order_row->user_id)->first();
            if ($temp_order_row->used_points > 0) {
                CartModel::createCashPointHistory(["cash_points_user_id" => $temp_order_row->user_id,
                    "cash_points_status" => 4,
                    "cash_points_redeem_date" => $temp_order_row->order_placed_date,
                    "cash_points_total" => $temp_order_row->used_points,
                    "order_block_id" => $temp_order_row->order_block_id,
                    "description" => 'You have Redeemed ' . $temp_order_row->used_points . ' Points']);

                UserTable::where(["user_id" => $temp_order_row->user_id])->update(["used_points" => $user_data->used_points + $temp_order_row->used_points]);
            }

            CartModel::deleteUserCart($temp_order_row->user_id);
            CartModel::deleteTempOrder($temp_order_row->user_id);
            $cash_point_earning_perc =  ConfigModel::where(['config_key' => 'cash_point_earning_perc', 'config_status' => 1])->first();
            $newpoints = ($temp_order_row->total_price * $cash_point_earning_perc->config_value) / 100;

            if ($newpoints > 0) {
                CartModel::createCashPointHistory(["cash_points_user_id" => $temp_order_row->user_id,
                    "cash_points_status" => 1,
                    "cash_points_redeem_date" => $temp_order_row->order_placed_date,
                    "cash_points_total" => round($newpoints, 2),
                    "order_block_id" => $temp_order_row->order_block_id,
                    "description" => 'You have Earn' . round($newpoints, 2) . ' Points',
                    "from_refer" => 0,
                    'history_id' => $history_id]);
            }

            $user_point = round($user_data->user_points + $newpoints, 2);
            UserTable::where(["user_id" => $temp_order_row->user_id])->update(["user_points" => $user_point]);

            //Firebase Notifications
//            if ($this->db->trans_status() !== FALSE) {
//
//                $this->load->model('Api_push_model', 'Api_push_model');
//
//                $sql = "SELECT U.* FROM user_table AS U WHERE U.user_id = '$sel_user_id'";
//
//                $rec = $this->db->query($sql)->result_array();
//
//                if (count($rec) > 0) {
//
//                    foreach ($rec as $row) {
//
//                        $first_name = $row['user_first_name'];
//                        $dev_tockon = $row['user_device_token'];
//                        $firebase_user_key = $row['firebase_user_key'];
//
//                        $title = 'Purchase successful';
//                        $body = 'Hi, ' . $first_name . ' Your purchase has been placed successfully';
//                        $clickaction = 'purchase';
//
//                        $params = array('type' => $clickaction, 'campaign_id' => '0');
//
//                        $params = ['notificationType' => $clickaction,
//                            'campaign_id' => "0",
//                            'order_id' => $order_block_id,
//                            'productId' => "0",
//                            'title' => $title,
//                            'description' => $body];
//
//                        $this->Api_push_model->sendNotificationSingle($dev_tockon, $title, $body, $params, $clickaction);
//
//                        // $push_data['data']['fromUserID']              = '';
//                        // $push_data['data']['touserID']                = $firebase_user_key;
//                        // $push_data['data']['title']                   = $title;
//                        // $push_data['data']['message']                 = $body;
//                        // $push_data['data']['order_id']                = $order_block_id;
//                        // $push_data['data']['image']                   = '';
//                        // $push_data['data']['msg_type']                = $clickaction;
//                        // $push_data['data']['notification_id']         = '';
//
//                        $push_data['data']['fromUserID'] = '';
//                        $push_data['data']['touserID'] = $firebase_user_key;
//                        $push_data['data']['title'] = $title;
//                        $push_data['data']['description'] = $body;
//                        $push_data['data']['order_id'] = $order_block_id;
//                        $push_data['data']['productId'] = '';
//                        $push_data['data']['notificationType'] = $clickaction;
//                        $push_data['data']['msg_type'] = $clickaction;
//                        $push_data['data']['message'] = $body;
//                        $push_data['data']['campaign_id'] = '';
//
//                        $this->load->view("firebase_notification", $push_data);
//                    }
//                }
//
//
//                // $status     = "1";
//                // $message    = "Success" ;
//            } else {//echo "sdfsd";exit;
//                // $status     = "3";
//                // $message    = "Unable to complete the transaction" ;
//            }
        }
    }

    public function checkout(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'points_to_redeem' => 'gt:0'
            ], [
                'points_to_redeem.gt' => __('messages.validation.greater_than_zero', ['field' => __('messages.common_messages.points_to_redeem')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 500, '', $validator->errors());
            }

            $lang = $request->has('language') ? $request->language : 1;
            $message = "";
            $user_address_list = [];
            $o_data = [];
            $sub_total = 0;
            $tax = 0;
            $shipping_charge = 0;
            $total_products = 0;
            $total_tickets = 0;
            $discount = 0;
            $promo_code = $request->has('promo_code') ? $request->promo_code : '';
            $promo_code_applied = '0';
            $total_quantity = 0;
            $config = ConfigModel::where(['config_key' => 'single_point_bc_value', 'config_status' => 1])->first();
            $cart_products_list = [];
            $promo_code_exists = null;
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            if (!empty($promo_code)) {
                $promo_code_exists = PromoCode::where('promo_code', $promo_code)->first();
                if (!$promo_code_exists) {
                    return return_response('0', 200, 'Promo code is invalid');
                } else {
                    $end_date = Carbon::parse($promo_code_exists->end_date);
                    $promo_code_applied = '1';

                    if (Carbon::now()->isAfter($end_date)) {
                        return return_response('0', 200, 'Promo code is expired');
                    }
                }
            }

            $user_id = $user->user_id;
            $country_id = $request->has('country_id') ? $request->country_id : 108;
            $user_available_points = $user->user_points - $user->used_points;
            $is_donated_all_products = 1;

            process_user_cart_data($user_id, $request->device_cart_id);
            $is_any_product_out_of_stock = false;
            $is_any_campaign_expired = false;
            $condition = " and cart.user_id = '{$user_id}'";
            $product_cart_data = CartModel::getCartProducts($user_id, $lang, $condition);

            foreach ($product_cart_data as $row) {
                $process_product_cart_data = process_product_data_v2($row, $lang, $promo_code);
                $process_product_cart_data['cart_quantity'] = (string)$row->cart_quantity;
                $process_product_cart_data['out_of_stock'] = "0";
                $pay_with_points = $request->pay_with_points;
                $points_to_reedem = (int)$request->points_to_reedem;
                $discount += ($process_product_cart_data['discount']);
                $user_available_points = $user->user_points - $user->used_points;
                $used_points = 0;
                $ticket_qty = $row->cart_quantity;
                if ($row->is_donate == 1) {
                    $ticket_qty = $ticket_qty * 2;
                }
                $process_product_cart_data["totalTickets"] = (string)$ticket_qty;
                if ($process_product_cart_data["campaigns_remaining_uts"] <= 0)
                    $is_any_campaign_expired = true;

                $is_any_campaign_expired = false;
                if ($row->cart_quantity > $process_product_cart_data['stock_available']) {
                    if (!$row->allow_back_order) {
                        $is_any_product_out_of_stock = true;
                        $process_product_cart_data['out_of_stock'] = "1";
                    }
                }

                if ($row->is_donate == 0) {
                    $is_donated_all_products = 0;
                }

                $temp_total = $row->sale_price * $row->cart_quantity;
                $product_tax = 0;
                $product_total = 0;
                if ($row->product_taxable) {
                    $tax_array = calculate_tax($country_id, $temp_total);
                    $product_tax = $tax_array['tax_amount'];
                    $tax += $product_tax;
                    $temp_total = $tax_array['product_without_tax'];
                }

                $product_total = $temp_total ;
                $process_product_cart_data["is_donate"] = (string)$row->is_donate;
                $process_product_cart_data["sub_total"] = (string)$temp_total;
                $process_product_cart_data["tax"] = (string)$product_tax;
                $process_product_cart_data["shipping_charge"] = (string)$shipping_charge;
                $process_product_cart_data["product_total"] = (string)$product_total;
                $sub_total += $temp_total;
                $arr[] = '';

                if ($row->attribute_values_ids == '') {
                    $product_variation = array();
                } else {
                    $product_variation = CartModel::getVariationBYProductId($row->attribute_values_ids, $lang);
                }

                $process_product_cart_data["attributes"] = $product_variation;
                $cart_products_list[] = $process_product_cart_data;
                $total_products++;
                $total_quantity += $row->cart_quantity;

                if ($row->is_donate == 1) {
                    $total_tickets += $row->cart_quantity * 2;
                } else {
                    $total_tickets += $row->cart_quantity;
                }
            }

            $grand_total = ($sub_total - $discount) + $tax + $shipping_charge;
            if ($is_any_product_out_of_stock) {
                $status = "3";
                $message = __("messages.errors.some_out_of_stock");
            } else if ($is_any_campaign_expired) {
                $status = "3";
                $message = __("messages.errors.some_campaign_expired");
            } else
                $status = "1";

            $default_address_data = UserTable::getUserDefaultAddress($user_id, $lang);

//            if (!$default_address_data) {
                $address_data = UserTable::getUserAddress($user_id, $lang);
                foreach ($address_data as $default_address_data) {
                    $address_row["shiping_details_id"] = (string)$default_address_data->user_shiping_details_id;
                    $address_row["first_name"] = (string)$default_address_data->s_first_name;
                    $address_row["middle_name"] = (string)$default_address_data->s_middle_name;
                    $address_row["last_name"] = (string)$default_address_data->s_last_name;
                    $address_row["cutomer_name"] = (string)$default_address_data->s_first_name . " " . (string)$default_address_data->s_last_name;
                    $address_row["street_name"] = (string)$default_address_data->user_shiping_details_street;
                    $address_row["building_name"] = (string)$default_address_data->user_shiping_details_building;
                    $address_row["floor_no"] = (string)$default_address_data->user_shiping_details_floorno;
                    $address_row["flat_no"] = (string)$default_address_data->user_shiping_details_flatno;
                    $address_row["location"] = (string)$default_address_data->user_shiping_details_loc;
                    $address_row["land_mark"] = (string)$default_address_data->user_shiping_details_landmark;
                    if ($default_address_data->user_shiping_details_city == '-1') {
                        $address_row["city_name"] = (string)$default_address_data->user_shiping_details_other_city;
                    } else {
                        $address_row["city_name"] = (string)$default_address_data->city_name;
                    }
                    $address_row["country_name"] = (string)$default_address_data->country_name;
                    $address_row["phone_no"] = (string)$default_address_data->user_shiping_details_phone;
                    $address_row["dial_code"] = (string)$default_address_data->user_shiping_details_dial_code;
                    $address_row["default_address"] = (string)$default_address_data->default_address_status;
                    $address_row["address_type"] = (string)$default_address_data->user_shiping_details_loc_type;
                    $address_row["city_id"] = (string)$default_address_data->user_shiping_details_city;
                    $address_row["country_id"] = (string)$default_address_data->user_shiping_country_id;
                    $address_row["latitude"] = (string)$default_address_data->user_shiping_details_latitude;
                    $address_row["longitude"] = (string)$default_address_data->user_shiping_details_longitude;

                    $user_address_list[] = $address_row;
                }
//            }

            $actual_amount_paid = $grand_total;
            $redeemed_amount = 0;
            $balance_points = 0;
            $used_points = 0;
            $pay_with_points = $request->pay_with_points ? $request->pay_with_points : 0;
            $points_to_reedem = $request->points_to_reedem ? $request->points_to_reedem : 0;
            if ($pay_with_points && $points_to_reedem > 0) {
                if ($pay_with_points > $user_available_points)
                    $pay_with_points = $user_available_points;

                $user_point_value_in_currency = $user_available_points * $config->config_value;
                $user_points_to_reedem = $points_to_reedem * $config->config_value;

                if (($grand_total - $user_points_to_reedem) < 0) {
                    $actual_amount_paid = 0;
                    $actual_amount_paid_bc = 0;
                    $redeemed_amount = $grand_total;

                    $balance_points = ($user_points_to_reedem - $grand_total) / $config->config_value;
                    $used_points = $grand_total / $config->config_value;


                } else if (($grand_total - $user_points_to_reedem) == 0) {
                    $actual_amount_paid = 0;
                    $actual_amount_paid_bc = 0;
                    $redeemed_amount = $grand_total;
                    $balance_points = 0;
                    $used_points = $grand_total / $config->config_value;
                } else {
                    $redeemed_amount = $user_points_to_reedem;
                    $actual_amount_paid = $grand_total - $user_points_to_reedem;
                    $balance_points = 0;
                    $used_points = $redeemed_amount / $config->config_value;
                }
            } else {
                $pay_with_points = 0;
            }
            $o_data["userAddress"] = $user_address_list;

            if (count($product_cart_data) == 0) {
                $status = "3";
                $message .= __("messages.errors.please_add_one");
            }

            $product_cart_data_checkout = CartModel::getCartProductsCheckout($user_id, $lang, $condition);

            if (empty($product_cart_data) && !empty($product_cart_data_checkout)) {
                $status = "5";
                $message = __("messages.errors.sold_out_stock");
            }
            $cash_point_earning_perc = ConfigModel::where('config_key', 'cash_point_earning_perc')->first();
            $newpoints = ($grand_total * $cash_point_earning_perc->config_value) / 100;
            $o_data["cartProducts"] = convertNumbersToStrings($cart_products_list);
            $o_data["userAvailablePoints"] = (string)$user_available_points;
            $subtotal = (float)$sub_total - (float)$discount;
            $o_data["subTotal"] = (string)$subtotal;
            $o_data["tax"] = (string)$tax;
            $o_data["discount"] = (string)$discount;
            $o_data["promo_code_applied"] = $promo_code_applied;

            if (!empty($promo_code) && !empty($promo_code_exists)) {
                $o_data['promo_code_object'] = convertNumbersToStrings($promo_code_exists->toArray());
            }

            $o_data["shippingCharge"] = (string)$shipping_charge;
            $o_data["grandTotal"] = (string)$grand_total;
            $o_data["totalProducts"] = (string)$total_products;
            $o_data["totalTickets"] = (string)$total_tickets;
            $o_data["totalQuantity"] = (string)$total_quantity;
            $o_data["currency"] = "JOD";
            $o_data["is_donated_all_products"] = (string)$is_donated_all_products;
            $o_data['actual_amount_paid'] = (string)$actual_amount_paid;
            $o_data['used_points'] = (string)$used_points;
            $o_data['order_points'] = (string)$newpoints;

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getCart(Request $request)
    {
        try {
            $message = '';
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $validator = Validator::make($request->all(), [
                'device_cart_id' => 'required'
            ], [
                'device_cart_id.required' => __('messages.validation.required', ['field' => 'messages.common_messages.device_cart_id'])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $device_cart_id = $request->input('device_cart_id');
            $country_id = !empty($request->input('country_id')) ? $request->input('country_id') : 2;
            $language = !empty($request->input('language')) ? $request->input('language') : 2;

            if ($user->user_id > 0) {
                $condition = "AND cart.user_id = '{$user->user_id}'";
            } else {
                $condition = "AND cart.anonimous_id = '{$device_cart_id}' AND cart.user_id = 0";
            }

            $product_cart_data = CartModel::getCartProducts($user->user_id, $language, $condition);
            $is_any_product_out_of_stock = false;

            $sub_total = 0;
            $shipping_charge = 0;
            $total_products = 0;
            $total_tickets = 0;
            $total_quantity = 0;
            $cart_products_list = [];
            $tax = 0;

            foreach ($product_cart_data as $row) {
                $process_product_cart_data = process_product_data_v2($row, $language);
                $process_product_cart_data['cart_quantity'] = (string)$row->cart_quantity;
                $process_product_cart_data['out_of_stock'] = "0";

                if ($row->cart_quantity > $process_product_cart_data['stock_available']) {
                    if (!$row->allow_back_order) {
                        $is_any_product_out_of_stock = true;
                        $process_product_cart_data['out_of_stock'] = "1";
                    }
                }

                $temp_total = $row->sale_price * $row->cart_quantity;
                $product_tax = 0;
                $shipping_charge = 0;

                if ($row->product_taxable) {
                    $tax_array = calculate_tax($country_id, $temp_total);
                    $product_tax = $tax_array['tax_amount'];
                    $tax += $product_tax;
                }

                $product_total = $temp_total + $shipping_charge + $product_tax;
                $process_product_cart_data["sub_total"] = (string)$temp_total;
                $process_product_cart_data["tax"] = (string)$product_tax;
                $process_product_cart_data["shipping_charge"] = (string)$shipping_charge;
                $process_product_cart_data["product_total"] = (string)$product_total;
                $sub_total += $temp_total;

                if ($row->attribute_values_ids == '') {
                    $product_variation = array();
                } else {
                    $product_variation = CartModel::getVariationBYProductId($row->attribute_values_ids, $language);
                }

                $process_product_cart_data["attributes"] = $product_variation;
                $cart_products_list[] = $process_product_cart_data;
                $total_products++;
                $total_tickets += $row->cart_quantity;
                $total_quantity += $row->cart_quantity;
            }
            $grand_total = $sub_total + $tax + $shipping_charge;
            if ($is_any_product_out_of_stock) {
                $status = "3";
                $message = __("messages.errors.some_out_of_stock");
            } else {
                $status = "1";
            }

            $o_data["cartProducts"] = $cart_products_list;
            $o_data["subTotal"] = (string)$sub_total;
            $o_data["tax"] = (string)$tax;
            $o_data["shippingCharge"] = (string)$shipping_charge;
            $o_data["grandTotal"] = (string)$grand_total;
            $o_data["totalProducts"] = (string)$total_products;
            $o_data["totalTickets"] = (string)$total_tickets;
            $o_data["totalQuantity"] = (string)$total_quantity;
            $o_data["currency"] = "JOD";

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function clearCart(Request $request) {
        try {
            if (!$request->has('user_id') && !$request->has('device_cart_id')) {
                return return_response('0', 200, '', ['user_id_device_cart_id' => __('messages.validation.user_id_device_cart_id')]);
            }
            if ($request->has('user_id')) {
                CartModel::where('user_id', $request->user_id)->delete();
            } else {
                CartModel::where('anonimous_id', $request->device_cart_id)->delete();
            }

            return return_response('1', 200, __('messages.success.cart_empty'));
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function deleteProductsFromCart(Request $request)
    {
        try {
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

            $language = $request->has('language') ? $request->language : 1;
            $product_cart = CartModel::getProductCart([
                'product_id' => $request->product_id,
                'product_attribute_id' => $request->product_attribute_id,
                'user_id' => $user->user_id,
            ]);

            if (empty($product_cart)) {
                return return_response('0', 200, __('messages.errors.no_cart'));
            }

            $product_cart->delete();
            $condition = " and cart.user_id = '{$user->user_id}'";
            $product_cart_data = CartModel::getCartProducts($user->user_id, $language, $condition);

            $total_products = $total_quantity = $total_tickets = 0;
            foreach ($product_cart_data as $row) {
                $total_products++;
                $total_quantity += $row->cart_quantity;
                $total_tickets += $row->cart_quantity;
            }

            $o_data["totalProducts"] = (string)$total_products;
            $o_data["totalTickets"] = (string)$total_tickets;
            $o_data["totalQuantity"] = (string)$total_quantity;

            return return_response('1', 200, __('messages.success.cart_deleted'), [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function deleteProductsFromCartByDeviceCartId(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|numeric',
                'product_attribute_id' => 'required|numeric',
                'device_cart_id' => 'required',
            ], [
                'product_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_id')]),
                'device_cart_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.device_cart_id')]),
                'product_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_id')]),
                'product_attribute_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_attribute_id')]),
                'product_attribute_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_attribute_id')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $language = $request->has('language') ? $request->language : 1;
            $product_cart = CartModel::getProductCart([
                'product_id' => $request->product_id,
                'product_attribute_id' => $request->product_attribute_id,
                'user_id' => 0,
                'anonimous_id' => $request->device_cart_id,
            ]);

            if (empty($product_cart)) {
                return return_response('0', 200, __('messages.errors.no_cart'));
            }

            $product_cart->delete();
            $condition = " and cart.anonimous_id	 = '{$request->device_cart_id}' and cart.user_id = 0 ";;
            $product_cart_data = CartModel::getCartProducts(0, $language, $condition);

            $total_products = $total_quantity = $total_tickets = 0;
            foreach ($product_cart_data as $row) {
                $total_products++;
                $total_quantity += $row->cart_quantity;
                $total_tickets += $row->cart_quantity;
            }

            $o_data["totalProducts"] = (string)$total_products;
            $o_data["totalTickets"] = (string)$total_tickets;
            $o_data["totalQuantity"] = (string)$total_quantity;

            return return_response('1', 200, __('messages.success.cart_deleted'), [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function reduceCart(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|numeric',
                'product_attribute_id' => 'required|numeric',
                'quantity' => 'required|numeric|gt:0'
            ], [
                'product_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_id')]),
                'product_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_id')]),
                'product_attribute_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_attribute_id')]),
                'product_attribute_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_attribute_id')]),
                'quantity.required' => __('messages.validation.required', ['field' => __('messages.common_messages.quantity')]),
                'quantity.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.quantity')]),
                'quantity.gt' => __('messages.validation.greater_than_zero', ['field' => __('messages.common_messages.quantity')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $language = $request->has('language') ? $request->language : 1;
            $total_products = $total_quantity = $total_tickets = 0;
            $product_data = Product::getProductByAttributeId($request->product_id, $request->product_attribute_id, $user->user_id, $language);
            if (!empty($product_data)) {
                $product_cart_data = CartModel::getProductCart(array("product_id" => $request->product_id,
                    "product_attribute_id" => $request->product_attribute_id,
                    "user_id" => $user->user_id ));

                if($product_cart_data) {
                    $quantity = $product_cart_data->quantity - $request->quantity;
                    CartModel::where(["cart_id" => $product_cart_data->cart_id])->update(["quantity" => $quantity]);
                    $status     = "1";
                    $message    = __("messages.success.qty_reduced");
                    $condition = " and cart.user_id = '{$user->user_id}'";
                    $product_cart_data = CartModel::getCartProducts($user->user_id, $language, $condition);
                    foreach($product_cart_data as $row) {
                        $total_products++;
                        $total_quantity += $row->cart_quantity;
                        $total_tickets += $row->cart_quantity;
                    }
                }
                else {
                    $status     = "0";
                    $message    = __('messages.errors.no_product');
                }

                $o_data["totalProducts"]    = (string) $total_products;
                $o_data["totalTickets"]     = (string) $total_tickets;
                $o_data["totalQuantity"]    = (string) $total_quantity;
            } else {
                $status = '0';
                $message = __('messages.errors.no_product');
                $o_data = [];
            }

            return return_response($status, 200, $message, [], $o_data);
        }  catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function reduceCartByDevice_cartId(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|numeric',
                'product_attribute_id' => 'required|numeric',
                'device_cart_id' => 'required|numeric',
                'quantity' => 'required|numeric|gt:0'
            ], [
                'product_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_id')]),
                'product_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_id')]),
                'product_attribute_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.product_attribute_id')]),
                'product_attribute_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.product_attribute_id')]),
                'device_cart_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.device_cart_id')]),
                'device_cart_id.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.device_cart_id')]),
                'quantity.required' => __('messages.validation.required', ['field' => __('messages.common_messages.quantity')]),
                'quantity.numeric' => __('messages.validation.numeric', ['field' => __('messages.common_messages.quantity')]),
                'quantity.gt' => __('messages.validation.greater_than_zero', ['field' => __('messages.common_messages.quantity')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $language = $request->has('language') ? $request->language : 1;
            $total_products = $total_quantity = $total_tickets = 0;
            $product_data = Product::getProductByAttributeId($request->product_id, $request->product_attribute_id, 0, $language);
            if (!empty($product_data)) {
                $product_cart_data = CartModel::getProductCart([
                    "product_id" => $request->product_id,
                    "product_attribute_id" => $request->product_attribute_id,
                    "user_id" => 0,
                    "anonimous_id" => $request->device_cart_id
                ]);

                if ($product_cart_data) {
                    $quantity = $product_cart_data->quantity - $request->quantity;
                    CartModel::where(["cart_id" => $product_cart_data->cart_id])->update(["quantity" => $quantity]);
                    $status = "1";
                    $message = __("messages.success.qty_reduced");
                    $condition = " and cart.anonimous_id	 = '{$request->device_cart_id}' and cart.user_id = 0 ";
                    $product_cart_data = CartModel::getCartProducts(0, $language, $condition);
                    foreach ($product_cart_data as $row) {
                        $total_products++;
                        $total_quantity += $row->cart_quantity;
                        $total_tickets += $row->cart_quantity;
                    }
                } else {
                    $status = "0";
                    $message = __('messages.errors.no_product');
                }

                $o_data["totalProducts"] = (string)$total_products;
                $o_data["totalTickets"] = (string)$total_tickets;
                $o_data["totalQuantity"] = (string)$total_quantity;
            } else {
                $status = '0';
                $message = __('messages.errors.no_product');
                $o_data = [];
            }

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function createStripePayment(Request $request) {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100,
                'currency' => 'JOD',
                'payment_method_types' => [$request->payment_type],
            ]);

            $o_data['payment_ref'] = $paymentIntent->client_secret;

            return return_response('1', 200, __('messages.success.payment_token_received'), [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }
}
