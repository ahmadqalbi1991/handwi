<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityType;
use App\Models\ProductModel;
use App\Models\Categories;
use App\Models\Service;
use App\Models\VendorModel;
use Validator;
use App\Models\PromotionBanners;

class PromotionBannerController extends Controller
{
    //
    public function index()
    {
        $page_heading = "Promotion Banners";
        $filter = [];
        $params = [];
        
        
        $list = PromotionBanners::OrderBy('id','desc')->paginate(10);

        return view("admin.promotion.list", compact("page_heading", "list"));
    }
    public function create(Request $request)
    {
        $filter = ['product.deleted' => 0];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $sortby = "product.id";
        $sort_order = "desc";
        $search_key = $params['search_key'];
        $products   = ProductModel::get_products_list($filter, $params, $sortby, $sort_order)->paginate(1000);
        $services   = Service::where(['active'=>1,'deleted'=>0])->paginate(1000);
        $activityTypes = ActivityType::with('account')->where(['deleted' => '0'])
        ->orderBy('sort_order', 'asc')->get();

        $vendors = VendorModel::select('vendor_details.company_name as name','users.id as id')->where(['role'=>'3','users.deleted'=>'0'])
        ->leftjoin('vendor_details','vendor_details.user_id','=','users.id')
        ->leftjoin('industry_types','industry_types.id','=','vendor_details.industry_type')->get();

        $category_ids = [];

        $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0,'activity_id'=>7])->get()->toArray();
        $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->get()->toArray();

        $parent_categories = array_column($parent_categories, 'name', 'id');
        asort($parent_categories);
        $category_list = $parent_categories;

        $sub_categories = [];
        foreach ($parent_categories_list as $row) {
            $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
        }
        $sub_category_list = $sub_categories;
        if ($request->isMethod('post')) {
            $status = "0";
            $message = "";
            $errors = '';
            if($request->banner != ''){
                $rule =[
                    'title' => 'required',
                    'banner' => 'required|image',
                ];
            }else{
                $rule = ['title' => 'required',];
            }
            $validator = Validator::make($request->all(),
                $rule,
                [
                    'title.required'=>'Fill title',
                    'banner.required'=>'Select image',
                    'banner.image' => 'should be in image format (.jpg,.jpeg,.png)',
                ]
            );
            if ($validator->fails()) {
                $status = "0";
                $message = "Validation error occured";
                $errors = $validator->messages();
            } else {
                if($request->id > 0){
                    $banner = PromotionBanners::find($request->id);
                }else{
                    $banner = new PromotionBanners();
                    $banner->created_at = gmdate('Y-m-d H:i:s');
                }
                $banner->title = $request->title;
                $banner->url = $request->url;
                $banner->updated_at = gmdate('Y-m-d H:i:s');
                $banner->status = $request->active??1;
                $banner->type = $request->applies_to??4;
                $banner->category = $request->category_id??0;
                $banner->product = $request->txt_products??0;
                $banner->service = $request->txt_services??0;
                $banner->banner_type = $request->banner_type??0;
                $banner->activity = $request->activity??0;
                $banner->store = $request->store??0;
                
                if ($file = $request->file("banner")) {
                    $dir = config('global.upload_path').config('global.banner_image_upload_dir');
                    $file_name = time().uniqid().".".$file->getClientOriginalExtension();
                    $file->move($dir, $file_name);
                    //$file->storeAs(config('global.banner_image_upload_dir'),$file_name,'s3');
                    $banner->image_name = $file_name;
                }
                $banner->save();
                if ($request->id > 0) {

                    $status = "1";
                    $message = "Banner updated successfully";
                    $errors = '';
                } else {
                    $status = "1";
                    $message = "Banner created successfully";
                    $errors = '';
                }
            }
            echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
        } else {
            $page_heading = "Create Promotion Banner";
            return view('admin.promotion.create', compact('page_heading','activityTypes','category_ids','vendors','products','services','category_list','category_ids'));
        }

    }

    public function edit($id = '')
    {
        $filter = ['product.deleted' => 0];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $sortby = "product.id";
        $sort_order = "desc";
        $search_key = $params['search_key'];
        $products   = ProductModel::get_products_list($filter, $params, $sortby, $sort_order)->paginate(1000);
        $services   = Service::where(['active'=>1,'deleted'=>0])->paginate(1000);
        $activityTypes = ActivityType::with('account')->where(['deleted' => '0'])
        ->orderBy('sort_order', 'asc')->get();

        $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0,'activity_id'=>7])->get()->toArray();
        $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->get()->toArray();

        $parent_categories = array_column($parent_categories, 'name', 'id');
        asort($parent_categories);
        $category_list = $parent_categories;

        $sub_categories = [];
        foreach ($parent_categories_list as $row) {
            $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
        }
        $sub_category_list = $sub_categories;

        
        $banner = PromotionBanners::find($id);
        $category_ids = [$banner->category];
        $vendors = VendorModel::select('vendor_details.company_name as name','users.id as id')->where('activity_id','=',$banner->activity)->where(['role'=>'3','users.deleted'=>'0'])
        ->leftjoin('vendor_details','vendor_details.user_id','=','users.id')
        ->leftjoin('industry_types','industry_types.id','=','vendor_details.industry_type')->get();
        if ($banner) {
            $page_heading = "Edit Promotion Banner";
            return view('admin.promotion.create', compact('page_heading','banner','activityTypes','vendors','products','services','category_list','category_ids'));
        } else {
            abort(404);
        }
    }

    public function delete($id = '')
    {
        PromotionBanners::where('id', $id)->delete();
        $status = "1";
        $message = "Banner removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }
}
