<?php

namespace App\Http\Controllers\admin;

use App\Exports\CampaignReportExport;
use App\Http\Controllers\Controller;
use App\Models\CampaignImages;
use App\Models\CampaignModel;
use App\Models\CountriesModel;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\TicketModel;
use App\Traits\FirebaseNotificationTrait;
use AWS\CRT\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth, DB;
use Kreait\Firebase\Contract\Database;
use Maatwebsite\Excel\Facades\Excel;

class CampaignController extends Controller
{

    use FirebaseNotificationTrait;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index(Request $request)
    {
        try {
            $data['page_heading'] = 'Campaigns';
            $data['products'] = Product::getProducts($request);
            $data['countries'] = CountriesModel::where(['country_status' => 1])->get();

            return view('admin.campaigns.index')->with($data);
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function exportExcel()
    {
        $data = [
            'search_key' => '',
            'country' => 108
        ];
        $reports = CampaignModel::getCampaignReport($data);
        $export_list = [];
        foreach ($reports as $report) {
            $export_list[] = [
                $report->campaigns_title,
                $report->campaigns_date,
                Carbon::parse($report->campaigns_time)->format('h:i A'),
                $report->product_name,
                'Ticket# ' . $report->draw_slip_number,
                $report->campaigns_status == 1 ? 'Pending' : 'Closed'
            ];
        }

        return Excel::download(new CampaignReportExport($export_list), 'campaign-reports.xlsx');
    }

    public function create(Request $request, $product_id = null)
    {
        try {
            $data['page_heading'] = 'Create Campaign';
            $data['product_id'] = $product_id;
            $data['countries'] = CountriesModel::where(['country_status' => 1])->get();
            if ($product_id) {
                $data['product'] = Product::findProduct($product_id);
            }

            return view('admin.campaigns.create')->with($data);
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            $campaign = CampaignModel::find($request->id);
            if ($campaign) {
                $campaign->campaigns_status = $request->status;
                $campaign->save();

                return return_response('1', 200, 'Status changed successfully');
            } else {
                return return_response('0', 404, 'Campaign not found');
            }
        } catch (\Exception $exception) {
            return return_response('0', 500, 'Something went wrong');
        }
    }

    public function saveCampaign(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'country_id' => 'required',
                'campaigns_title' => 'required',
                'campaigns_draw_date' => 'required',
                'campaigns_desc' => 'required',
                'product_unique_iden' => 'required',
                'product_desc_short' => 'required',
                'product_desc_full' => 'required',
                'product_name' => 'required',
                'stock_quantity' => 'required',
                'price' => 'required',
            ], [
                'country_id.required' => 'Country id is required',
                'campaigns_title.required' => 'Campaign title is required',
                'campaigns_draw_date.required' => 'Draw date is required',
                'campaigns_desc.required' => 'Campaign description is required',
                'product_unique_iden.required' => 'Product unique identifier is required',
                'product_desc_short.required' => 'Product description is required',
                'product_desc_full.required' => 'Product description is required',
                'product_name.required' => 'Product unique identifier is required',
                'stock_quantity.required' => 'Quantity is required',
                'price.required' => 'Price is required',
            ]);

            if ($validator->fails()) {
                return return_response('2', 200, '', $validator->errors());
            }

            $input = $request->except('_token');

            $exists_product = Product::where('product_unique_iden', $input['product_unique_iden'])->first();
            if ($exists_product && !$request->has('product_id')) {
                return return_response('0', 200, 'Product identity must be unique');
            }

            $product_arr = [
                'product_type' => 1,
                'product_desc_short' => $input['product_desc_short'],
                'product_desc_short_arabic' => null,
                'product_desc_full_arabic' => null,
                'product_desc_full' => $input['product_desc_full'],
                'product_sale_from' => null,
                'product_sale_to' => null,
                'product_featured_image' => null,
                'product_tag' => null,
                'product_created_by' => Auth::user()->user_id,
                'product_updated_by' => null,
                'product_created_date' => Carbon::now(),
                'product_updated_date' => Carbon::now(),
                'product_status' => 1,
                'product_deleted' => 0,
                'product_name' => $input['product_name'],
                'product_variation_type' => 1,
                'product_taxable' => 1,
                'product_vender_id' => 0,
                'product_unique_iden' => $input['product_unique_iden'],
                'cash_points' => 0,
                'offer_enabled' => 0,
                'deal_enabled' => 0,
                'is_today_offer' => 0,
                'today_offer_date' => Carbon::now(),
                'thanku_perc' => 0,
                'custom_status' => 0,
                'featured_product' => 0,
            ];

            $product_attr_arr = [
                'stock_quantity' => $input['stock_quantity'],
                'sale_price' => $input['price']
            ];

            if ($request->has('schedule_now') && $request->schedule_now) {
                $campaign_start_date = Carbon::now()->format('Y-m-d');
                $campaign_start_time = Carbon::now()->format('h:i A');
            } else {
                $datetime = Carbon::createFromFormat('Y-m-d h:i A', $input['campaigns_schedule_date']);
                $campaign_start_date = $datetime->toDateString();
                $campaign_start_time = $datetime->format('h:i A');
            }

            $campaigns_arr = [
                'campaigns_title' => $input['campaigns_title'],
                'campaigns_date' => Carbon::now()->format('Y-m-d'),
                'campaigns_time' => $campaign_start_time,
                'campaigns_qty' => 1,
                'campaigns_desc' => $input['campaigns_desc'],
                'country_id' => $input['country_id'],
                'campaigns_status' => 1,
                'campaigns_date_start' => $campaign_start_date,
                'campaigns_time_start' => $campaign_start_time,
                'campaigns_draw_date' => Carbon::parse($input['campaigns_draw_date'])->format('Y-m-d'),
                'schedule_now' => !empty($input['schedule_now']) ? 1 : 0,
                'is_featured' => !empty($input['is_featured']) ? 1 : 0,
            ];

            if ($request->hasFile('product_image')) {
                $file = $request->file('product_image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $s3Path = 'products/' . $fileName;
                $filePath = \Storage::disk('s3')->put($s3Path, file_get_contents($file));
                $product_arr['product_image'] = \Storage::disk('s3')->url($s3Path);
            }

            if ($request->hasFile('prize_image')) {
                $file = $request->file('prize_image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $s3Path = 'campaigns/' . $fileName;
                $filePath = \Storage::disk('s3')->put($s3Path, file_get_contents($file));
                $campaigns_arr['campaigns_image2'] = \Storage::disk('s3')->url($s3Path);
            }

            if ($request->has('product_id')) {
                Product::where('product_id', $request->product_id)->update($product_arr);
                $product = Product::find($request->product_id);
                ProductAttribute::where('product_id', $request->product_id)->delete();
                CampaignModel::where('product_id', $request->product_id)->update($campaigns_arr);
                $campaigns = CampaignModel::where('product_id', $product->product_id)->first();
            } else {
                $product = Product::create($product_arr);
                $campaigns_arr['product_id'] = $product->product_id;
                $campaigns = CampaignModel::create($campaigns_arr);
            }

            $product_attr_arr['product_id'] = $product->product_id;
            $product_attr = ProductAttribute::create($product_attr_arr);

            $images = $request->campaigns_image ?? [];
            $url = '';
            $firstImageUrl = null;

            foreach ($images as $index => $image) {
                $file = $image;
                $fileName = time() . '_' . $file->getClientOriginalName();
                $s3Path = 'campaigns/' . $fileName;
                $filePath = \Storage::disk('s3')->put($s3Path, file_get_contents($file));
                $url = \Storage::disk('s3')->url($s3Path);

                if ($index === 0) {
                    $firstImageUrl = $url;
                }

                CampaignImages::create([
                    'campaign_id' => $campaigns->campaigns_id,
                    'image_url' => $url
                ]);
            }

            if ($firstImageUrl) {
                $campaigns->campaigns_image = $firstImageUrl;
                $campaigns->save();
            }

            return return_response('1', 200, 'Campaign saved successfully');
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error($exception);
            return return_response('0', 500, 'Something went wrong');
        }
    }

    public function delete($id)
    {
        try {
            ProductAttribute::where('product_id', $id)->delete();
            $campaign = CampaignModel::where('product_id', $id)->first();
            CampaignImages::where('campaign_id', $campaign->campaign_id)->delete();
            $campaign->delete();
            Product::where('product_id', $id)->delete();

            return return_response('1', 200, 'Product and campaign deleted');
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error($exception);
            return return_response('0', 500, 'Something went wrong');
        }
    }

    public function generateWinner(Request $request)
    {
        try {
            $campaign = CampaignModel::where('campaigns_id', $request->campaign_id)->with('product', 'product_attribute')->first();
            $product_attribute = DB::table('product_attribute')->where('product_id', $campaign->product_id)->first();
            $success_message = '';
            if ($campaign) {
                if ($campaign->campaigns_status == 1) {
                    $draw_slip_number = \DB::table('draw_slip')
                        ->where('campaign_id', $request->campaign_id)
                        ->where('draw_slip_number', '!=', '')
                        ->when($request->ticket_number, function ($q) use ($request) {
                            return $q->where('draw_slip_number', $request->ticket_number);
                        })
                        ->inRandomOrder()
                        ->limit(1)
                        ->pluck('draw_slip_number')
                        ->first();

                    if (!empty($draw_slip_number)) {
                        $campaignDate = Carbon::parse($_POST['campaign_date'])->format('d-m-Y h:i A');
                        $update = [
                            'campaigns_status' => 2,
                            'draw_date_manual' => $campaignDate,
                            'campaigns_draw_date' => now(),
                        ];

                        DB::table('campaigns')
                            ->where('campaigns_id', $request->campaign_id)
                            ->update($update);

                        DB::table('draw_slip')
                            ->where('campaign_id', $request->campaign_id)
                            ->update(['draw_slip_status' => 0]);

                        DB::table('draw_slip')
                            ->where('campaign_id', $request->campaign_id)
                            ->where('draw_slip_number', $draw_slip_number)
                            ->update(['draw_slip_status' => 1]);

                        $rec = DB::table('draw_slip as D')
                            ->select([
                                'U.user_device_token',
                                'D.user_id',
                                'D.campaign_id',
                                'D.order_block_id',
                                'U.firebase_user_key',
                                'U.user_first_name',
                                'U.user_last_name',
                                'U.user_device_token',
                                'U.user_access_token',
                                'C.campaigns_title',
                            ])
                            ->distinct()
                            ->leftJoin('user_table as U', 'U.user_id', '=', 'D.user_id')
                            ->leftJoin('campaigns as C', 'C.campaigns_id', '=', 'D.campaign_id')
                            ->where('D.draw_slip_number', $draw_slip_number)
                            ->first();

                        $firebase_user_key = $rec->firebase_user_key;
                        $campaign_id = $rec->campaign_id;
                        $user_first_name = $rec->user_first_name;
                        $user_last_name = $rec->user_last_name;
                        $dev_token = $rec->user_device_token;
                        $campaigns_title = $rec->campaigns_title;
                        $user_access_token = $rec->user_access_token;
                        $order_block_id = $rec->order_block_id;

                        $won_user = $user_first_name . ' ' . $user_last_name;
                        $won_user_id = $rec->user_id;

                        $title = 'You won the campaign';
                        $description = 'Congratulations ' . $user_first_name . ' ' . $user_last_name . ', We are happy to announce “You’ve won the “' . $campaigns_title . '” campaign.” To claim your prize email us at campaign@ma7zouz.com!!! We love to see you participate in our future campaigns.';

                        $status = '1';
                        $success_message = 'The winner is ' . $user_first_name . ' ' . $user_last_name . ' against campaign ' . $campaigns_title . '.';

                        $user = $rec;
                        $notification_id = time();
                        $ntype = 'win_campaign';
                        $order_id = $order_block_id;
                        if (!empty($user->firebase_user_key)) {
                            $notification_data["Notifications/" . $user->firebase_user_key . "/" . $notification_id] = [
                                "title" => $title,
                                "description" => $description,
                                "notificationType" => $ntype,
                                "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                                "orderId" => (string)$order_id,
                                "productId" => (string)$campaign->product_id,
                                "productAttrId" => (string)$product_attribute->product_attribute_id,
                                "status" => $status,
                                "url" => "",
                                "imageURL" => '',
                                "read" => "0",
                                "seen" => "0",
                            ];
                            $this->database->getReference()->update($notification_data);
                        }

                        $user_device_token = $user->user_device_token;
                        if (!empty($user_device_token)) {

                            $res = send_single_notification(
                                $user_device_token,
                                [
                                    "title" => $title,
                                    "body" => $description,
                                    "icon" => 'myicon',
                                    "sound" => 'default',
                                    "click_action" => "EcomNotification",
                                    "productId" => (string)$campaign->product_id,
                                    "productAttrId" => (string)$product_attribute->product_attribute_id,
                                ],
                                [
                                    "type" => $ntype,
                                    "notificationID" => $notification_id,
                                    "orderId" => (string)$order_id,
                                    "status" => $status,
                                    "imageURL" => "",
                                    "productId" => (string)$campaign->product_id,
                                    "productAttrId" => (string)$product_attribute->product_attribute_id,
                                ]
                            );

                        }

                    } else {
                        $status = '0';
                        $success_message = 'Ticket not found';
                    }
                } else {
                    $status = '0';
                    $success_message = 'Campaign is already finished';
                }
            } else {
                $status = '0';
                $success_message = 'Campaign not found';
            }

            return return_response($status, 200, $success_message);
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error($exception);
            return return_response('0', 500, 'Something went wrong');
        }
    }

    public function winnerList()
    {
        $page_heading = 'Winners';
        $winner_list = TicketModel::getWinnerList(1, "", "", "", 108);
        $user_list_won = [];
        foreach ($winner_list as $row) {
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
            $ticket_info = TicketModel::getTicketCount($row->draw_slip_number);
            $processed_won_ticket["ticket_number"] = (string)$row->draw_slip_number;
            $processed_won_ticket["ticket_count"] = (string)$ticket_info;
            $processed_won_ticket["user_id"] = (string)$row->user_id;

            $user_list_won[] = $processed_won_ticket;
        }

        return view('admin.winners', compact('page_heading', 'user_list_won'));
    }
}
