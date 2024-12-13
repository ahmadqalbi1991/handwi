<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendorDetailsModel;
use App\Models\User;
use App\Models\Likes;
use App\Models\BannerModel;
use App\Models\Categories;
use App\Models\ProductModel;
use App\Models\Service;
use App\Models\FeaturedProducts;
use App\Models\Rating;
use App\Models\FeaturedProductsImg;
use App\Models\ServiceCategories;
use App\Models\ActivityType;
use App\Models\ServicePrice;
use App\Models\ContactUsSetting;
use Validator;

class ActivityController extends Controller
{
    function details(Request $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), ['id' => 'required']);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => (string) 0,
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }

        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();
        $limit = isset($request->limit) ? $request->limit : 10;
        $offset = isset($request->page) ? ($request->page - 1) * $request->limit : 0;

        $where = [];
        $params = [];
        $filter['search_text'] = $request->search_text;
        $filter['emirate_id']  = $request->emirate_id;
        $filter['city_id']     = $request->city_id;
        //banner END
        $activity  = ActivityType::where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->select('id','name','description','logo','banner_image','indvidual_logo as all_image')
        ->with(['categories'=>function($q) use($request){
            return $q->where(['deleted' => '0','active' => '1','parent_id'=>'0'])->orderBy('sort_order', 'asc')
            ->select('id','name','image','banner_image','activity_id','sort_order')
            ->with(['children'=>function($q) use($request){
                return $q->where(['deleted' => '0','active' => '1'])->orderBy('sort_order', 'asc')
                ->select('id','name','image','banner_image','activity_id','parent_id');
            }]);
        }])
        ->find($request->id);

        // $where['activity_id']   = $request->id;
        $activity->min_price = ProductModel::select('product.id', 'product.product_name', 'product.product_type', 'default_attribute_id','master_product','product.featured','product.recommended','product_selected_attribute_list.regular_price')
        ->join("product_selected_attribute_list","product_selected_attribute_list.product_id","=","product.id")
        ->orderBy('regular_price','asc')->first()->regular_price ?? 0;

        $activity->max_price = ProductModel::select('product.id', 'product.product_name', 'product.product_type', 'default_attribute_id','master_product','product.featured','product.recommended','product_selected_attribute_list.regular_price')
        ->join("product_selected_attribute_list","product_selected_attribute_list.product_id","=","product.id")
        ->orderBy('regular_price','desc')->first()->regular_price ?? 0;


        $con = ContactUsSetting::first();

        $o_data['transport_website_link']  = $con->transport_website_link;
        $o_data['activity']  = $activity;

        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => convert_all_elements_to_string($o_data)], 200);
    }
}