<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\WebBannerModel;
use Illuminate\Http\Request;
use Validator;
use App\Models\BannerModel;
use App\Models\ProductModel;
use App\Models\Service;
use App\Models\Categories;
use App\Models\ActivityType;
use App\Models\VendorModel;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_heading = "App Banners";
        $filter = [];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $params['banner_type'] = $_GET['banner_type'] ?? '';
        $bannertype = $params['banner_type'];
        $search_key = $params['search_key'];
        
        $list = BannerModel::get_banners_list($filter, $params)->paginate(10);

        return view("admin.banner.list", compact("page_heading", "list", "search_key",'bannertype'));
    }
    public function web_banner()
    {
        $page_heading = "Web Banners";
        $filter = [];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $search_key = $params['search_key'];
        $list = WebBannerModel::get_banners_list($filter, $params)->paginate(10);
        return view("admin.banner.web_banner", compact("page_heading", "list", "search_key"));
    }
    public function create(Request $request)
    {
        $id='';
        $filter = ['product.deleted' => 0];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $sortby = "product.id";
        $sort_order = "desc";
        $search_key = $params['search_key'];
        $products   = ProductModel::get_products_list($filter, $params, $sortby, $sort_order)->paginate(1000);
        $services   = Service::where(['active'=>1,'deleted'=>0])->paginate(1000);
        $services_4   = Service::where(['active'=>1,'deleted'=>0,'activity_id'=>4])->paginate(1000);
        $services_1   = Service::where(['active'=>1,'deleted'=>0,'activity_id'=>1])->paginate(1000);

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

        $activityTypes = ActivityType::with('account')->where(['deleted' => '0'])
            ->orderBy('sort_order', 'asc')->get();

        $category_ids = [];
        if ($request->isMethod('post')) {
            $status = "0";
            $message = "";
            $errors = '';
            
            $validator = Validator::make($request->all(),
                [
                    // 'banner_title' => 'required',
                    'banner' => 'required|image',
                ],
                [
                    // 'banner_title.required' => 'Title required',
                    'banner.required' => 'Banner required',
                    'banner.image' => 'should be in image format (.jpg,.jpeg,.png)',
                ]
            );
            if ($validator->fails()) {
                $status = "0";
                $message = "Validation error occured";
                $errors = $validator->messages();
            } else {
                $ins['active'] = $request->active;
                $ins['banner_title'] = $request->banner_title;
                $ins['type'] = $request->applies_to??4;
                $ins['category'] = $request->category_id??0;
                $ins['product'] = $request->txt_products??0;
                $ins['service'] = $request->txt_services??0;
                if($request->activity == 1){
                    $ins['service'] = $request->txt_services1??0;
                }
                if($request->activity == 4){
                    $ins['service'] = $request->txt_services4??0;
                }
                $ins['banner_type'] = $request->banner_type??0;
                $ins['url'] = $request->url;
                $ins['activity'] = $request->activity??0;
                $ins['store'] = $request->store??0;
                $ins['created_at'] = gmdate('Y-m-d H:i:s');
                $ins['created_by'] = session("user_id");
                if ($file = $request->file("banner")) {
                    $dir = config('global.upload_path').config('global.banner_image_upload_dir');
                    $file_name = time() . $file->getClientOriginalName();
                    $file->move($dir, $file_name);
                    //$file->storeAs(config('global.banner_image_upload_dir'),$file_name,'s3');
                    $ins['banner_image'] = $file_name;
                }
                if (BannerModel::insert($ins)) {

                    $status = "1";
                    $message = "Banner created";
                    $errors = '';
                } else {
                    $status = "0";
                    $message = "Something went wrong";
                    $errors = '';
                }
            }
            echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
        } else {
            $page_heading = "Create App Banner";
            return view('admin.banner.create', compact('page_heading','products','services','services_1','services_4','sub_category_list','category_list','category_ids','activityTypes','id'));
        }

    }
    public function create_web_banner(Request $request)
    {
        if ($request->isMethod('post')) {
            $status = "0";
            $message = "";
            $errors = '';
            $validator = Validator::make($request->all(),
                [
                    'banner' => 'required|image',
                ],
                [
                    'banner.required' => 'Banner required',
                    'banner.image' => 'should be in image format (.jpg,.jpeg,.png)',
                ]
            );
            if ($validator->fails()) {
                $status = "0";
                $message = "Validation error occured";
                $errors = $validator->messages();
            } else {
                $ins['active'] = $request->active;
                $ins['banner_title_1'] = $request->banner_title_1;
                $ins['banner_title_2'] = $request->banner_title_2;
                $ins['banner_title_3'] = $request->banner_title_3;
                $ins['banner_title_4'] = $request->banner_title_4;
                $ins['created_on'] = gmdate('Y-m-d H:i:s');
                $ins['created_by'] = session("user_id");
                if ($file = $request->file("banner")) {
                    if(isset($request->cropped_upload_image) && $request->cropped_upload_image){
                        $image_parts = explode(";base64,", $request->cropped_upload_image);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];
                        $image_base64 = base64_decode($image_parts[1]);
                        $imageName = uniqid() .time(). '.'.$image_type;
                        $path = \Storage::disk('s3')->put(config('global.banner_image_upload_dir').$imageName, $image_base64);
                        $path = \Storage::disk('s3')->url($path);
                        $ins['banner_image'] = $imageName;
                    }else{
                        $dir = config('global.upload_path') . "/" . config('global.banner_image_upload_dir');
                        $file_name = time() . $file->getClientOriginalName();
                        //$file->move($dir, $file_name);
                        $file->storeAs(config('global.banner_image_upload_dir'),$file_name,'s3');
                        $ins['banner_image'] = $file_name;
                    }
                }
                if (WebBannerModel::insert($ins)) {

                    $status = "1";
                    $message = "Banner created";
                    $errors = '';
                } else {
                    $status = "0";
                    $message = "Something went wrong";
                    $errors = '';
                }
            }
            echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
        } else {
            $page_heading = "Create Web Banner";
            return view('admin.banner.create_web_banner', compact('page_heading'));
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
        $services   = Service::where(['active'=>1,'deleted'=>0,'activity_id'=>6])->paginate(1000);
        $services_4   = Service::where(['active'=>1,'deleted'=>0,'activity_id'=>4])->paginate(1000);
        $services_1   = Service::where(['active'=>1,'deleted'=>0,'activity_id'=>1])->paginate(1000);

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

        $activityTypes = ActivityType::with('account')->where(['deleted' => '0'])
            ->orderBy('sort_order', 'asc')->get();
        
        $banner = BannerModel::find($id);
        $category_ids = [$banner->category];
        $vendors = VendorModel::select('vendor_details.company_name as name','users.id as id')->where('activity_id','=',$banner->activity)->where(['role'=>'3','users.deleted'=>'0'])
        ->leftjoin('vendor_details','vendor_details.user_id','=','users.id')
        ->leftjoin('industry_types','industry_types.id','=','vendor_details.industry_type')->get();
        if ($banner) {
            $page_heading = "Edit App Banner";
            return view('admin.banner.edit', compact('page_heading','sub_category_list', 'vendors','banner','products','services','services_1','services_4','category_list','sub_category_list','category_ids','activityTypes','id'));
        } else {
            abort(404);
        }
    }
    public function edit_web_banner($id = '')
    {
        $banner = WebBannerModel::find($id);
        if ($banner) {
            $page_heading = "Edit Web Banner";
            return view('admin.banner.edit_web_banner', compact('page_heading', 'banner'));
        } else {
            abort(404);
        }
    }
    
    public function update(Request $request)
    {
        $status = "0";
        $message = "";
        $errors = '';
        $validator = Validator::make($request->all(),
            [
                // 'banner_title' => 'required',
                'banner' => 'image',
            ],
            [
                // 'banner_title.required' => 'Title required',
                'banner.image' => 'should be in image format (.jpg,.jpeg,.png)',
            ]
        );
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $ins['active'] = $request->active;
            $ins['banner_title'] = $request->banner_title;
            $ins['type'] = $request->applies_to??4;
            if($request->activity == 7){
            $ins['category'] = $request->category_id??0;
            }else{
                $ins['category'] = 0;
            }
            $ins['product'] = $request->txt_products??0;
            $ins['service'] = $request->txt_services??0;
            if($request->activity == 1){
                $ins['service'] = $request->txt_services1??0;
            }
            if($request->activity == 4){
                $ins['service'] = $request->txt_services4??0;
            }
            $ins['banner_type'] = $request->banner_type??0;
            $ins['activity'] = $request->activity??0;
            $ins['store'] = $request->store??0;
            if($request->activity == 4 || $request->activity == 1){
                $ins['store'] =  0;
            }
            $ins['url'] = $request->url;
            $ins['updated_at'] = gmdate('Y-m-d H:i:s');
            $ins['updated_by'] = session("user_id");
            if ($file = $request->file("banner")) {
                $dir = config('global.upload_path') . "/" . config('global.banner_image_upload_dir');
                $file_name = time() . $file->getClientOriginalName();
                $file->move($dir, $file_name);
                //$file->storeAs(config('global.banner_image_upload_dir'),$file_name,'s3');
                $ins['banner_image'] = $file_name;
            }
            if (BannerModel::where('id',$request->id)->update($ins)) {

                $status = "1";
                $message = "Banner updated";
                $errors = '';
            } else {
                $status = "0";
                $message = "Something went wrong";
                $errors = '';
            }
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
    }
    public function update_web_banner(Request $request)
    {
        $status = "0";
        $message = "";
        $errors = '';
        $validator = Validator::make($request->all(),
            [
                // 'banner_title' => 'required',
                'banner' => 'image',
            ],
            [
                // 'banner_title.required' => 'Title required',
                'banner.image' => 'should be in image format (.jpg,.jpeg,.png)',
            ]
        );
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $ins['active'] = $request->active;
            $ins['banner_title_1'] = $request->banner_title_1;
            $ins['banner_title_2'] = $request->banner_title_2;
            $ins['banner_title_3'] = $request->banner_title_3;
            $ins['banner_title_4'] = $request->banner_title_4;
            $ins['updated_on'] = gmdate('Y-m-d H:i:s');
            $ins['updated_by'] = session("user_id");
            if ($file = $request->file("banner")) {
                if(isset($request->cropped_upload_image) && $request->cropped_upload_image){
                    $image_parts = explode(";base64,", $request->cropped_upload_image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $imageName = uniqid() .time(). '.'.$image_type;
                    $path = \Storage::disk('s3')->put(config('global.banner_image_upload_dir').$imageName, $image_base64);
                    $path = \Storage::disk('s3')->url($path);
                    $ins['banner_image'] = $imageName;
                }else{
                    $dir = config('global.upload_path') . "/" . config('global.banner_image_upload_dir');
                    $file_name = time() . $file->getClientOriginalName();
                    //$file->move($dir, $file_name);
                    $file->storeAs(config('global.banner_image_upload_dir'),$file_name,'s3');
                    $ins['banner_image'] = $file_name;
                }
            }
            if (WebBannerModel::where('id',$request->id)->update($ins)) {

                $status = "1";
                $message = "Banner updated";
                $errors = '';
            } else {
                $status = "0";
                $message = "Something went wrong";
                $errors = '';
            }
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
    }
    public function delete($id = '')
    {
        BannerModel::where('id', $id)->delete();
        $status = "1";
        $message = "Banner removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function delete_web_banner($id = '')
    {
        WebBannerModel::where('id', $id)->delete();
        $status = "1";
        $message = "Banner removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function getstore($ctid)
    {
        $data= VendorModel::select('vendor_details.company_name as name','users.id as id')->where('activity_id','=',$ctid)->where(['role'=>'3','users.deleted'=>'0'])
        ->leftjoin('vendor_details','vendor_details.user_id','=','users.id')
        ->leftjoin('industry_types','industry_types.id','=','vendor_details.industry_type')->get()->toArray();
      
        if(count($data)==0)
        { $data ="0"; }
        echo  json_encode($data);
     }
     public function getproductbystore($ctid)
    {
        $where['deleted'] = 0;
        $where['product_status'] = 1;

        $filter['vendor_id']   = $ctid;

        $limit = '';
        $offset = '';
        
        $data = ProductModel::products_list($where, $filter, $limit, $offset)->get()->toArray();
      
        if(count($data)==0)
        { $data ="0"; }
        echo  json_encode($data);
     }

}
