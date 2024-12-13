<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\IndustryTypes;
use App\Models\ProductLikes;
use App\Models\ProductModel;
use App\Models\StoreLikes;
use App\Models\Stores;
use App\Models\User;
use App\Models\StoreImages;
use DB;
use Illuminate\Http\Request;
use Validator;

class StoreController extends Controller
{
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
                return $user->id;
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
    public function industry_types(Request $request)
    {
        $industry_types = IndustryTypes::where(['deleted' => 0]);
        if ($request->search_text) {
            $industry_types = $industry_types->where('name', 'ilike', '%' . $request->search_text . '%');
        }
        $industry_types = $industry_types->order_by('sort_order', 'asc')->get();
    }
    function list(Request $request) {
        $status = 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => 0,
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }

        $access_token = $request->access_token;
        $limit = isset($request->limit) ? $request->limit : 10;
        $offset = isset($request->page) ? ($request->page - 1) * $request->limit : 0;

        $where['deleted'] = 0;
        $where['active'] = 1;
        $where['verified'] = 1;
        $filter['search_text'] = $request->search_text;
        $filter['lat'] = $request->latitude;
        $filter['long'] = $request->longitude;
        $filter['distance'] = $request->distance;
        // $datastore = Stores::get();
        
        $stores = Stores::get_stores($where, $filter, $limit, $offset)->get();
        $user = User::where('user_access_token', $access_token)->first();
        foreach ($stores as $key => $val) {
            $stores[$key]->logo = asset($val->logo);
            $stores[$key]->cover_image = asset($val->cover_image);
            $stores[$key]->is_liked = 0;
            if ($user) {
                $is_liked = StoreLikes::where(['store_id' => $val->id, 'user_id' => $user->id])->count();
                if ($is_liked) {
                    $stores[$key]->is_liked = 1;
                }
            }

        }

        $o_data['list'] = $stores;
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }

    public function store_details(Request $request)
    {
        $status = 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => 0,
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;

        $where['deleted'] = 0;
        $where['active'] = 1;
        $where['verified'] = 1;
        $where['id'] = $request->store_id;

        $details = Stores::where($where)->first();
        $user = User::where('user_access_token', $access_token)->first();

        if ($details) {
            $details->logo = asset($details->logo);
            $details->cover_image = asset($details->cover_image);
            $details->license_doc = asset($details->license_doc);
            $details->vat_cert_doc = asset($details->vat_cert_doc);
            $images = StoreImages::where('store_id', $details->id)->get();
            $banner_images = [];
            foreach($images as $key=>$val){
                $banner_images[$key] = asset($val->image);
            }
            $details->banner_images = $banner_images;
            
            $details->is_liked = 0;
            if ($user) {
                $is_liked = StoreLikes::where(['store_id' => $details->id, 'user_id' => $user->id])->count();
                if ($is_liked) {
                    $details->is_liked = 1;
                }
            }

            $products = ProductModel::select('product.id', 'product.product_name', 'product.product_type', 'default_attribute_id')->where('product.product_status', 1)->where('product.deleted', 0)->where('store_id', $details->id)->orderBy('created_at', 'desc');
            if ($request->search_text) {
                $products->where('product_name', 'ilike', '%' . $request->search_text . '%');
            }
            $products = $products->limit(10)->get();

            $details['products'] = $this->product_inv($products,$user);
        }
        
        $o_data['store'] = $details;
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    function product_inv($products,$user){
        foreach ($products as $key => $val) {
            $products[$key]->is_liked = 0;
            if ($user) {
                $is_liked = ProductLikes::where(['product_id' => $val->id, 'user_id' => $user->id])->count();
                if ($is_liked) {
                    $products[$key]->is_liked = 1;
                }
            }
            $det = [];
            if ($val->default_attribute_id) {
                $det = DB::table('product_selected_attribute_list')->select('product_attribute_id', 'stock_quantity', 'sale_price', 'regular_price', 'image')->where('product_id', $val->id)->where('product_attribute_id', $val->default_attribute_id)->first();
                if ($det) {
                    $images = $det->image;
                    if ($images) {
                        $images = explode(',', $images);
                        $i = 0;
                        $prd_img = [];
                        foreach ($images as $img) {
                            if ($img) {
                                $prd_img[$i] = url(config('global.upload_path') . '/' . config('global.product_image_upload_dir').$img);
                                $i++;
                            }
                        }
                        $det->image = $prd_img;
                    } else {
                        $det->image = [];
                    }
                }

            } else {
                $det = DB::table('product_selected_attribute_list')->select('product_attribute_id', 'stock_quantity', 'sale_price', 'regular_price', 'image')->where('product_id', $val->id)->orderBy('product_attribute_id', 'DESC')->limit(1)->first();

                if ($det) {
                    $images = $det->image;
                    if ($images) {
                        $images = explode(',', $images);
                        $i = 0;
                        $prd_img = [];
                        
                        foreach ($images as $img) {
                            if ($img) {
                                $prd_img[$i] = url(config('global.upload_path') . '/' . config('global.product_image_upload_dir').$img);
                                $i++;
                            }
                        }
                        $det->image = $prd_img;
                    } else {
                        $det->image = [];
                    }
                }
            }
            $products[$key]->inventory = $det;
        }
        return $products;
    }
    public function like_dislike(REQUEST $request)
    {
        $status = 0;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'store_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $store_id = $request->store_id;
            $check_exist = StoreLikes::where(['store_id' => $store_id, 'user_id' => $user_id])->get();
            if ($check_exist->count() > 0) {
                StoreLikes::where(['store_id' => $store_id, 'user_id' => $user_id])->delete();
                $status = 1;
                $message = "disliked";
            } else {
                $like = new StoreLikes();
                $like->store_id = $store_id;
                $like->user_id = $user_id;
                $like->created_at = gmdate('Y-m-d H:i:s');
                $like->save();
                if ($like->id > 0) {
                    $status = 1;
                    $message = "liked";
                } else {
                    $message = "faild to like";
                }
            }
        }
        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }

    public function product_like_dislike(REQUEST $request)
    {
        $status = 0;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'product_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $product_id = $request->product_id;
            $check_exist = ProductLikes::where(['product_id' => $product_id, 'user_id' => $user_id])->get();
            if ($check_exist->count() > 0) {
                ProductLikes::where(['product_id' => $product_id, 'user_id' => $user_id])->delete();
                $status = 1;
                $message = "disliked";
            } else {
                $like = new ProductLikes();
                $like->product_id = $product_id;
                $like->user_id = $user_id;
                $like->created_at = gmdate('Y-m-d H:i:s');
                $like->save();
                if ($like->id > 0) {
                    $status = 1;
                    $message = "liked";
                } else {
                    $message = "faild to like";
                }
            }
        }
        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
}
