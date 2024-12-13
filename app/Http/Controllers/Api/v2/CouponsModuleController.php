<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\BuildingTypes;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CategoriesCoupons;
use App\Models\Coupon;
use App\Models\CouponImages;
use App\Models\CouponBrands;
use App\Models\ServiceInclude;
use App\Models\HourlyRate;
use App\Models\VendorModel;
use App\Models\VendorDetailsModel;
use App\Models\BannerModel;
use App\Models\ServiceCategorySelected;
use Validator;

class CouponsModuleController extends Controller
{

    public function couponCategories(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $categories = CategoriesCoupons::where(['active'=>1,'deleted'=>0,'parent_id'=>0])->orderBy('sort_order','asc')
        ->get();
        $categorieslist = [];
        $key = 0;
        foreach ($categories as $value) {
            $count = Coupon::where('category_id',$value->id)->where('start_date','<=',date('Y-m-d'))->where('coupon_end_date','>=',date('Y-m-d'))->get()->count();
            
            if($count > 0)
            {
              $categorieslist[$key] = new \stdClass;
              $categorieslist[$key]->id = $value->id;
              $categorieslist[$key]->name = $value->name;
              $categorieslist[$key]->image = $value->image;
              $categorieslist[$key]->description = (string) $value->description; 
              $categorieslist[$key]->sub_categories = [];
              $key++;
            }
            
        }

        $o_data['list'] = convert_all_elements_to_string($categorieslist);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function couponCategoriesdetails(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = (object)[];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $categories = CategoriesCoupons::where(['active'=>1,'deleted'=>0,'id'=>$request->category_id])->first();
        
        if(!empty($categories))
        {
            $o_data = convert_all_elements_to_string($categories->toArray());
            $datafetch = Coupon::select('*')
        ->orderBy('sort_order','asc')->whereDate('start_date','<=',date('Y-m-d'))->whereDate('coupon_end_date','>=',date('Y-m-d'))->where('coupons.category_id',$request->category_id);
        if(!empty($request->search))
        {

        $datafetch =$datafetch->where('coupons.title', 'ilike', '%'.$request->search.'%');
        }
        $datafetch =$datafetch->get();
        foreach ($datafetch as $key => $value) {
            $datafetch[$key]->images = CouponImages::where('coupon_id',$value->id)->get();
            $datafetch[$key]->brand = CouponBrands::where('id',$value->brand_id)->first();
        }
       

        
      

           
            $o_data['coupon_list'] = convert_all_elements_to_string($datafetch);
           
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function couponDetails(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = (object)[];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'coupon_id' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        
            $datafetch = Coupon::select('*')
        ->orderBy('sort_order','asc')->where('coupons.id',$request->coupon_id);
        if(!empty($request->search))
        {

        $datafetch =$datafetch->where('coupons.title', 'ilike', '%'.$request->search.'%');
        }
        $datafetch =$datafetch->first();
    
            $datafetch->images = CouponImages::where('coupon_id',$datafetch->id)->get();
            $datafetch->brand = CouponBrands::where('id',$datafetch->brand_id)->first();
       
      

           
            $o_data = convert_all_elements_to_string($datafetch->toArray());
           
       
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
}