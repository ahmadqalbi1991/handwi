<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupons;
use App\Models\AmountType;
use App\Models\Categories;
use App\Models\ProductModel;
use App\Models\CouponCategory;
use App\Models\CouponProducts;
use App\Models\CouponServices;
use App\Models\VendorservicesModel;
use App\Models\OrderServiceModel;
use App\Models\User;
use App\Models\CouponVendor;
use App\Models\Service;
use Illuminate\Http\Request;
use Validator;

class CouponsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_heading = "Promo Code";
        $datamain = Coupons::select('coupon.*','amount_type.*','coupon.id as id')->orderBy('coupon.id', 'DESC')
        ->leftjoin('amount_type','amount_type.id','=','coupon.amount_type')
        ->get();
        foreach ($datamain as $key => $value) {
             $datamain[$key]->coupon_used = 0;
             $datamain[$key]->coupon_used_users = 0; 
             if($value->applied_to == 3)
             {
             $coupon_used = OrderServiceModel::where('coupon_id',$value->id)->count();
             $coupon_used_user = OrderServiceModel::where(['coupon_id'=>$value->id])->count();
             $datamain[$key]->coupon_used = $coupon_used;
             $datamain[$key]->coupon_used_users = $coupon_used_user;
             }
             
        }
        return view('admin.coupons.list', compact('page_heading', 'datamain'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $couponproducts = []; 
        $category_ids = [];
        $vendors = [];
        $page_heading = "Promo Code";
        $mode = "create";
        $id = "";
        $prefix = "";
        $name = "";
        $dial_code = "";
        $image = "";
        $active = "1";
        $amounttype = AmountType::get();


        $filter = ['product.deleted' => 0];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $sortby = "product.id";
        $sort_order = "desc";
        $search_key = $params['search_key'];
        $products   = ProductModel::get_products_list($filter, $params, $sortby, $sort_order)->paginate(1000);
        $services   = Service::where(['active'=>1,'deleted'=>0])->paginate(1000);
        

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

        $dist_list = User::select('users.id','vendor_details.company_name as name','activity_id')->where('role', 3)
        ->leftjoin('vendor_details','vendor_details.user_id','=','users.id')
        ->where('users.deleted', 0)->get();
        $couponservices = [];
        
        return view("admin.coupons.create", compact('page_heading', 'mode', 'id', 'name', 'dial_code', 'active','prefix','amounttype','category_list','sub_category_list','category_ids','products','couponproducts','dist_list','services','couponservices','vendors'));
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
            'coupone_code'    => 'required',
            'coupone_amount'  => 'required',
            'amount_type'     => 'required',
            'expirydate'      => 'required',
            'startdate'       => 'required',
            'title'           => 'required',
            'description'     => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $input = $request->all();
            // $check_exist = Coupons::where(['coupon_code' => $request->coupone_code])->where('id', '!=', $request->id)->get()->toArray();
            if (true) {
                $ins = [
                    'coupon_code'      => $request->coupone_code,
                    'coupon_amount'    =>  $request->coupone_amount,
                    'amount_type'      =>  $request->amount_type,
                    'coupon_title'     => $request->title,
                    'coupon_description' => $request->description,
                    'coupon_status'    => $request->active,
                    'start_date'       => $request->startdate,
                    'coupon_end_date'  => $request->expirydate,
                    'applied_to'       => $request->applies_to,
                    'minimum_amount'   => $request->minimum_amount,
                    'coupon_vender_id' => 0,
                    'coupon_usage_percoupon'   => empty($request->coupon_usage_percoupon) ? 0: $request->coupon_usage_percoupon,
                    'coupon_usage_peruser'     => empty($request->coupon_usage_peruser) ? 0: $request->coupon_usage_peruser,
                ];
               
                $categories = $request->category_ids; 
                $products   = $request->txt_products;
                $services   = $request->txt_services;
                $vendors    = $request->distributor;
               
                if ($request->id != "") {
                    $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                    Coupons::where('id',$request->id)->update($ins);
                    CouponCategory::insertcategory($request->id,$categories);
                    CouponProducts::insertproducts($request->id,$products);
                    CouponServices::insertservices($request->id,$services);
                    CouponVendor::insertvendors($request->id,$vendors);
                    $status = "1";
                    $message = "Coupon updated succesfully";
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    Coupons::insert($ins);
                    $inid = Coupons::orderBy('id', 'desc')->get()->first();
                    CouponCategory::insertcategory($inid->id,$categories);
                    CouponProducts::insertproducts($inid->id,$products);
                    CouponServices::insertservices($inid->id,$services);
                    CouponVendor::insertvendors($inid->id,$vendors);
                    $status = "1";
                    $message = "Coupon added successfully";
                }
            } else {
                $status = "0";
                $message = "Coupon code should be unique";
                $errors['coupone_code'] = $request->coupone_code . " already added";
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
        $couponproducts = []; 
        $category_ids = [];
        $amounttype = AmountType::get();
        $datamain = Coupons::where('id',$id)->first();
        if ($datamain) {
            $page_heading = "Coupon";
            $mode = "edit";
            $prefix = "";
        $name = "";
        $dial_code = "";
        $image = "";
        $active = "1";

        $filter = ['product.deleted' => 0];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $sortby = "product.id";
        $sort_order = "desc";
        $search_key = $params['search_key'];
        $products   = ProductModel::get_products_list($filter, $params, $sortby, $sort_order)->paginate(1000);
        

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

        $product_categories = CouponCategory::where('coupon_id',$id)->get()->toArray();
        $category_ids       = array_column($product_categories,'category_id');

        $couponproducts = CouponProducts::where('coupon_id',$id)->get();
        $couponservices = CouponServices::where('coupon_id',$id)->pluck('service_id')->toArray();
        $vendors = CouponVendor::where('coupon_id',$id)->get();

        

        $services  = Service::where(['active'=>1,'deleted'=>0]);
        if(count($vendors) > 0)
        {
        $vendorservices = VendorservicesModel::whereIn('vendor_id',$vendors->pluck('vendor'))->pluck('service_id');
        $services = $services->whereIn('id',$vendorservices);  
        }
        $services = $services->paginate(1000);

        if(count($vendors) > 0)
          {
        foreach ($services as $key => $value) {
            $service_vendors_list = VendorservicesModel::where('service_id',$value->id)->leftjoin('vendor_details','vendor_details.user_id','=','vendor_services.vendor_id')->leftjoin('users','users.id','=','vendor_services.vendor_id')->where('users.deleted', 0)->whereIn('vendor_services.vendor_id',$vendors->pluck('vendor'))->pluck('vendor_details.company_name')->toArray();
             $services[$key]->companies = " - ".implode(", ",$service_vendors_list);
       
        }
        }

        $dist_list = User::select('users.id','vendor_details.company_name as name','activity_id')->where('role', 3)
        ->leftjoin('vendor_details','vendor_details.user_id','=','users.id')
        ->where('users.deleted', 0)->get();
        


        


        $id = "";
            return view("admin.coupons.create", compact('page_heading', 'datamain','id','amounttype','category_list','sub_category_list','category_ids','products','couponproducts','couponservices','dist_list','services','vendors'));
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
    public function couponproductsearch(Request $request)
    {
        $data_arry = [];
        $filter = ['product.deleted' => 0];
        $params = [];
        $params['search_key'] = $_GET['search_text'] ?? '';
        $params['vendor'] = $_GET['distributor'] ?? '';
        $sortby = "product.id";
        $sort_order = "desc";
        $search_key  = $params['search_key'];
        $distributor = $params['vendor'];
        $products   = ProductModel::get_products_list($filter, $params, $sortby, $sort_order)->paginate(10);
        foreach($products as $prd)
        {
            $data_arry[] = array(
               'id'           => $prd->id,
               'text'         => $prd->product_name,
             );
        }
        echo json_encode($data_arry);
    }
    public function couponservicesearch(Request $request)
    {
        $data_arry = [];
        $filter = ['service.deleted' => 0];
        $filter = ['service.active' => 1];
        $params = [];
        $params['search_key'] = $request->search_text??'';
        
        if(!empty($request->distributor))
        {
        $vendors =  array_diff($request->distributor, [0]);   
        }
        
        if(!empty($vendors))
        {
         $vendorservices = VendorservicesModel::whereIn('vendor_id',$vendors)->pluck('service_id');
        }
        
        //$params['product_vender_id'] = $_GET['distributor'] ?? '';
        $sortby = "product.id";
        $sort_order = "desc";
        $search_key  = $params['search_key'];
        $service     = Service::where($filter);
        if(!empty($params['search_key']))
        {
         $srch = $params['search_key'];
         $service= $service->where('name', 'ilike', '%' . $srch . '%');   
        }
        if(!empty($vendorservices))
        {
         $service = $service->whereIn('id',$vendorservices);  
        }
        $service= $service->paginate(10000);
        foreach($service as $prd)
        {
            $service_vendors_list = VendorservicesModel::where('service_id',$prd->id)->leftjoin('vendor_details','vendor_details.user_id','=','vendor_services.vendor_id')->leftjoin('users','users.id','=','vendor_services.vendor_id')->where('users.deleted', 0)->whereIn('vendor_services.vendor_id',$vendors)->pluck('vendor_details.company_name')->toArray();

            $data_arry[] = array(
               'id'           => $prd->id,
               'text'         => $prd->name." - ".implode(", ",$service_vendors_list),
             );
        }
        echo json_encode($data_arry);
    }
    public function delete($id) 
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $datamain = Coupons::where('id',$id)->first();
        if ($datamain) {
            Coupons::where('id',$id)->delete();
            $status = "1";
            $message = "Coupon removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }
    public function couponcategoryActivity(Request $request)
    {
        $activity = $request->activity;

        $category = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->whereNotIn('id',[8]);
        if(!empty($activity))
        {
            $category = $category->where('activity_id',$activity); 
        }
        $category = $category->get();
        foreach($category as $prd)
        {
            $data_arry[] = array(
               'id'           => $prd->id,
               'text'         => $prd->name,
             );
        }
        echo json_encode($data_arry);
    }

    public function servicebyvendor(Request $request)
    {
        $data_arry = [];
        $filter = ['service.deleted' => 0];
        $filter = ['service.active' => 1];
        $params = [];
        $params['search_key'] = $request->search_text??'';
        
        if(!empty($request->distributor))
        {
        $vendors =  array_diff([$request->distributor], [0]);   
        }

       
        
        if(!empty($vendors))
        {
         $vendorservices = VendorservicesModel::whereIn('vendor_id',$vendors)->pluck('service_id');
        }
        
        //$params['product_vender_id'] = $_GET['distributor'] ?? '';
        $sortby = "product.id";
        $sort_order = "desc";
        $search_key  = $params['search_key'];
        $service     = Service::where($filter);
        if(!empty($params['search_key']))
        {
         $srch = $params['search_key'];
         $service= $service->where('name', 'ilike', '%' . $srch . '%');   
        }
        if(!empty($vendorservices))
        {
         $service = $service->whereIn('id',$vendorservices);  
        }
        $service= $service->paginate(10000);
        foreach($service as $prd)
        {
            $service_vendors_list = VendorservicesModel::where('service_id',$prd->id)->leftjoin('vendor_details','vendor_details.user_id','=','vendor_services.vendor_id')->leftjoin('users','users.id','=','vendor_services.vendor_id')->where('users.deleted', 0)->whereIn('vendor_services.vendor_id',$vendors)->pluck('vendor_details.company_name')->toArray();

            $data_arry[] = array(
               'id'           => $prd->id,
               'text'         => $prd->name." - ".implode(", ",$service_vendors_list),
             );
        }
        echo json_encode($data_arry);
    }
}
