<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Validator, DB, Auth, Log;

class AuctionController extends Controller
{
    public function __construct(Database $database)
    {

        $this->database = $database;
    }
    public function search(Request $request)
    {
        $status  = "1";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $User    =  getUserData($request);
        $filter = ['product_status' => 1];
        $params = [];
        $auctionsCount = 0;
        if (isset($request->category_id)) {
            $params['category_ids'] = $request->category_id;
        }
        if (isset($request->city_id)) {
            $params['city_id'] = $request->city_id;
        }
        if (isset($request->search_key)) {
            $params['search_key'] = $request->search_key;
        }
        if (isset($request->min_price)) {
            $params['min_price'] = $request->min_price;
        }
        if (isset($request->max_price)) {
            $params['max_price'] = $request->max_price;
        }
        if (isset($request->live)) {
            $params['live'] = 1;
        }
        if (isset($request->age)) {
            $params['age'] = $request->age;
        }
        $sort = 'newest';
        if (isset($request->sort)) {
            $sort = $request->sort;
        }
        if ($User) {
            $params['user_id'] = $User->id;
        } else {
            $params['user_id'] = 0;
        }
        $params['active_only'] = true;
        $page = ($request->page) ? $request->page : 1;
        $limit = ($request->limit) ? $request->limit : config('global.per_page_limit');
        $offset = ($page - 1) * $limit;
        $auctionsCount  = \App\Models\ProductModel::search_auctions($filter, $sort, [], $params)->count();
        $auctions = \App\Models\ProductModel::search_auctions($filter, $sort, [], $params)->limit($limit)->skip($offset)->get();
        $actAr = [];
        foreach ($auctions as $key => $value) {
            //if(strtotime($value->end_date.$value->end_time."+10 mins") > strtotime(date('Y-m-d H:i')) ) {
            $actAr[] = processAuctions($value);
            //}
        }
        $o_data['list'] = $actAr;
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object)$o_data, 'total_count' => $auctionsCount]);
    }

    public function details(Request $request)
    {
        $status  = "0";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $similar = [];
        $similaract  = [];
        $User    =  getUserData($request);

        $validator = Validator::make($request->all(), [
            'auction_id'      => 'required',

        ]);
        if ($validator->fails()) {
            $status = "0";
            $msg = setValidationMesages($validator);
            $message = $msg['message'];
            $errors = $msg['errors'];
            $o_data = (object)$o_data;
        } else {
            $filter = array('product.id' => $request->auction_id, 'product_status' => 1);
            $params = [];
            if ($User) {
                $params['user_id'] = $User->id;
            } else {
                $params['user_id'] = 0;
            }
            $params['showall'] = 1;
            $details = \App\Models\ProductModel::search_auctions($filter, '', [], $params)->get()->first();
            if ($details) {
                $notify_user_ids = json_decode($details->notify_user_ids);
                if ($notify_user_ids != null) {
                    $notify_user_ids = $notify_user_ids->users;
                } else {
                    $notify_user_ids = [];
                }
                $visits = \App\Models\ProductVisits::where('product_id', $request->auction_id)
                    ->count();
                \App\Models\ProductVisits::trackVisits($request->auction_id, $User->id);
                $status  = "1";
                $all = $details;
                $details = processAuctions($details);
                $details['videos'] = productImage($all->video_path);
                if (!empty($details['videos'])) {
                    $details['videos'] = $details['videos']['gallery'];
                }
                $video = $details['videos'];


                $video_links =  json_decode($all->video_thumb);
                if (!empty($video_links)) {
                    foreach ($video_links as $key => $value) {
                        $value->th = url(config('global.upload_path') . config('global.product_image_upload_dir') . $value->th);
                        $value->fl = url(config('global.upload_path') . config('global.product_image_upload_dir') . $value->fl);
                    }
                } else {
                    $video_links = [];
                }
                $details['video_links'] = $video_links;
                $details['product_visit'] = $visits;
                if (in_array($User->id, $notify_user_ids)) {
                    $details['already_notified'] = 1;
                } else {
                    $details['already_notified'] = 0;
                }
                $details['share_url'] = url('auction/details/' . $request->auction_id);
                $o_data =  $details;
                $filter = ['product_status' => 1];
                $params['category_ids'] = array($details['category_id']);

                $params['not_this_id'] = $request->auction_id;
                $similar = \App\Models\ProductModel::search_auctions($filter, '', [], $params)->limit(config('global.similar_product_count'))->get();
                $similaract = [];
                foreach ($similar as $key => $value) {
                    if (strtotime($value->end_date . $value->end_time . "+10 mins") > strtotime(date('Y-m-d H:i'))) {
                        $similaract[] = processAuctions($value);
                    }
                }
            } else {
                $message  = "Invalid auction";
                $o_data = (object)$o_data;
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data, 'similar' => $similaract]);
    }

    private function validateAccesToken($access_token)
    {

        $user = User::where(['user_access_token' => $access_token])->get();

        if ($user->count() == 0) {
            http_response_code(401);
            echo json_encode([
                'status' => 0,
                'message' => login_message(),
                'oData' => [],
                'errors' => (object) [],
            ]);
            exit;
        } else {
            $user = $user->first();
            if ($user->active == 1) {
                return $user;
            } else {
                http_response_code(401);
                echo json_encode([
                    'status' => 0,
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ]);
                exit;
                return response()->json([
                    'status' => 0,
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ], 401);
                exit;
            }
        }
    }

    public function bidAuction(Request $request)
    {
        $status  = "0";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $similar = [];

        $validator = Validator::make($request->all(), [
            'auction_id'      => 'required',
            'amount'          => 'required'

        ]);
        if ($validator->fails()) {
            $status = "0";
            $msg = setValidationMesages($validator);
            $message = $msg['message'];
            $errors = $msg['errors'];
            $o_data = (object)$o_data;
        } else {

            $user = $this->validateAccesToken($request->access_token);
            $checkAuction = \App\Models\ProductModel::where(['id' => $request->auction_id, 'deleted' => 0, 'product_status' => 1])->where('end_date', '<=', date('Y-m-d'))->withMax('bids', 'bid_price')->get()->first();
            //$starttime = $checkAuction->starting_date." ".$checkAuction->start_time.":00";
            //$currentTime = date('Y-m-d H:i:s');

            if ($checkAuction == null) {
                $message = "Action is not active";
            } else if ($checkAuction != null && $checkAuction->product_vender_id == $user->id) {
                $message = "Can't Bid Own Auction";
            }/* elseif(strtotime($starttime) > strtotime($currentTime)){
            $message = "Auction is not yet started";
          }*/ else if ($checkAuction != null) {
                $valid = 0;
                if (((float)$checkAuction->bids_max_bid_price == 0 && $checkAuction->starting_price <= $request->amount)  || ((float)$checkAuction->bids_max_bid_price < $request->amount  && $request->amount >= $checkAuction->starting_price)) {

                    // if((float)$checkAuction->bids_max_bid_price!=0 ) {

                    // 	$lastBid = \App\Models\AuctionBids::where('product_id',$request->auction_id)->orderBy('bid_price','desc')->limit(1)->get()->first();
                    // 	 if(strtotime($lastBid->bid_date) > (time()-60*10)) {
                    //     $valid = 1;
                    //  } else {
                    //  	$lastBid->status = 1;
                    //  	$lastBid->save();
                    //  	$checkAuction->completed = 1;
                    //  	$checkAuction->save();
                    //  	$message = "Auction owned by some other user";
                    //  }
                    // } else {
                    // 	$valid = 1;
                    // }
                    $valid = 1;
                    if ($valid == 1) {
                        $auctionbidObj               = new \App\Models\AuctionBids();
                        $auctionbidObj->product_id   = $request->auction_id;
                        $auctionbidObj->user_id      = $user->id;
                        $auctionbidObj->bid_price    = $request->amount;
                        $auctionbidObj->status       = 1;
                        $auctionbidObj->created_at   = gmdate("Y-m-d H:i:s");
                        $auctionbidObj->bid_date     = gmdate("Y-m-d H:i:s");
                        $notification_id             = time();
                        $auctionbidObj->notification_id = $notification_id;
                        if ($auctionbidObj->save()) {
                            $message = "Joined  bid with amount JOD " . $request->amount;
                            exec("php " . base_path() . "/artisan bid:update " . $auctionbidObj->id . " " . $notification_id . " > /dev/null 2>&1 & ");
                            $status = "1";
                        }
                        Mail::to(['address' => 'info@fasttime.com'])
                            ->cc(['address' => Auth::user()->email])
                            ->send(new \App\Mail\AuctionCreation($order_id));
                    }
                } else {
                    $message = "Amount is not enough with Minimum bid";
                }
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data]);
    }

    public function addAuction(Request $request)
    {
        $status  = "0";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $validator = Validator::make($request->all(), [
            'category'      => 'required',
            'title'          => 'required',
            'description'          => 'required',
            'starting_price'          => 'required',
            'starting_date'          => '',
            'end_date'          => 'required',
            'start_time'          => '',
            'location'          => 'required',
            'latitude'          => 'required',
            'longitude'          => 'required',
            'image'          => '',
            'featured'   => 'required',
            'end_time'  => ''

        ]);
        if ($validator->fails()) {
            $status = "0";
            $msg = setValidationMesages($validator);
            $message = $msg['message'];
            $errors = $msg['errors'];
            $o_data = (object)$o_data;
        } else {
            date_default_timezone_set(config('global.date_timezone'));
            $User    =  getUserData($request);
            $categories = $request->category;
            $id  = $request->id;
            if ($request->end_date == date('Y-m-d') && date('H:i:s') > '12:00:00') {
                $message = "Permission Denied";
            } else {
                if ($id) {
                    $productDetails = \App\Models\ProductModel::getProductInfo($id)->first();
                    if ($productDetails->product_vender_id != $User->id) {
                        $message = "Permission Denied";
                        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object)$o_data]);
                        exit;
                    }
                    $product_type = $productDetails->product_type;
                }
                if (!empty($categories)) {
                    $all_categories = \App\Models\ProductModel::getCategoriesCondition($categories);
                    $all_categories = array_column($all_categories, 'category_parent_id', 'category_id');
                    foreach ($categories as $t_cat) {
                        $p_cat_id = $all_categories[$t_cat] ?? 0;
                        do {
                            if ($p_cat_id > 0) {
                                $categories[] = $p_cat_id;
                                $p_cat_id = $all_categories[$p_cat_id] ?? 0;
                            }
                        } while ($p_cat_id > 0);
                    }
                    $categories = array_filter($categories);
                    $categories = array_unique($categories);
                }
                $vendor_id = $User->id;

                $starttime = date('H:i');
                $data = [
                    'product' => [
                        'product_type' => 1,
                        'sales_type' => 2,
                        'product_name' => $request->title,
                        'product_name_arabic' => $request->product_name_arabic,
                        'product_desc_short' => $request->product_desc,
                        'product_desc_full' => $request->description,
                        'product_desc_full_arabic' => $request->product_desc_full_arabic,
                        'product_desc_short_arabic' => $request->product_desc_arabic,
                        'product_vender_id' => $User->id,
                        'default_category_id' => $categories[0],
                        'brand' => 0, //$request->brand,
                        'store_id' => $request->store_id ?? 0,
                        'moda_main_category' => 0,
                        'moda_sub_category' => 0,
                        'starting_price' => $request->starting_price,
                        'starting_date' => date('Y-m-d'),
                        'end_date' => $request->end_date,
                        'start_time' => $starttime,
                        'health' => $request->health,
                        'color' => $request->color,
                        'age' => $request->age,
                        'quantity' => $request->quantity,
                        'gender' => $request->gender,
                        'featured' => $request->featured ?? 0,
                        'location' => $request->location,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'dial_code' => $request->dial_code,
                        'contact_mobile' => $request->contact_mobile,
                        'city_id' => (int)$request->city_id,
                        'end_time'  => '18:10:00',
                        'ending_date' => $request->end_date . "18:10:00"
                        /* 'end_time'  =>'12:00:00',
                    'ending_date'=> $request->end_date."12:00:00"*/

                    ],
                    'product_category' => $categories,

                ];
                if (!$id) {
                    $action_date = date('Y-m-d H:i:s');
                    $data['product']['product_vender_id'] = $vendor_id;
                    $data['product']['product_created_by'] = $vendor_id;
                    $data['product']['created_at'] = $action_date;
                    $data['product']['product_unique_iden'] = -1;
                    $data['product']['product_status'] = 0;
                    $data['product']['product_vendor_status'] = 0;
                    $data['product']['product_deleted'] = 0;
                    $data['product']['product_variation_type'] = 1;
                    $data['product']['product_taxable'] = 1;
                    $data['product']['product_status'] = 0;
                } else {
                    unset($data['product']['product_type']);
                    $data['product']['product_updated_by'] = $vendor_id;
                    $data['product']['updated_at'] = date('Y-m-d H:i:s');
                }

                $imagesList = [];
                $videosList = [];
                $thumb_list = [];
                if ($id) {
                    if (!empty($productDetails->image)) {
                        $imagesList = explode(",", $productDetails->image);
                    }
                    if (!empty($productDetails->video_path)) {
                        $videosList = explode(",", $productDetails->video_path);
                    }
                    if (!empty($productDetails->video_thumb)) {
                        $thumb_list = json_decode($productDetails->video_thumb);
                    }
                }
                $image = $request->file('image');
                if (!empty($image)) {
                    foreach ($image as $files) {
                        $destinationPath = 'uploads/products/';

                        $file_name = rand(1000, 9000) . time() . "." . $files->getClientOriginalExtension();
                        $files->move($destinationPath, $file_name);
                        $imagesList[] = $file_name;
                    }
                }

                $videos = $request->file('videos');

                if (!empty($videos)) {
                    foreach ($videos as $files) {
                        $destinationPath = 'uploads/products/';
                        $gmname = rand(1000, 9000) . time();
                        $file_name = $gmname . "." . $files->getClientOriginalExtension();
                        $files->move($destinationPath, $file_name);
                        $videosList[] = $file_name;
                        $ffprobe = \FFMpeg\FFProbe::create();
                        $ffmpeg     = \FFMpeg\FFMpeg::create();
                        $capture_frame_second   = 0;;
                        //echo asset('uploads/products/'.$file_name);
                        $video_capture_filename =  $gmname . ".jpg";
                        $video = $ffmpeg->open(asset('uploads/products/' . $file_name));
                        $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($capture_frame_second))->save($destinationPath . $video_capture_filename);;


                        $thumb_list[] = ['th' => $video_capture_filename, 'fl' => $file_name];
                    }
                }


                $data['product_simple_variant'] = [
                    'regular_price' => $request->starting_price,
                    'sale_price' => $request->starting_price,
                    'stock_quantity' => $request->stock_quantity,
                    'product_desc' => '',
                    'product_full_descr' => $request->description,
                    'barcode' => '',
                    'pr_code' => '',
                    'weight' => 0,
                    'length' => 0, //$request->length,
                    'height' => 0,
                    'width' => 0, //$request->width,
                    'image' => implode(",", $imagesList),
                    'video_path' => implode(",", $videosList),
                    'video_thumb' => json_encode($thumb_list),
                    'size_chart' => '',

                ];
                if ($id) {
                    $ret = 1;
                    $data['product']['updated_at'] = date('Y-m-d H:i:s');
                    $ret = \App\Models\ProductModel::update_product($id, $data['product'], $data['product_category'], [], [], $data);
                    if ($ret) {
                        $status = "1";
                        $message = "Auction Updated Successfully";
                    } else {
                        $message = "Something went wrong please try again";
                    }
                } else {
                    $ret = \App\Models\ProductModel::addProductByVendor($data);

                    if ($ret) {

                        $status = "1";
                        $message = "Auction Saved Successfully";
                    } else {
                        $message = "Something went wrong please try again";
                    }
                }
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data]);
    }

    public function myAuctions(Request $request)
    {
        $status  = "1";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $User    =  getUserData($request);
        $filter = ['product.deleted' => 0, 'product_vender_id' => $User->id];
        $params = [];
        if ($User) {
            $params['user_id'] = $User->id;
        } else {
            $params['user_id'] = 0;
        }
        $page = ($request->page) ? $request->page : 1;
        $limit = ($request->limit) ? $request->limit : config('global.per_page_limit');
        $offset = ($page - 1) * $limit;
        $total_products = $products = \App\Models\ProductModel::search_auctions($filter, '', [], $params)->count();
        $products = \App\Models\ProductModel::search_auctions($filter, '', [], $params)->limit($limit)->skip($offset)->get();
        foreach ($products as $key => $value) {
            $products[$key] = processProduct($value);
        }
        $o_data = $products;
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data, 'total_count' => $total_products]);
    }

    public function delete(Request $request)
    {
        $status  = "0";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $validator = Validator::make($request->all(), [
            'auction_id'      => 'required',

        ]);
        if ($validator->fails()) {
            $msg = setValidationMesages($validator);
            $message = $msg['message'];
            $errors = $msg['errors'];
            $o_data = (object)$o_data;
        } else {
            $User    =  getUserData($request);
            $checkproduct = \App\Models\ProductModel::where('id', $request->auction_id)->where('product_vender_id', $User->id)->where(['sales_type' => 2, 'deleted' => 0])->get()->first();
            if ($checkproduct != null) {
                $checklareadyOrderd  = \App\Models\OrderProductsModel::where('product_id', $request->auction_id)->get()->first();
                if ($checklareadyOrderd == null) {
                    $checkproduct->deleted = 1;
                    $checkproduct->save();
                    $status = "1";
                    $message = "Auction Deleted Successfully";
                    exec("php " . base_path() . "/artisan item:deleted " . $request->auction_id . " > /dev/null 2>&1 & ");
                } else {
                    $message = "Can't delete Item";
                }
            } else {
                $message = "Invalid Auction Item";
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data]);
    }
    public function notify(Request $request)
    {
        $status  = "0";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $validator = Validator::make($request->all(), [
            'auction_id'      => 'required',

        ]);
        if ($validator->fails()) {
            $msg = setValidationMesages($validator);
            $message = $msg['message'];
            $errors = $msg['errors'];
            $o_data = (object)$o_data;
        } else {
            $User           =  getUserData($request);
            $checkproduct   = \App\Models\ProductModel::where('id', $request->auction_id)->where(['sales_type' => 2, 'deleted' => 0])->get()->first();
            if ($checkproduct != null) {
                if (strtotime($checkproduct->end_date) >= strtotime(date('Y-m-d'))) {
                    $userIds['users'] = [];
                    $userIds['send'] = [];
                    $notify_user_ids    =   $checkproduct->notify_user_ids;
                    if ($notify_user_ids != null) {
                        $notify_user_ids = json_decode($notify_user_ids);

                        $userIds['users'] = (array)$notify_user_ids->users;
                        $userIds['send'] = (array)$notify_user_ids->send;
                    }
                    if (!in_array($User->id, $userIds['users'])) {
                        $userIds['users'][] = $User->id;
                        $checkproduct->notify_user_ids = json_encode($userIds);
                        $checkproduct->save();
                        $status  = "1";
                        $message = "Notification will sent when the auction is active";
                    } else {
                        $message = "Settings already updated";
                    }
                } else {
                    $message = "Auction expired";
                }
            } else {
                $message = "Invalid auction item";
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data]);
    }

    public function auctionHistory(Request $request)
    {
        $status         = "1";
        $message        = "";
        $o_data         = [];
        $errors         = [];
        $User = $this->validateAccesToken($request->access_token);
        $page = ($request->page) ? $request->page : 1;
        $limit = ($request->limit) ? $request->limit : config('global.per_page_limit');
        $offset = ($page - 1) * $limit;
        $history = \App\Models\AuctionBids::select(
            'auction_bids.id',
            'auction_bids.status',
            'auction_bids.product_id',
            'auction_bids.bid_price',
            'auction_bids.bid_date',
            'product.product_name',
            'product_selected_attribute_list.image',
            'product_selected_attribute_list.product_full_descr',
            'users.name as seller_name',
            'users.dial_code as seller_dial_code',
            'users.phone as seller_phone',
            'users.user_image as seller_image'
        )
            ->leftjoin('product', 'product.id', 'auction_bids.product_id')
            ->leftjoin('product_selected_attribute_list', 'product_selected_attribute_list.product_id', 'product.id')
            ->where('auction_bids.user_id', $User->id)
            ->where('auction_bids.status', 1)
            ->leftjoin('users', 'users.id', 'product.product_vender_id');

        if ($request->status) {
            $history        = $history->whereIn('auction_bids.status', explode(",", $request->status));
        }
        $total_count = $history;
        $total_count = $total_count->count();

        $history        = $history->get();

        $auction_status = config('global.auction_status');

        foreach ($history as $key1 =>  $row) {
            $row->seller_image = get_uploaded_image_url($row->seller_image, 'user_image_upload_dir');
            $row->seller_mobile_number = $row->seller_dial_code . $row->seller_phone;
            $row->statusText = $auction_status[$row->status];
            $row->bid_date = $row->bid_date;
            $gallery = explode(",", $row->image);
            $row->image = "";
            $g_list = [];
            if (is_array($gallery) && !empty($gallery)) {
                foreach ($gallery as $key => $value) {
                    if (isset($value) && file_exists(public_path() . "/uploads/products/{$value}")) {
                        if ($row->image == "") {
                            $row->image =  url(config('global.upload_path') . config('global.product_image_upload_dir') . $value);
                        }
                    }
                }
            }
        }
        $o_data['list']         =  convert_all_elements_to_string($history);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object)$o_data, 'total_count' => $total_count]);
    }



    public function payBid(Request $request)
    {
        $status         = "0";
        $message        = "";
        $o_data         = [];
        $errors         = [];
        $validator = Validator::make($request->all(), [
            'id'      => 'required',
            'payment_method'      => 'required',

        ]);
        if ($validator->fails()) {
            $msg = setValidationMesages($validator);
            $message = $msg['message'];
            $errors = $msg['errors'];
            $o_data = (object)$o_data;
        } else {
            $User           =  getUserData($request);
            $auctionDetails = \App\Models\AuctionBids::where('id', $request->id)->where('status', 1)->get()->first();

            if ($auctionDetails != null) {
                $payObj = new \App\Models\PaymentReport();
                $payObj->user_id = $User->id;
                $payObj->amount = $auctionDetails->bid_price;
                $payObj->method_type = config('global.payment_report_type_auction_bid');
                $payObj->payment_status = 'P';
                $payObj->payment_method = $request->payment_method;
                if ($payObj->payment_method == config('global.payment_method_wallet')) {
                    $payObj->wallet_amount_used = $auctionDetails->bid_price;
                }
                $payObj->ref_id = $request->id;
                $validPay = 1;
                $current_wallet_amount = currentWalletBalance($User);
                if ($payObj->payment_method == config('global.payment_method_wallet') && $auctionDetails->bid_price >  $current_wallet_amount) {
                    $validPay = 0;
                    $message = "Wallet is not having enough amount";
                    $o_data = (object)$o_data;
                }
                if ($validPay == 1) {
                    $payObj->save();
                    if ($payObj->payment_method == config('global.payment_method_wallet')) {
                        $ret = $this->bidSuccess($payObj->id);
                        if ($ret == true) {
                            $User->wallet_amount = $User->wallet_amount - $auctionDetails->bid_price;
                            $User->save();
                            $data = [
                                'user_id'       => $User->id,
                                'wallet_amount' => $auctionDetails->bid_price,
                                'amount'        => $auctionDetails->bid_price,
                                'pay_type'      => 'debited',
                                'pay_method'    => 0,
                                'description'   => 'Bid Payment',
                            ];

                            wallet_history($data);
                            $status = "1";


                            $message = "Order Placed Successfully";
                        } else {
                            $message = "Failed";
                            $o_data = (object)$o_data;
                        }
                    } else if (in_array($payObj->payment_method, [config('global.payment_method_card'), config('global.payment_method_apple_pay'), config('global.payment_method_google_pay'), config('global.payment_method_wire_transfer')])) {

                        $payment = payment_init_stripe($payObj->id, round($auctionDetails->bid_price, 2), $User->id);

                        $status  = "1";
                        $o_data['invoice_id'] = $payObj->id;
                        $o_data['payment_ref'] = $payment['payment_ref'];
                    }
                    $o_data['invoice_id'] = $payObj->id;
                }
            } else {
                $message = "Failed to pay";
                $o_data = (object)$o_data;
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data]);
    }
    public function bidPaySuccess(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'invoice_id'    => 'required|integer|min:1',
            'payment_reference' => 'required'
        ], [
            'invoice_id.required'       =>  'Payment Invoice Id is required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $User =  getUserData($request);
            $ret = $this->bidSuccess($request->invoice_id);
            if ($ret == true) {
                $message = "Payment Done Successfully";
                $status = "1";
            } else {
                $message = "Failed Payment";
            }
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object)$o_data]);
    }
    public function bidSuccess($pay_id)
    {
        DB::beginTransaction();
        try {
            if ($pay_id) {
                $payObj                     = \App\Models\PaymentReport::find($pay_id);
                if ($payObj != null && isset($payObj->payment_status) &&   $payObj->payment_status == 'P') {
                    $auctionBid                 =  \App\Models\AuctionBids::find($payObj->ref_id);
                    $auctionBid->payment_status = 1;
                    $auctionBid->payment_date   = getcreatedAt();
                    $auctionBid->status         = 2;
                    $auctionBid->save();
                    $payObj->payment_status     =  'A'; //paid
                    $payObj->save();
                    $statusChange    = \App\Models\AuctionStatusChange::updateAuctionStatus($auctionBid);
                } else {
                    return false;
                }

                DB::commit();
                return true;
            }
        } catch (\Excepton $e) {
            DB::rollback();
            return false;
        }
        return false;
    }


    public function releaseAuction(Request $request)
    {
        $status         = "0";
        $message        = "";
        $o_data         = [];
        $errors         = [];
        $validator = Validator::make($request->all(), [
            'id'      => 'required',

        ]);
        if ($validator->fails()) {
            $msg        = setValidationMesages($validator);
            $message    = $msg['message'];
            $errors     = $msg['errors'];
            $o_data     = (object)$o_data;
        } else {
            $User           =  getUserData($request);
            $ret            = $this->releaseOrReject($request, $User, 4);
            $message        = $ret['message'];
            $status         = $ret['status'];
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data]);
    }

    public function rejectAuction(Request $request)
    {
        $status         = "0";
        $message        = "";
        $o_data         = [];
        $errors         = [];

        $validator = Validator::make($request->all(), [
            'id'      => 'required',

        ]);
        if ($validator->fails()) {
            $msg        = setValidationMesages($validator);
            $message    = $msg['message'];
            $errors     = $msg['errors'];
            $o_data     = (object)$o_data;
        } else {
            $User           =  getUserData($request);
            $ret            = $this->releaseOrReject($request, $User, 5);
            $message        = $ret['message'];
            $status         = $ret['status'];
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object)$o_data]);
    }
    private function releaseOrReject($request, $User, $chstatus)
    {
        DB::beginTransaction();
        try {
            $status = "0";
            $auctionDetails = \App\Models\AuctionBids::find($request->id);
            if ($auctionDetails != null && $auctionDetails->status == 2 && $auctionDetails->user_id == $User->id) {
                $auctionDetails->status = $chstatus;
                $auctionDetails->updated_at = getcreatedAt();
                $auctionDetails->save();
                if ($auctionDetails->save()) {
                    $statusChange    = \App\Models\AuctionStatusChange::updateAuctionStatus($auctionDetails);
                    exec("php " . base_path() . "/artisan auction:statuschange " . $auctionDetails->id . "  > /dev/null 2>&1 & ");

                    if ($chstatus == 4) {
                        $message = "Auction Released Successfully";
                    } else {
                        $message = "Auction Rejected Successfully";
                    }

                    $status = "1";
                } else {
                    $message = "Failed";
                }
            } else {
                $message = "Invalid auction";
            }
            DB::commit();
        } catch (\Excepton $e) {

            DB::rollback();
        }
        return ['status' => $status, 'message' => $message];
    }

    public function rateAuction(Request $request)
    {
        $status         = "0";
        $message        = "";
        $o_data         = [];
        $errors         = [];

        $validator = Validator::make($request->all(), [
            'id'      => 'required',
            'rating'  => 'required',
            'review'  => 'required'

        ], [
            'id.required'      => 'Please Select Auction',
            'rating'           => 'Enter Rating',
            'review'           => 'Enter Review'

        ]);
        if ($validator->fails()) {
            $msg        = setValidationMesages($validator);
            $message    = $msg['message'];
            $errors     = $msg['errors'];
            $o_data     = (object)$o_data;
        } else {
            $User           =  getUserData($request);

            $checkExist =  \App\Models\AuctionRating::where('user_id', $User->id)->where('auction_id', $request->id)->get()->first();
            if ($checkExist != null) {
                $message = "Auction Already Rated";
            } else {
                $ratingObj = new \App\Models\AuctionRating();
                $ratingObj->auction_id = $request->id;
                $ratingObj->user_id = $User->id;
                $ratingObj->rating = $request->rating;
                $ratingObj->review = $request->review;
                $ratingObj->save();
                $status = "1";
                $message = "Auction Rated Successfully";
            }
        }

        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data]);
    }
}