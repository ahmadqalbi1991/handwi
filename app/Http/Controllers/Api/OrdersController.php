<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartModel;
use App\Models\MyShop;
use App\Models\OrderModel;
use App\Models\SpinnerModel;
use App\Models\TicketModel;
use App\Models\UserTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use File;

class OrdersController extends Controller
{
    public function getOrderDetails(Request $request) {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $validator = Validator::make($request->all(), [
                'order_id' => 'required'
            ], [
                'order_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.order_id')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $user_address_list = [];
            $order_details = OrderModel::where('order_block_id', $request->order_id)->with('ticketNumber')->first();
            $total_tickets = 0;
            $order_product_details = OrderModel::get_order_detail($request->order_id);
            if ($order_details) {
                $order_details = $order_details->toArray();
                $order_details['ticket_number'] = $order_details['ticket_number']['ticket_number'];
                $order_details['order_placed_date'] = Carbon::parse($order_details['order_placed_date'])->format('d M Y h:i A');
                if ($order_details['order_status'] == 0) {
                    $order_details['order_status'] = 'Pending';
                }

                if ($order_details['order_status'] == 1) {
                    $order_details['order_status'] = 'Confirmed';
                }

                if ($order_details['order_status'] == 2) {
                    $order_details['order_status'] = 'Completed';
                }

                if ($order_details['order_status'] == 3) {
                    $order_details['order_status'] = 'Canceled';
                }

                $address_data = UserTable::getUserAddress($user->user_id, 1);
                foreach ($address_data as $default_address_data) {
                    if ($default_address_data->user_shiping_details_id == $order_details['shipping_address_id']) {
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

                        $user_address_list = $address_row;
                    }
                }
            } else {
                $order_details = [];
            }
            foreach ($order_product_details as $key => $detail) {
                $total_tickets++;
                $detail = (array) $detail;
                $detail['draw_date'] = Carbon::parse($detail['draw_date'])->format('d M Y h:i A');
                $order_product_details[$key] = convertNumbersToStrings($detail);
            }

            $o_data['order_details'] = convertNumbersToStrings($order_details);
            $o_data['order_product_details'] = convertNumbersToStrings($order_product_details);
            $o_data["user_addresses"] = $user_address_list;
            $o_data["total_tickets"] = (string)$total_tickets;

            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getOrdersProducts(Request $request)
    {
        try {
            $message = "";
            $o_data = [];
            $user_address_list = [];
            $order_sub_total = 0;
            $order_grand_total = 0;
            $order_tax_total = 0;
            $order_shipping_charge = 0;
            $total_tickets = 0;
            $used_points = 0;
            $discounted_amount = 0;
            $amount_paid = 0;
            $order_no = "";
            $order_product_list = [];
            $userTimezone = "Etc/GMT";

            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $validator = Validator::make($request->all(), [
                'order_id' => 'required'
            ], [
                'order_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.order_id')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $order_details = OrderModel::where('order_block_id', $request->order_id)->first();
            $order_product_details = OrderModel::get_order_detail($request->order_id);

            if ($order_details) {
                $is_donated = 1;
                $order_no = "BG" . date("Ymd", strtotime($order_details->order_placed_date)) . $request->order_id;
                foreach ($order_product_details as $row) {
                    $processed_product_order_data = process_product_data_v2($row);
                    $processed_product_order_data["unit_price"] = (string)$row->unit_price;
                    $processed_product_order_data["sub_total"] = (string)$row->product_sub_price;
                    $processed_product_order_data["tax"] = (string)$row->product_total_tax;
                    $processed_product_order_data["shipping_charge"] = (string)$row->product_total_shipping_charge;
                    $processed_product_order_data['spin_prize'] = (string)$row->prize;
                    $processed_product_order_data["product_quantity"] = (string)$row->purchase_qty;
                    $processed_product_order_data["order_is_donation"] = (string)$row->is_donate;
                    $processed_product_order_data["order_id"] = (string)$row->order_block_id;
                    $processed_product_order_data["order_date"] = (string)get_date_in_timezone($userTimezone, gmdate("d-m-Y H:i:s", strtotime($row->order_placed_date)), "d M Y h:i A");

                    $ticket_numbers_data = TicketModel::select('ticket_number')->where(["order_block_id" => $row->order_block_id, "product_id" => $row->product_id])->get();

                    $ticket_list = [];
                    foreach ($ticket_numbers_data as $trow) {
                        $ticket_process = $trow;
                        $ticket_process->prize = $processed_product_order_data['campaigns_title'];
                        $ticket_process->purchase_date = $processed_product_order_data["order_date"];
                        $ticket_process->draw_date = $processed_product_order_data['draw_date'];
                        $ticket_list[] = $ticket_process;
                    }

                    $processed_product_order_data["ticket_numbers"] = $ticket_list;
                    $processed_product_order_data["product_total"] = (string)($row->product_sub_price + $row->product_total_tax + $row->product_total_shipping_charge);
                    $total_tickets += count($ticket_numbers_data);
                    $order_sub_total += $row->product_sub_price;
                    if ($row->attribute_values_ids == '') {
                        $product_variation = array();
                    } else {
                        $product_variation = CartModel::getVariationBYProductId($row->attribute_values_ids, 1);
                    }
                    $processed_product_order_data["attributes"] = $product_variation;
                    $processed_product_order_data['is_spinner'] = $row->is_spinner;
                    $processed_product_order_data['spin_complete'] = "1";
                    $processed_product_order_data['spinner_his_id'] = "0";
                    $spin_data = OrderModel::get_spin_history($row->order_block_id, $row->history_id);
                    if (!empty($spin_data)) {
                        $processed_product_order_data['spin_complete'] = ($spin_data->spinner_status == 2) ? '1' : '0';
                        $processed_product_order_data['spinner_his_id'] = $spin_data->spinner_his_id;
                    }
                    $order_product_list[] = $processed_product_order_data;

                    if ($row->is_donate == 0) {
                        $is_donated = 0;
                    }
                }

                $order_grand_total = (string)$order_details->total_price;
                $order_tax_total = (string)$order_details->vat_price;
                $order_shipping_charge = (string)$order_details->shipping_charge;
                $used_points = (string)$order_details->used_points;
                $discounted_amount = (string)$order_details->redeemed_amount;
                $amount_paid = (string)$order_details->actual_amount_paid;
                $status = "1";
                $shipping_address_data = UserTable::getUserAddressByAddressId($order_details->shipping_address_id, 1);

                if ($shipping_address_data) {
                    $address_row["shiping_details_id"] = (string)$shipping_address_data->user_shiping_details_id;
                    $address_row["first_name"] = (string)$shipping_address_data->s_first_name;
                    $address_row["middle_name"] = (string)$shipping_address_data->s_middle_name;
                    $address_row["last_name"] = (string)$shipping_address_data->s_last_name;
                    $address_row["cutomer_name"] = (string)$shipping_address_data->s_first_name . " " . (string)$shipping_address_data->s_last_name;
                    $address_row["street_name"] = (string)$shipping_address_data->user_shiping_details_street;
                    $address_row["building_name"] = (string)$shipping_address_data->user_shiping_details_building;
                    $address_row["floor_no"] = (string)$shipping_address_data->user_shiping_details_floorno;
                    $address_row["flat_no"] = (string)$shipping_address_data->user_shiping_details_flatno;
                    $address_row["location"] = (string)$shipping_address_data->user_shiping_details_loc;
                    $address_row["land_mark"] = (string)$shipping_address_data->user_shiping_details_landmark;
                    if ($shipping_address_data->user_shiping_details_city == '-1') {
                        $address_row["city_name"] = (string)$shipping_address_data->user_shiping_details_other_city;
                    } else {
                        $address_row["city_name"] = (string)$shipping_address_data->city_name;
                    }
                    $address_row["country_name"] = (string)$shipping_address_data->country_name;
                    $address_row["phone_no"] = ((string)$shipping_address_data->user_shiping_details_dial_code) . " " . ((string)$shipping_address_data->user_shiping_details_phone);
                    $address_row["default_address"] = (string)$shipping_address_data->default_address_status;
                    $address_row["address_type"] = (string)$shipping_address_data->user_shiping_details_loc_type;
                    $address_row["city_id"] = (string)$shipping_address_data->user_shiping_details_city;
                    $address_row["country_id"] = (string)$shipping_address_data->user_shiping_country_id;
                    $address_row["latitude"] = (string)$shipping_address_data->user_shiping_details_latitude;
                    $address_row["longitude"] = (string)$shipping_address_data->user_shiping_details_longitude;

                    $user_address_list[] = $address_row;
                }

                $o_data["userAddress"] = $user_address_list;
                $order_status_array = OrderModel::getOrderStatusHistory($order_details->order_block_id);

                if ($order_status_array) {

                    $o_data["order_delivery_date"] = (string)$order_status_array->delivery_date;
                    $o_data["order_notes"] = (string)$order_status_array->status_note;
                }

                $o_data["order_is_donation"] = (string)$is_donated;
                $o_data["order_status"] = (string)$this->get_order_stat_change($order_product_details[0]->deliver_status);
            } else {
                $status = '3';
                $message = __('messages.errors.no_order_found');
            }

            $o_data["orderProductList"] = $order_product_list;
            $o_data["orderSubTotal"] = (string)$order_grand_total;// $order_sub_total;
            $o_data["orderTaxTotal"] = (string)$order_tax_total;
            $o_data["orderShippingCharge"] = (string)$order_shipping_charge;
            $o_data["orderGrandTotal"] = (string)$order_grand_total;
            $o_data["orderUsedPoints"] = (string)$used_points;
            $o_data["orderDiscountedAmount"] = (string)$discounted_amount;
            $o_data["orderAmountPaid"] = (string)$amount_paid;
            $o_data["orderno"] = (string)$order_no;
            $o_data["totalTickets"] = (string)$total_tickets;
            $o_data["currency"] = "JOD";
            $o_data['spinner_data'] = SpinnerModel::where([
                'is_deleted' => 0,
                'spinner_language_code' => 1
            ])->get();

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            dd($exception);
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getOrdersList(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $o_data = [];
            $order_list = [];
            $order_status["cancelled"] = "0";
            $order_status["pending"] = "0";
            $order_status["dispatched"] = "0";
            $order_status["delivered"] = "0";
            $limit = (int)$request->limit;
            $page_number = $request->has('page') ? ($request->page ?? 1) : 1;
            $offset = ($page_number - 1) * $limit;
            $language = $request->has('language') ? $request->language : 1;
            $limit_query = "";
            if ($limit > 0)
                $limit_query = " limit {$limit} offset {$offset}";

            $order_list_data = OrderModel::getOrdersWithProducts($user->user_id, $limit_query);
            foreach ($order_list_data as $row) {
                if ($row->campaigns_id != '') {
                    $check_donated = OrderModel::getDonationDetails($row->order_block_id);
                    $is_donated = 1;
                    foreach ($check_donated as $key1 => $value1) {
                        if ($value1->is_donate == 0) {
                            $is_donated = 0;
                        }
                    }

                    $processed_product_order_data = process_product_data_v2($row, $language);
                    $processed_product_order_data["product_order_total_price"] = (string)$row->product_sub_price;
                    $processed_product_order_data["product_order_unit_price"] = (string)$row->unit_price;
                    $processed_product_order_data["product_quantity"] = (string)$row->purchase_qty;
                    $processed_product_order_data["order_is_donation"] = (string)$is_donated;
                    $processed_product_order_data["order_id"] = (string)$row->order_block_id;
                    $processed_product_order_data["order_sub_total"] = (string)$row->sub_total;
                    $processed_product_order_data["order_grand_total"] = (string)$row->total_price;
                    $processed_product_order_data["order_tax_total"] = (string)$row->vat_price;
                    $processed_product_order_data["order_shipping_charge"] = (string)$row->shipping_charge;
                    $processed_product_order_data["used_points"] = (string)$row->used_points;
                    $processed_product_order_data["order_date"] = (string)get_date_in_timezone(USERTIMEZONE, gmdate("d-m-Y H:i:s", strtotime($row->order_placed_date)), "d M Y h:i A");
                    $processed_product_order_data["order_no"] = "BG" . date("Ymd", strtotime($row->order_placed_date)) . $row->order_block_id;
                    $ticket_numbers_data = TicketModel::getOrderTicketNumbers(["order_block_id" => $row->order_block_id, "product_id" => $row->product_id]);
                    $processed_product_order_data["ticket_number"] = (string)(count($ticket_numbers_data) > 0) ? $ticket_numbers_data[0]->ticket_number : "";

                    $order_status_data = OrderModel::getOrderStatusHistory($row->order_block_id);

                    if ($order_status_data) {
                        if ($order_status_data['order_status'] == 0) {
                            $order_status = 'Pending';
                        }

                        if ($order_status_data['order_status'] == 1) {
                            $order_status = 'Confirmed';
                        }

                        if ($order_status_data['order_status'] == 2) {
                            $order_status = 'Completed';
                        }

                        if ($order_status_data['order_status'] == 3) {
                            $order_status = 'Canceled';
                        }
                    }
                    else
                        $order_status = "Pending";

                    $processed_product_order_data['order_status'] = (string)$order_status;
                    if ($row->attribute_values_ids == '') {
                        $product_variation = array();
                    } else {
                        $product_variation = OrderModel::getVariationByProductId($row->attribute_values_ids, $language);
                    }

                    $processed_product_order_data["attributes"] = $product_variation;
                    $order_list[] = convertNumbersToStrings($processed_product_order_data);
                }
            }

            $o_data["orders"] = $order_list;
            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            dd($exception);
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

    public function getOrderTicketNumbers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required'
            ], [
                'order_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.order_id')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $arr = [];
            $language = $request->has('language') ? $request->language : 1;
            $order = OrderModel::where("order_block_id", $request->order_id)
                ->orderBy("product_order_id", "DESC")->first();

            if (empty($order)) {
                return return_response('0', 500, __('messages.errors.no_order'));
            }

            $order_ticket_numbers = TicketModel::where('order_block_id', $request->order_id)->get();
            foreach ($order_ticket_numbers as $key => $value) {
                $order_product_list_data = TicketModel::getTicketsByOrderProductId($value->ticket_number, $value->product_id, $language);
                if (!empty($order_product_list_data)) {
                    if ($order_product_list_data->campaigns_image2 == '') {
                        $order_product_list_data->campaigns_image2 = 'dummy.png';
                    }
                    $arr[] = array(
                        'order_date' => (string)get_date_in_timezone(USERTIMEZONE, gmdate("d-m-Y H:i:s", strtotime($order_product_list_data->order_placed_date)), "d M Y h:i A"),
                        'product_name' => $order_product_list_data->product_name,
                        'campaigns_home_image' => asset("uploads/products/" . $order_product_list_data->campaigns_image2),
                        'ticket_numbers' => $value->ticket_number,
                        'campaigns_title' => $order_product_list_data->campaigns_title
                    );
                }
            }

            $o_data["orderTicketNumbers"] = $arr;
            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getCampaignDrawDetails(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }
            $language = $request->has('language') ? $request->language : 1;

            $campaign_data = TicketModel::getCampaign($request->campaign_id, $user->user_id, $language);

            if (!empty($campaign_data)) {
                $processed_product_order_data = process_product_data($campaign_data, $language);
                $processed_product_order_data["draw_date"] = "";
                if ($campaign_data->campaigns_draw_date != '') {
                    $processed_product_order_data["draw_date"] = (string)get_date_in_timezone(USERTIMEZONE, gmdate("d-m-Y H:i:s", strtotime($campaign_data->campaigns_draw_date)), "d M Y h:i A");
                }
                $processed_product_order_data["won_user_name"] = "";
                $processed_product_order_data["won_user_image"] = "";
                $processed_product_order_data["won_ticket_number"] = "";
                $processed_product_order_data["is_user_won_campaign"] = "0";

                if (File::exists(public_path('uploads/products/' . $campaign_data->campaigns_image2)) && File::isFile(public_path('uploads/products/' . $campaign_data->campaigns_image2))) {
                    $processed_product_order_data['campaigns_home_image'] = url('uploads/products/' . $campaign_data->campaigns_image2);
                } else {
                    $processed_product_order_data['campaigns_home_image'] = url('images/dummy.jpg');
                }

                if ($campaign_data->campaigns_status == 2) {
                    $campaign_winner_data = TicketModel::getWonTicketNumber($campaign_data->campaigns_id);
                    if ($campaign_winner_data) {
                        $user_image = base_url() . "campaign_winner_data/user/" . $campaign_winner_data->image;
                        if (File::exists(public_path('uploads/user/' . $campaign_winner_data->image)) && File::isFile(public_path('uploads/user/' . $campaign_winner_data->image))) {
                            $processed_product_order_data['won_user_image'] = url('uploads/user/' . $campaign_winner_data->image);
                        } else {
                            $processed_product_order_data['won_user_image'] = url('images/user_dummy.png');
                        }

                        $processed_product_order_data["won_user_name"] = (string)$campaign_winner_data->user_first_name . " " . (string)$campaign_winner_data->user_middle_name . " " . (string)$campaign_winner_data->user_last_name;
                        $processed_product_order_data["won_ticket_number"] = (string)$campaign_winner_data->draw_slip_number;

                        if ($campaign_winner_data->user_id == $user->user_id) {
                            $processed_product_order_data["is_user_won_campaign"] = "1";
                        }
                    }
                }

                $message = __('messages.success.list_success');
                $status = '1';
                $o_data = $processed_product_order_data;
            } else {
                $message = __('messages.errors.no_campaigns');
                $status = '0';
                $o_data = [];
            }

            return return_response($status, 200, $message, [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getOrderTrackingStatus(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $validator = Validator::make($request->all(), [
                'order_id' => 'required'
            ], [
                'order_id.required' => __('messages.validation.required', ['field' => __('messages.common_messages.order_id')])
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $order_status["cancelled"] = "0";
            $order_status["pending"] = "0";
            $order_status["dispatched"] = "0";
            $order_status["delivered"] = "0";

            $order_details = OrderModel::where('order_block_id', $request->order_id)->first();
            if ($order_details) {
                $order_status_record = OrderModel::getOrderStatus($request->order_id);
                if ($order_status_record) {
                    if ($order_status_record->order_status === 1) {
                        $order_status['dispatched'] = '1';
                    } else if ($order_status_record->order_status === 2) {
                        $order_status['delivered'] = '1';
                    } else {
                        $order_status['cancelled'] = '1';
                    }
                } else {
                    $order_status['pending'] = '1';
                }
                $status = '1';
                $message = __('messages.success.list_success');
            } else {
                $status = '0';
                $message = __('messages.errors.no_order_found');
            }

            return return_response($status, 200, $message, [], $order_status);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getMyTickets(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return return_response('0', 401, __('messages.errors.invalid_token'));
            }

            $tickets_list = $tickets_list_won = [];
            $product_order_list_data = TicketModel::getUserTicketsAll($user->user_id);
            foreach ($product_order_list_data as $row) {
                if (File::exists(public_path('uploads/products/' . $row->campaigns_image)) && File::isFile(public_path('uploads/products/' . $row->campaigns_image))) {
                    $product_image = asset('uploads/products/' . $row->campaigns_image);
                } else {
                    $product_image = asset('images/dummy.jpg');
                }

                if (File::exists(public_path('uploads/products/' . $row->campaigns_image2)) && File::isFile(public_path('uploads/products/' . $row->campaigns_image2))) {
                    $campaigns_home_image = asset('uploads/products/' . $row->campaigns_image2);
                } else {
                    $campaigns_home_image = asset('images/dummy.jpg');
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
                if ($row->campaigns_draw_date != '') {
                    $processed_product_order_data["draw_date"] = (string)get_date_in_timezone(USERTIMEZONE, gmdate("d-m-Y H:i:s", strtotime($row->campaigns_draw_date)), "d M Y h:i A");
                } else {
                    $processed_product_order_data["draw_date"] = "";
                }
                $processed_product_order_data["won_ticket_number"] = "";
                $processed_product_order_data["is_user_won_campaign"] = "0";
                $processed_product_order_data["campaigns_end_date"] = gmdate("d-m-Y H:i:s", strtotime($row->campaigns_date . " " . $row->campaigns_time));
                $ticket_info = TicketModel::getTicketCount($row->ticket_number);
                $processed_product_order_data["ticket_number"] = (string)$row->ticket_number;
                $processed_product_order_data["ticket_count"] = (string)$ticket_info;
                $tickets_list[] = $processed_product_order_data;
            }

            $won_tickets = TicketModel::getUserTicketsWon($user->user_id);
            foreach ($won_tickets as $row) {
                if (File::exists(public_path('uploads/products/' . $row->campaigns_image)) && File::isFile(public_path('uploads/products/' . $row->campaigns_image))) {
                    $product_image = asset('uploads/products/' . $row->campaigns_image);
                } else {
                    $product_image = asset('images/dummy.jpg');
                }

                if (File::exists(public_path('uploads/products/' . $row->product_image)) && File::isFile(public_path('uploads/products/' . $row->product_image))) {
                    $product_image2 = asset('uploads/products/' . $row->product_image);
                } else {
                    $product_image2 = asset('images/dummy.jpg');
                }

                if (File::exists(public_path('uploads/user/' . $row->image)) && File::isFile(public_path('uploads/user/' . $row->image))) {
                    $user_image = asset('uploads/user/' . $row->image);
                } else {
                    $user_image = asset('images/user_dummy.png');
                }

                if (File::exists(public_path('uploads/products/' . $row->campaigns_image2)) && File::isFile(public_path('uploads/products/' . $row->campaigns_image2))) {
                    $campaigns_home_image = asset('uploads/products/' . $row->campaigns_image2);
                } else {
                    $campaigns_home_image = asset('images/dummy.jpg');
                }

                $processed_won_ticket["m_product_image"] = (string)$product_image;
                $processed_won_ticket["m_product_image2"] = (string)$product_image2;
                $processed_won_ticket["campaigns_home_image"] = (string)$campaigns_home_image;
                $processed_won_ticket["product_name"] = (string)$row->product_name;
                $processed_won_ticket["campaigns_title"] = (string)$row->campaigns_title;
                $processed_won_ticket["campaigns_id"] = (string)$row->campaigns_id;
                $processed_won_ticket["product_id"] = (string)$row->product_id;
                $processed_won_ticket["product_attribute_id"] = (string)$row->product_attribute_id;
                $processed_won_ticket["purchased_on"] = (string)get_date_in_timezone(USERTIMEZONE, gmdate("d-m-Y H:i:s", strtotime($row->order_placed_date)), "d M Y h:i A");

                if ($row->campaigns_draw_date != '') {
                    $processed_won_ticket["draw_date"] = (string)get_date_in_timezone(USERTIMEZONE, gmdate("d-m-Y H:i:s", strtotime($row->campaigns_draw_date)), "d M Y h:i A");
                } else {
                    $processed_won_ticket["draw_date"] = "";
                }
                $processed_won_ticket["won_ticket_number"] = "";
                $processed_won_ticket["is_user_won_campaign"] = "0";
                $processed_won_ticket["user_first_name"] = (string)$row->user_first_name;
                $processed_won_ticket["user_middle_name"] = (string)$row->user_middle_name;
                $processed_won_ticket["user_last_name"] = (string)$row->user_last_name;
                $processed_won_ticket["campaigns_end_date"] = gmdate("d-m-Y H:i:s", strtotime($row->campaigns_date . " " . $row->campaigns_time));
                $processed_won_ticket["user_image"] = (string)$user_image;
                $ticket_info = TicketModel::getTicketCount($row->draw_slip_number);
                $processed_won_ticket["ticket_number"] = (string)$row->draw_slip_number;
                $processed_won_ticket["ticket_count"] = (string)$ticket_info;

                $tickets_list_won[] = $processed_won_ticket;
            }

            $o_data["ticketsList"] = $tickets_list;
            $o_data["ticketsWon"] = $tickets_list_won;
            return return_response('1', 200, '', [], convertNumbersToStrings($o_data));
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }
}
