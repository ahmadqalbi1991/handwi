<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServiceCategories;
use App\Models\Service;
use App\Models\Categories;
use App\Models\BuildingTypes;
use App\Models\ProductModel;
use App\Models\VendorServiceTimings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use App\Models\States;
use App\Models\HourlyRate;
use App\Models\Cities;
use App\Models\ServiceTimings;
use App\Models\ActivityType;
use App\Models\AccountType;
use App\Models\ServiceType;
use App\Models\ServiceCategorySelected;
use Illuminate\Support\Facades\Mail;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!GetUserPermissions('services_view')){
            return redirect()->to('admin/dashboard');
        }
        $page_heading = "Services";
        $categories = Service::where(['deleted' => 0])->orderBy('id','desc')->get();

        foreach ($categories as $key => $cat) {

            $categories[$key]->categories_selected = DB::table('service_category_selected')
                ->join('service_category', 'service_category.id', '=', 'service_category_selected.category_id')
                ->where('service_id', '=', $cat->id)->get();
        }



        return view('admin.service.list', compact('page_heading', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        $page_heading = "Services";
        $mode = "create";
        $id = "";
        $name = "";
        $activity_id = "";
        $parent_id = "";
        $image = "";
        $active = "1";
        $banner_image = "";
        $category = [];
        $description = "";
        $category       = '';
        $serviceprice       = "";
        $duration       = "";
        $building_type  = "";
        $servic_prcies = [];
        $service_price_type = '';
        $price_label  = "";

        $category_ids = [];

        $parent_categories_list = $parent_categories = ServiceCategories::where(['deleted' => 0, 'active' => 1, 'parent_id' => 0])->get()->toArray();
        $parent_categories_list = ServiceCategories::where(['deleted' => 0, 'active' => 1])->where('parent_id', '!=', 0)->get()->toArray();

        $parent_categories = array_column($parent_categories, 'name', 'id');
        asort($parent_categories);
        $category_list = $parent_categories;

        $sub_categories = [];
        foreach ($parent_categories_list as $row) {
            $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
        }
        $sub_category_list = $sub_categories;
        $states = States::where(['deleted' => 0, 'country_id' => 1])->get();
        $cities = [];
        $buildingTypes = BuildingTypes::where(['deleted' => 0])->get();

        $activity_types = ActivityType::select('id','name as activity_name')->where(['deleted' => 0, 'account_id' => AccountType::COMMERCIAL_CENTER])->whereIn('id',[4,1,6])->get();
            $service_types = ServiceType::select('id','name')->where(['deleted' => 0])->get();


        return view("admin.service.create", compact('page_heading', 'category', 'price_label','mode', 'id', 'name', 'parent_id', 'image', 'active', 'banner_image', 'description', 'category', 'category_list', 'serviceprice', 'duration', 'parent_categories_list', 'sub_categories', 'sub_category_list', 'category_ids', 'states', 'cities', 'servic_prcies', 'buildingTypes', 'building_type','service_price_type','activity_id','activity_types','service_types'));
    }
    public function get_service_categories_by_activity_id(Request $request){

        $activity_id = $request->activity_id ;
        $category_list = [];
        $cat_view = '';
        $brand_view = '';
        if($activity_id){
            $parent_categories_list = $parent_categories = ServiceCategories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->where('activity_id',$activity_id)->get()->toArray();
            $parent_categories_list = ServiceCategories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->where('activity_id',$activity_id)->get()->toArray();

            $parent_categories = array_column($parent_categories, 'name', 'id');
            asort($parent_categories);
            $category_list = $parent_categories;
            $category_ids = [];

            $sub_categories = [];
            foreach ($parent_categories_list as $row) {
                $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
            }
            $sub_category_list = $sub_categories;
            $id = '';

            $cat_view = view('admin.product.categories',compact('category_list','category_ids','sub_category_list','id'))->render();

        }
        return  json_encode(['status' => '1','cat_view'=>$cat_view, 'message' => '']);

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
        } else {
            $input = $request->all();
            $check_exist = Service::where(['deleted' => 0, 'name' => $request->name])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name'            => $request->name,
                    'updated_at'      => gmdate('Y-m-d H:i:s'),
                    'service_price'   => $request->price ?? 0,
                    'description'     => $request->description,
                    'duration'        => $request->duration,
                    'building_type_id' => $request->buildingType,
                    'active'          => $request->active,
                    'service_price_type'=>$request->service_price_type,
                    'price_label'=>$request->price_lable,
                    'activity_id'=>$request->activity_id
                ];

                if ($request->file("image")) {
                    $response = image_save($request, config('global.service_image_upload_dir'), 'image', '');
                    if ($response['status']) {
                        $ins['image'] = $response['link'];
                    }
                }

                if ($request->id != "") {
                    $category = Service::find($request->id);
                    $category->update($ins);
                    $inid = $request->id;
                    $status = "1";
                    $message = "Services updated succesfully";
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    $service =  Service::create($ins);
                    $inid = $service->id;
                    $status = "1";
                    $message = "Services added successfully";
                }

                $categories = $request->category_ids;


                if (!empty($categories)) {
                    $all_categories = ServiceCategories::getCategoriesCondition($categories);
                    $all_categories = array_column($all_categories, 'service_category_parent_id', 'service_category_id');
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

                if (!empty($categories)) {
                    DB::table('service_category_selected')->where('service_id', '=', $inid)->delete();
                    foreach ($categories as $cat) {
                        DB::table('service_category_selected')->insert(['category_id' => $cat, 'service_id' => $inid]);
                    }
                }

                $text = $request->text;
                $hourly_rate = $request->hourly_rate;
                HourlyRate::where('service_id',$inid)->delete();
                if(!empty($hourly_rate))
                {
                    foreach($text as $key => $value) {
                        $serviceprice = new HourlyRate();
                        $serviceprice->service_id    = $inid;
                        $serviceprice->text         = $value;
                        $serviceprice->hourly_rate  = $hourly_rate[$key];
                        $serviceprice->save();
                    }
                }
                

            } else {
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
    public function edit($id)
    {
        //
        $category = Service::find($id);
       
        $product_categories = ServiceCategories::get_service_categories($id);

        $category_ids       = array_column($product_categories, 'category_id');
        $states = States::where(['deleted' => 0, 'country_id' => 1])->get();
        $cities = [];
       
        if ($category) {
            $page_heading   = "Services";
            $mode           = "edit";
            $serviceprice   = $category->service_price;
            $id             = $category->id;
            $name           = $category->name;
            $parent_id      = $category->parent_id;
            $image          = $category->image;
            $active         = $category->active;
            $description    = $category->description;
            $duration       = $category->duration;
            $building_type  = $category->building_type_id;
            $service_price_type = $category->service_price_type;
            $activity_id       = $category->activity_id;
            $price_label    = $category->price_label;
            $category       = $category->category;
            
            $activity_types = ActivityType::select('id','name as activity_name')->where(['deleted' => 0, 'account_id' => AccountType::COMMERCIAL_CENTER])->whereIn('id',[4,1,6])->get();
            $service_types = ServiceType::select('id','name')->where(['deleted' => 0])->get();


            $banner_image   = "";

            $parent_categories_list = $parent_categories = ServiceCategories::where(['deleted' => 0, 'active' => 1, 'parent_id' => 0])->whereIn('activity_id',[$activity_id])->get()->toArray();
            $parent_categories_list = ServiceCategories::where(['deleted' => 0, 'active' => 1])->whereIn('activity_id',[$activity_id])->where('parent_id', '!=', 0)->get()->toArray();

            $parent_categories = array_column($parent_categories, 'name', 'id');
            asort($parent_categories);
            $category_list = $parent_categories;

            $sub_categories = [];
            foreach ($parent_categories_list as $row) {
                $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
            }
            $sub_category_list = $sub_categories;
            $buildingTypes = BuildingTypes::where(['deleted' => 0])->get();

            $servic_prcies = HourlyRate::where('service_id',$id)->get();
            

            return view("admin.service.create", compact('page_heading', 'category', 'price_label',
            'mode', 'id', 'name', 'parent_id', 'image', 'active',  'banner_image',
             'description', 'category', 'serviceprice', 'parent_categories_list', 'sub_category_list',
              'category_list', 'category_ids', 'states', 'cities',  'buildingTypes', 'building_type', 'duration','servic_prcies','service_price_type','activity_id','activity_types','service_types'));
        } else {
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
        $category = Service::find($id);
        if ($category) {
            $category->deleted = 1;
            $category->active = 0;
            $category->updated_at = gmdate('Y-m-d H:i:s');
            $category->save();
            $status = "1";
            $message = "Services removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (Service::where('id', $request->id)->update(['active' => $request->status])) {
            $status = "1";
            $msg = "Successfully activated";
            if (!$request->status) {
                $msg = "Successfully deactivated";
            }
            $message = $msg;
            // $user_email = DB::select('select email from service s 
            // left join users u on s.service_user_id = u.id where order_id =' . $request->id);

            // Mail::to(['address' => $user_email[0]->email])
            //     ->send(new \App\Mail\OrderStatusChange($request->id, $request->status));
        } else {
            $message = "Something went wrong";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function sort(Request $request)
    {
        if ($request->ajax()) {
            $status = 0;
            $message = '';

            $items = $request->items;
            $items = explode(",", $items);
            $sorted = Service::sort_item($items);
            if ($sorted) {
                $status = 1;
            }

            echo json_encode(['status' => $status, 'message' => $message]);
        } else {
            $page_heading = "Sort Categories";

            $list = Service::where(['deleted' => 0])->orderBy('sort_order','asc')->get();

            foreach ($list as $key => $value) {
                $category_id = ServiceCategorySelected::where('service_id',$value->id)->first()->category_id??'';
                $categoryname = ServiceCategories::find($category_id);
                $list[$key]->name = $value->name.' - '.$categoryname->name;
            }

            return view("admin.sort", compact('page_heading', 'list'));
        }
    }
    public function timeslote()
    {
        $page_heading = "Services";
        $mode = "create";
        $id = "";
        $name = "";
        $parent_id = "";
        $image = "";
        $active = "1";
        $banner_image = "";
        $category = [];
        $description = "";
        $category       = '';
        $serviceprice       = "";
        $duration       = "";
        $building_type  = "";
        $servic_prcies = [];

        $category_ids = [];

        $parent_categories_list = $parent_categories = ServiceCategories::where(['deleted' => 0, 'active' => 1, 'parent_id' => 0])->get()->toArray();
        $parent_categories_list = ServiceCategories::where(['deleted' => 0, 'active' => 1])->where('parent_id', '!=', 0)->get()->toArray();

        $parent_categories = array_column($parent_categories, 'name', 'id');
        asort($parent_categories);
        $category_list = $parent_categories;

        $sub_categories = [];
        foreach ($parent_categories_list as $row) {
            $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
        }
        $sub_category_list = $sub_categories;
        $states = States::where(['deleted' => 0, 'country_id' => 1])->get();
        $cities = [];
        $buildingTypes = BuildingTypes::where(['deleted' => 0])->get();
        $datamain = VendorServiceTimings::get()->first();
        $datamain->gr_availablity =VendorServiceTimings::where(['service_id'=>2,'vendor'=>1])->first();
        $vendor_timings = ServiceTimings::where(['vendor_id'=>1])->get();
            $days = Config('global.days');
            $selected_days = [];
            foreach($days as $k=>$val){
                $in=[
                    'day'       =>  $val
                ];
                $in['open_24'] = "0";
                $from_hours = [];
                $to_hours = [];
                foreach($vendor_timings as $timing){
                    if($timing->day == $val){
                        $in['open_24'] = $timing->has_24_hour;
                        
                        $from_hours[] = $timing->time_from;
                        $to_hours[] = $timing->time_to;
                    }
                }
                
                $in['from_hours'] = $from_hours;
                $in['to_hours'] = $to_hours;
                
                $selected_days[$val] = $in;
            }

        

        return view("admin.service.time_slote", compact('page_heading', 'selected_days','datamain','category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'banner_image', 'description', 'category', 'category_list', 'serviceprice', 'duration', 'parent_categories_list', 'sub_categories', 'sub_category_list', 'category_ids', 'states', 'cities', 'servic_prcies', 'buildingTypes', 'building_type'));
    }
    public function save_time_slote(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            //'first_name' => 'required',
        ]);
       
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            ServiceTimings::where('vendor_id',1)->delete();
                 $days = Config('global.days') ;
                    $gr_availability =[];
                    foreach($days as $key =>$val){
                        $vet_val = $val.'_grooming';
                        if(isset($request->{$vet_val})) {
                            $to_data = $request->{$key.'_to_grooming'};
                            $has_24=$request->{$val.'_24'}??0;
                            if($has_24 != 1){
                           
                                foreach($request->{$key.'_from_grooming'} as $in=>$dd){
                                    $insert_data = [];
                                    $insert_data['vendor_id'] = 1;
                                    $insert_data['service_id'] = 2;
                                    $insert_data['day'] = $val;
                                    $insert_data['has_24_hour'] = $request->{$val.'_24'}??0;
                                    $insert_data['time_from'] = $dd;
                                    $insert_data['time_to'] = $to_data[$in];
                                    $insert_data['created_at'] = gmdate('Y-m-d H:i:s');
                                    $insert_data['updated_at'] = gmdate('Y-m-d H:i:s');
                                    $gr_availability[] = $insert_data;
                                    ServiceTimings::create($insert_data);
                                }
                            }else{
                                $insert_data = [];
                                $insert_data['vendor_id'] = 1;
                                $insert_data['service_id'] = 2;
                                $insert_data['day'] = $val;
                                $insert_data['has_24_hour'] = 1;
                                $insert_data['time_from'] = "00:00 AM";
                                $insert_data['time_to'] = "12:59 PM";
                                $insert_data['created_at'] = gmdate('Y-m-d H:i:s');
                                $insert_data['updated_at'] = gmdate('Y-m-d H:i:s');
                                $gr_availability[] = $insert_data;
                                ServiceTimings::create($insert_data);
                            }
                            
                        } else {
                            // $gr_availability[][$val] = 0;
                            // $gr_availability[]['time_from'] = '00:00';
                            // $gr_availability[]['time_to'] = '00:00';
                            // $gr_availability[]['created_at'] = gmdate('Y-m-d H:i:s');
                            // $gr_availability[]['updated_at'] = gmdate('Y-m-d H:i:s');
                        }
                    }
                    //$datamain = VendorServiceTimings::where('vendor',1)->first();
                    
           
                    $status = "1";
                    $message ="Service time updated successfully!";
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }
    public function save_time_slote_old(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            //'first_name' => 'required',
        ]);
       
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            VendorServiceTimings::where('vendor',1)->delete();
                 $days = Config('global.days') ;
                    $gr_availability['vendor'] = 1;
                    $gr_availability['service_id'] = 2;
                    foreach($days as $key =>$val){
                        $vet_val = $val.'_grooming';
                        if(isset($request->{$vet_val})) {
                            $gr_availability[$val] = $request->{$vet_val} ;
                            $gr_availability[$key.'_from'] = $request->{$key.'_from_grooming'} ;
                            $gr_availability[$key.'_to'] = $request->{$key.'_to_grooming'} ;
                        } else {
                            $gr_availability[$val] = 0;
                            $gr_availability[$key.'_from'] = '00:00';
                            $gr_availability[$key.'_to'] = '00:00';
                        }
                    }
                    VendorServiceTimings::create($gr_availability);
                    //$datamain = VendorServiceTimings::where('vendor',1)->first();
                    
           
                    $status = "1";
                    $message ="Service time updated successfully!";
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }
    
}