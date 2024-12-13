<?php

namespace App\Http\Controllers\admin;

use App\Models\Coupon;
use App\Models\CategoriesCoupons;
use App\Models\CouponBrands;
use App\Models\CountryModel;
use App\Models\CouponImages;
use App\Models\CouponCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;

class CouponVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page_heading = "Coupons List";
        $datamain = Coupon::where('brand_id',$request->brand)->get();
        foreach($datamain as $key=> $value)
        {
            $datamain[$key]->category = CategoriesCoupons::where('id',$value->category_id)->first();
            $datamain[$key]->brand = CouponBrands::where('id',$value->brand_id)->first();
        }
        
        
        return view('admin.coupons_voucher.list',compact('page_heading','datamain'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if(CouponBrands::whereId($request->brand)->exists()){
            $brand_name = CouponBrands::whereId($request->brand)->first('name');
            $page_heading = $brand_name->name . "- Coupons";
        }
        else{
            $page_heading = "Coupons";
        }
        $mode = "Create";
        $id = "";
        $name = "";
        $name_ar = "";
        $coupon_code = "";
        $brand_id = $request->brand??'';
        $category_id = "";
        $image = "";
        $active = "1";
        $trending = "0";
        $selected_countries = [];
        $description = "";
        $description_ar = "";
        $hot_deal = "";
        $categories = CategoriesCoupons::where('deleted',0)->get();
        $brands = CouponBrands::
        where(['coupon_brand.deleted' => 0])
        ->get();
        $countries = CountryModel::where('deleted',0)->get();
        return view('admin.coupons_voucher.create',compact('page_heading','mode','id','name','name_ar','brand_id','category_id','image','active','categories','brands','coupon_code','countries','trending','description','description_ar','selected_countries','hot_deal'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        }
        else{
            $input = $request->all();
            $check_exist = Coupon::where(['title' => $request->name, 'category_id' => $request->category_id])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'title' => $request->name,
                    'title_ar' => $request->name_ar,
                    'coupon_code' => $request->coupon_code,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
                    'category_id' => $request->category_id ?? 0,
                    'brand_id' => $request->brand_id ?? 0,
                    'active' => $request->active,
                    'description' => $request->description,
                    'description_ar' => $request->description_ar,
                    'start_date'       => $request->startdate,
                    'coupon_end_date'  => $request->expirydate,
                ];
                if($request->has('hot_deal')){
                    $ins['hot_deal'] = 1;
                }
                else{
                    $ins['hot_deal'] = 0;   
                }
                if($request->has('trending')){
                    $ins['trending'] = 1;
                }
                else{
                    $ins['trending'] = 0;   
                }
                if($request->file("image")){
                    $response = image_upload($request,'brand','image');
                    if($response['status']){
                        $ins['image'] = $response['link'];
                    }
                }

                if ($request->id != "") {
                    $brand = Coupon::find($request->id);
                    $brand->update($ins);
                    //$brand->countries()->sync($request->countries ?? []);
                    $status = "1";
                    $message = "Coupon updated succesfully";
                    $inid = $request->id;
                }
                else{
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    $brand = Coupon::insertGetId($ins);
                    //$brand->countries()->sync($request->countries ?? []);
                    $inid = $brand;
                    $status = "1";
                    $message = "Coupon added successfully";
                }
                
                $banners = $request->file("banners");

                $banner_images = [];
                if ($banners) {
                foreach ($banners as $ikey => $img) {

                    if ($file = $img) {
                        $dir = config('global.upload_path') . "/" . config('global.coupon_image_upload_dir');
                        $file_name = time() . uniqid() . "." . $file->getClientOriginalExtension();
                        $file->storeAs(config('global.coupon_image_upload_dir'), $file_name, config('global.upload_bucket'));

                        

                        $gameimages = new CouponImages;
                        $gameimages->coupon_id = $inid;
                        $gameimages->image = $file_name;
                        $gameimages->save();

                    }


                }
               }
            }
            else {
                $status = "0";
                $message = "Name should be unique";
                $errors['name'] = $request->name . " already added";
            }
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $datamain = Coupon::find($id);
        if ($datamain) {
            if(CouponBrands::whereId($request->brand)->exists()){
                $brand_name = CouponBrands::whereId($request->brand)->first('name');
                $page_heading = $brand_name->name . " Coupons";
            }
            else{
                $page_heading = "Coupons";
            }
            $datamain->images = CouponImages::where('coupon_id',$datamain->id)->get();
            

            $mode = "Edit";
            $id = $datamain->id;
            $name = $datamain->title;
            $name_ar = $datamain->title_ar;
            $coupon_code = $datamain->coupon_code;
            $brand_id = $datamain->brand_id;
            $category_id = $datamain->category_id;
            $image = $datamain->image;
            $active = $datamain->active;
            $trending = $datamain->trending;
            $hot_deal = $datamain->hot_deal;
            $description = $datamain->description;
            $description_ar = $datamain->description_ar;
            $selected_countries = [];
            if($datamain->coupon_contry){
                //$selected_countries = $datamain->coupon_contry->pluck('country_id')->toArray() ?? [];
            }
            $categories = CategoriesCoupons::where('deleted',0)->get();
            $brands = CouponBrands::
                where(['deleted' => 0])
            ->get();
            $countries = CountryModel::where('deleted',0)->get();
        return view('admin.coupons_voucher.create',compact('page_heading','datamain','mode','id','name','name_ar','brand_id','category_id','image','active','categories','brands','coupon_code','countries','selected_countries','trending','description','description_ar','hot_deal'));   
        }
        else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $category = Coupon::find($id);
        if ($category) {
            $category->delete();
            $status = "1";
            $message = "Coupon removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }

    public function sort(Request $request)
    {
        if ($request->ajax()) {
            $status = 0;
            $message = '';

            $items = $request->items;
            $items = explode(",", $items);
            $sorted = Coupon::sort_item($items);
            if ($sorted) {
                $status = 1;
            }

            echo json_encode(['status' => $status, 'message' => $message]);

        } else {
            $page_heading = "Sort Coupons";
            $list = Coupon::orderBy('sort_order', 'asc')->get();
            $back = url("admin/coupons_voucher");
            return view("admin.sort_coupon", compact('page_heading', 'list','back'));
        }
    }
    public function delete_image($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $img =  CouponImages::find($id);
        if ($img) {
            $img->delete();
            $status = "1";
            $message = "Image removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
}
