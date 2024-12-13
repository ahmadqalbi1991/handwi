<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorModel;
use App\Models\VendorDetailsModel;
use App\Models\BankdataModel;
use App\Models\CountryModel;
use App\Models\IndustryTypes;
use App\Models\States;
use App\Models\Cities;
use App\Models\BankModel;
use App\Models\BankCodetypes;
use App\Models\StoreType;
use Illuminate\Http\Request;
use App\Models\ActivityType;
use App\Models\AccountType;
use App\Models\VendorServiceTimings;
use App\Models\VendorMenuImages;
use App\Models\VendorCuisines;
use App\Models\Cuisine;
use App\Models\ProductModel;
use App\Models\VendorTimings;
use App\Models\VendorLocation;
use Validator;
use Illuminate\Support\Facades\Auth;
use Hash;
use DB;

class VendorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!GetUserPermissions('vendor_view')){
            return redirect()->to('admin/dashboard');
        }
        
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $users_ty = isset($_GET['users']) ? $_GET['users'] : '';
        $page_heading = "Vendors";
        $datamain = VendorModel::with('activity')->select('*','users.name as name','industry_types.name as industry','users.activity_id','users.active as active','users.id as id','users.created_at as created_at')
        ->where(['role'=>'3']);
        if(request()->deleted){
            $datamain->where(['users.deleted'=>'1']);
        }else{
            $datamain->where(['users.deleted'=>'0']);
        }
        //->with('vendordata')
        $datamain->leftjoin('vendor_details','vendor_details.user_id','=','users.id')
        ->leftjoin('industry_types','industry_types.id','=','vendor_details.industry_type');
        if(!empty($type))
        {
           if($type == 1)
           {
           $page_heading = "Approved Medicine Vendor";
           }
           if($type == 2)
           {
           $page_heading = "Approved Service provider";
           }
           $datamain = $datamain->where('store_type',$type)->where('verified','1');

        }else{
            //$datamain = $datamain->where('verified', '0');
        }
        if($users_ty == "new")
        {
            $datamain = $datamain->where('admin_viewed',0);
        }

        $datamain = $datamain->orderBy('users.id','desc')->get();


        $category_with_services = DB::table('service_category_selected as scs')
            ->join('service_category as sc', 'sc.id', '=', 'scs.category_id')
            ->join('service as s', 's.id', '=', 'scs.service_id')
            ->select("sc.id as category_id", "sc.name as category_name", "s.id as service_id", "s.name as service_name")
            ->where('sc.active', 1)
            ->get();

        $aService_data = [];
        if ( isset ( $category_with_services ) && count ( $category_with_services ) > 0 ) {
            foreach ( $category_with_services as $index => $service ) {
                $aService_data[$service->category_id.'_'.$service->category_name][] = array
                (
                    "service_id" => $service->service_id,
                    "service_name" => $service->service_name
                );
            }
        }
        
        
       
        if(request()->test){
            // $del_vendors = VendorModel::where('deleted',1)->get();
            // if($del_vendors->count()){
            //     foreach($del_vendors as $row){
            //         $row->email = $row->email."_deleted_mail";
            //         $row->phone = $row->phone."_deleted_mobile";
            //         $row->save();
            //     }
            // }
            // $del_vendors = VendorModel::where('deleted',1)->get();
            // dd($del_vendors);
        // if ($datatb) {
           
        //     $datatb->deleted = 1;
        //     $datatb->active = 0;
        }
        /*echo "<pre>";
            print_r($datamain);
        exit(" |_| ");*/

        return view('admin.vendor.list', compact('page_heading', 'datamain','type', 'aService_data','users_ty'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Vendors";
        $mode = "create";
        $id = "";
        $prefix = "";
        $name = "";
        $dial_code = "";
        $image = "";
        $active = "1";
        $activity_id = "";
        $states = [];
        $cities = [];
        $cities = Cities::where(['deleted' => 0])->get();
        $countries  = CountryModel::where(['deleted' => 0])->orderBy('name','asc')->get();
        $industry   = IndustryTypes::where(['deleted' => 0])->get();

        $datamain = new VendorModel;
        $Cuisines = Cuisine::where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->get();
        $Cuisines_ids = [];

        // $storetype  = VendorModel::VendorTypes();
        $storetype  = 2;

        $banks      = BankModel::get();
        $banks_codes = BankCodetypes::get();

         $activity_types = ActivityType::select('id','name as activity_name')->where(['deleted' => 0, 'account_id' => AccountType::COMMERCIAL_CENTER])->whereNotIn('id',[2])->get();

        return view("admin.vendor.create", compact('page_heading', 'industry', 'id', 'name', 'dial_code', 'active','prefix','countries','states','cities','banks','banks_codes','storetype','activity_types','activity_id','datamain','Cuisines','Cuisines_ids'));
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
            'first_name' => 'required',
        ]);
        if(!empty($request->password))
        {
             $validator = Validator::make($request->all(), [
            'confirm_password' => 'required',
             ]);
        }
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $input = $request->all();
            $lat = "";
            $long = "";
            if ($request->location) {
                $location = explode(",", $request->location);
                $lat = $location[0];
                $long = $location[1];
            }
            $check_exist = VendorModel::where('email' ,$request->email)->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $check_exist_phone = VendorModel::where('phone', $request->phone)->where('id', '!=', $request->id)->get()->toArray();
                if(empty($check_exist_phone))
                {

                    $ins = [
                    'country_id' => $request->country_id,
                    'name'       => $request->name,
                    'email'      => $request->email,
                    'dial_code'  => $request->dial_code,
                    'phone'      => $request->phone,
                    'role'       => '3',//vendor
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'state_id'   => $request->state_id ?? 0,
                    'city_id'    => $request->city_id,
                    'minimum_order_amount' => $request->minimum_amount,
                    'is_dinein' => $request->is_dinein ?? 0,
                    'is_delivery' => $request->is_delivery ?? 0,
                    'delivery_charge' => $request->delivery_charge ?? 0,
                ];



                if($request->password){
                        $ins['password'] = bcrypt($request->password);
                }
                if($request->activity_id){
                    $ins['activity_id'] = $request->activity_id;
                }

                if($request->file("image")){
                    $response = image_upload($request,'company','image');
                    if($response['status']){
                        $ins['user_image'] = $response['link'];
                    }
                }


                if ($request->id != "") {
                    $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                    $user = VendorModel::find($request->id);
                    $user->update($ins);

                    $vendordata = VendorDetailsModel::where('user_id',$request->id)->first();
                    $bank       = BankdataModel::where('user_id',$request->id)->first();
                    if(empty($vendordata->id))
                    {
                    $vendordatils = new VendorDetailsModel();
                    $vendordatils->user_id = $request->id;
                    }
                    else
                    {
                     $vendordatils = VendorDetailsModel::find($vendordata->id);
                    }

                    if(empty($bank->id))
                    {
                    $bankdata = new BankdataModel();
                    $bankdata->user_id = $request->id;
                    }
                    else
                    {
                     $bankdata = BankdataModel::find($bank->id);
                    }



                    $status = "1";
                    $message = "Vendor updated succesfully";
                        $loc_check = VendorLocation::where(['user_id'=>$request->id,'is_default'=>1])->get()->first();
                        if($loc_check){
                            $locationins = VendorLocation::find($loc_check->id);
                           $locationins->location = $request->txt_location;
                           $locationins->latitude = $lat;
                           $locationins->longitude = $long;
                           $locationins->updated_at = gmdate('Y-m-d H:i:s');
                           $locationins->save();
                        }else{
                            $locationins = new VendorLocation;
                           $locationins->user_id = $request->id;
                           $locationins->location = $request->txt_location;
                           $locationins->latitude = $lat;
                           $locationins->longitude = $long;
                           $locationins->created_at = gmdate('Y-m-d H:i:s');
                           $locationins->updated_at = gmdate('Y-m-d H:i:s');
                           $locationins->is_default = 1;
                           $locationins->save();
                        }
                    
                    } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    $userid = VendorModel::create($ins)->id;


                    $vendordatils = new VendorDetailsModel();
                    $vendordatils->user_id = $userid;

                    $bankdata = new BankdataModel();
                    $bankdata->user_id = $userid;

                    $status = "1";
                    $message = "Vendor added successfully";
                    //add location 

                       $locationins = new VendorLocation;
                       $locationins->user_id = $userid;
                       $locationins->location = $request->txt_location;
                       $locationins->latitude = $lat;
                       $locationins->longitude = $long;
                       $locationins->created_at = gmdate('Y-m-d H:i:s');
                       $locationins->updated_at = gmdate('Y-m-d H:i:s');
                       $locationins->is_default = 1;
                       $locationins->save();
                }

                //pharmacy or product
                    $industrytype = 0;

                    if(!empty($request->type))
                    {
                        $typecount = count($request->type);
                        if($typecount == 2)
                        {
                           $industrytype = 3;//pharmacy and service
                        }
                        else
                        {
                            foreach($request->type as $typeval)
                            {
                                if($typeval == 1)
                                {
                                    $industrytype = 1;
                                }
                                else
                                {
                                    $industrytype = 2;
                                }
                            }
                        }
                    }
                    //pharmacy or product END

                 $vendordatils->industry_type      = $industrytype;
                 $vendordatils->company_name       = $request->company_legal_name;
                 $vendordatils->company_brand      = $request->company_brand_name;
                 $vendordatils->reg_date           = $request->business_registration_date;
                 $vendordatils->trade_license           = $request->trade_licene_number;
                 $vendordatils->trade_license_expiry    = $request->trade_licene_expiry;
                 $vendordatils->vat_reg_number      = $request->vat_registration_number;
                 $vendordatils->vat_reg_expiry      = $request->vat_expiry_date;
                 $vendordatils->store_type    = 0;
                 $vendordatils->address1      = $request->address1;
                 $vendordatils->address2      = $request->address2;
                 $vendordatils->street        = $request->street;
                 $vendordatils->state         = $request->state_id ??0;
                 $vendordatils->city          = $request->city_id;
                 $vendordatils->zip           = $request->zip;
                 $vendordatils->txt_location  = $request->txt_location;
                 $vendordatils->location      = $request->txt_location;
                 $vendordatils->latitude      = $lat;
                 $vendordatils->longitude     = $long;
                 $vendordatils->pharmacycommission      = $request->pharmacycommission;
                 $vendordatils->servicecommission       = $request->servicecommission;
                 $vendordatils->featured_flag           = $request->featured??0;
                 $vendordatils->open_time        = $request->open_time;
                 $vendordatils->close_time       = $request->close_time;
                 $vendordatils->description      = $request->description;


                 //logo
                 if($request->file("logo")){
                    $response = image_upload($request,'company','logo');
                    if($response['status']){
                        $vendordatils->logo = $response['link'];
                    }
                 }

                 if($request->is_dinein && $request->file("menus")){
                    foreach ($request->menus as $key => $menu) {
                        $response = file_save($menu,'company');
                        if($response['status']){
                            $menu_image = new \App\Models\VendorMenuImages();
                            $menu_image->vendor_id = $vendordatils->user_id;
                            $menu_image->image = $response['link'];
                            $menu_image->save();
                        }
                    }
                 }
                 VendorCuisines::where('vendor_id',$vendordatils->user_id)->delete();
                 if($request->is_dinein && $request->cuisines && count($request->cuisines)){
                    foreach ($request->cuisines as $key => $row) {
                        $Cuisine = new VendorCuisines();
                        $Cuisine->vendor_id = $vendordatils->user_id;
                        $Cuisine->cuisine_id = $row;
                        $Cuisine->save();
                    }
                 }
                 
                 //logo end

                 //cover image
                 if($request->file("cover_image")){
                    $response = image_upload($request,'company','cover_image');
                    if($response['status']){
                 $vendordatils->cover_image = $response['link'];
                    }
                 }
                 //cover image end


                 if($request->file("trade_licence")){
                    $response = image_upload($request,'company','trade_licence');
                    if($response['status']){
                 $vendordatils->trade_license_doc = $response['link'];
                    }
                 }

                 $vendordatils->save();

                 $bankdata->bank_name       = $request->bank_id;
                 $bankdata->country         = $request->bankcountry;
                 $bankdata->company_account = $request->company_account;
                 $bankdata->account_no      = $request->bank_account_number;
                 $bankdata->code_type       = $request->bank_code_type;
                 $bankdata->branch_code     = $request->bank_branch_code;
                 $bankdata->branch_name     = $request->branch_name;
                 $bankdata->iban            = $request->iban;

                 if($request->file("bank_statement")){
                    $response = image_upload($request,'company','bank_statement');
                    if($response['status']){
                 $bankdata->bank_statement_doc     = $response['link'];
                    }
                 }

                 if($request->file("credit_card_statement")){
                    $response = image_upload($request,'company','credit_card_statement');
                    if($response['status']){
                 $bankdata->credit_card_sta_doc     = $response['link'];
                    }
                 }
                 $bankdata->save();
                 
                //  VendorServiceTimings::where('vendor',$request->id)->delete();
                //  $days = Config('global.days') ;
                //     $gr_availability['vendor'] = $request->id??$userid;
                //     $gr_availability['service_id'] = 2;
                //     foreach($days as $key =>$val){
                //         $vet_val = $val.'_grooming';
                //         if(isset($request->{$vet_val})) {
                //             $gr_availability[$val] = $request->{$vet_val} ;
                //             $gr_availability[$key.'_from'] = $request->{$key.'_from_grooming'} ;
                //             $gr_availability[$key.'_to'] = $request->{$key.'_to_grooming'} ;
                //         } else {
                //             $gr_availability[$val] = 0;
                //             $gr_availability[$key.'_from'] = '00:00';
                //             $gr_availability[$key.'_to'] = '00:00';
                //         }
                //     }
                //     VendorServiceTimings::create($gr_availability);

                VendorTimings::where('vendor_id',$request->id)->delete();
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
                                    $insert_data['vendor_id'] = $request->id??$userid;
                                    $insert_data['service_id'] = 2;
                                    $insert_data['day'] = $val;
                                    $insert_data['has_24_hour'] = $request->{$val.'_24'}??0;
                                    $insert_data['time_from'] = $dd;
                                    $insert_data['time_to'] = $to_data[$in];
                                    $insert_data['created_at'] = gmdate('Y-m-d H:i:s');
                                    $insert_data['updated_at'] = gmdate('Y-m-d H:i:s');
                                    $gr_availability[] = $insert_data;
                                    VendorTimings::create($insert_data);
                                }
                            }else{
                                $insert_data = [];
                                $insert_data['vendor_id'] = $request->id??$userid;
                                $insert_data['service_id'] = 2;
                                $insert_data['day'] = $val;
                                $insert_data['has_24_hour'] = 1;
                                $insert_data['time_from'] = "00:00 AM";
                                $insert_data['time_to'] = "12:59 PM";
                                $insert_data['created_at'] = gmdate('Y-m-d H:i:s');
                                $insert_data['updated_at'] = gmdate('Y-m-d H:i:s');
                                $gr_availability[] = $insert_data;
                                VendorTimings::create($insert_data);
                            }
                            
                        } else {
                            // $gr_availability[][$val] = 0;
                            // $gr_availability[]['time_from'] = '00:00';
                            // $gr_availability[]['time_to'] = '00:00';
                            // $gr_availability[]['created_at'] = gmdate('Y-m-d H:i:s');
                            // $gr_availability[]['updated_at'] = gmdate('Y-m-d H:i:s');
                        }
                    }
                    // printr($gr_availability);
                    // VendorTimings::create($gr_availability);


                }
                else
                {
                    $status = "0";
                    $message = "Phone number should be unique";
                    $errors['phone'] = "Already exist";
                }

            } else {
                $status = "0";
                $message = "Email should be unique";
                $errors['email'] = $request->email . " already added";
            }

        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }

    public function removevendorMenuImage(Request $request)
    {
        $status = 0;
        $message = "";
        $id = (int)$request->id;
        $image = (string)$request->image;
        if($id !="" && $image !="" ) {
            $image = VendorMenuImages::find($id);  
            if($image) {
                $image->delete();
                $status = 1;
                $message = "Deleted ";
            }
        }
        echo json_encode(compact('status', 'message'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $page_heading = "View Vendor";
        $datamain = VendorModel::with('menu_images')->find($id);
        $datamain->vendordatils = VendorDetailsModel::where('user_id',$id)->first();
        $datamain->bankdetails = BankdataModel::where('user_id',$id)->first();

        $countries = CountryModel::orderBy('name','asc')->get();
        $industry = IndustryTypes::where(['deleted' => 0])->get();
        $banks      = BankModel::get();
        $banks_codes = BankCodetypes::get();

        $Cuisines = Cuisine::where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->get();
        $Cuisines_ids = VendorCuisines::where('vendor_id',$datamain->id)->pluck('cuisine_id')->toArray();

        // $storetype  = VendorModel::VendorTypes();
        $storetype  = 2;

        $user_image = asset($datamain->user_image);
        $states = States::where(['deleted' => 0, 'active' => 1, 'country_id' => $datamain->country_id])->orderBy('name', 'asc')->get();

        /*echo "<pre>";
        print_r($datamain);
        die;*/
        $vendor_timings = VendorTimings::where(['vendor_id'=>$id])->get();
        
            $cities = Cities::where(['deleted' => 0, 'active' => 1, 'state_id' => $datamain->state_id])->orderBy('name', 'asc')->get();
        if ($datamain) {
            return view("admin.vendor.view", compact('page_heading','vendor_timings', 'datamain','id','countries','states','cities','user_image','industry','banks','banks_codes','storetype','Cuisines','Cuisines_ids'));
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $dataupdate = VendorModel::find($id);
        $dataupdate->admin_viewed = 1;
        $dataupdate->save();

        $page_heading = "Edit Vendor";
        $datamain = VendorModel::with('menu_images')->find($id);
        $datamain->vendordatils = VendorDetailsModel::where('user_id',$id)->first();
        $datamain->bankdetails  = BankdataModel::where('user_id',$id)->first();
        $datamain->gr_availablity =VendorServiceTimings::where(['service_id'=>2,'vendor'=>$id])->first();
        $activity_id = $datamain->activity_id;
        $Cuisines = Cuisine::where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->get();
        $Cuisines_ids = VendorCuisines::where('vendor_id',$datamain->id)->pluck('cuisine_id')->toArray();

        $countries = CountryModel::where(['deleted' => 0])->orderBy('name','asc')->get();
        $industry = IndustryTypes::where(['deleted' => 0])->get();
        $banks      = BankModel::get();
        $banks_codes = BankCodetypes::get();

        $storetype  = 2;

        $activity_types = ActivityType::select('id','name as activity_name')->where(['deleted' => 0, 'account_id' => AccountType::COMMERCIAL_CENTER])->get();

        $user_image = asset($datamain->user_image);
        $states = States::where(['deleted' => 0, 'active' => 1, 'country_id' => $datamain->country_id])->orderBy('name', 'asc')->get();

            $cities = Cities::where(['deleted' => 0, 'active' => 1, 'state_id' => $datamain->state_id])->orderBy('name', 'asc')->get();
            $vendor_timings = VendorTimings::where(['vendor_id'=>$id])->get();
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
           
            //printr($selected_days); exit;
        if ($datamain) {
            
            return view("admin.vendor.create", compact('page_heading','selected_days', 'datamain','id','countries','states','cities','user_image','industry','banks','banks_codes','storetype','activity_types','activity_id','Cuisines','Cuisines_ids'));
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
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if(VendorModel::where('id', $request->id)->update(['active' => $request->status])) {
            $status = "1";
            $msg = "Successfully activated";
            if (!$request->status) {
                $msg = "Successfully deactivated";
            }
            $message = $msg;
        } else {
            $message = "Something went wrong";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function verify(Request $request)
    {
        $status = "0";
        $message = "";
        if (VendorModel::where('id', $request->id)->update(['verified' => $request->status])) {
            $status = "1";
            $msg = "Successfully verified";

            $link = url('portal');
            $user = VendorModel::find($request->id);
            $mailbody =  view("mail.verify_mail",compact('user','link'));
            $res = send_email($user->email,'Your Membership To '.env("APP_NAME").' Has Been Approved',$mailbody);

            if (!$request->status) {
                $msg = "Successfully updated";
            }
            $message = $msg;
        } else {
            $message = "Something went wrong";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function destroy($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $datatb = VendorModel::find($id);
        if ($datatb) {
            $datatb->email = $datatb->email."_deleted_mail";
            $datatb->phone = $datatb->phone."_deleted_mobile";
            
            $datatb->deleted = 1;
            $datatb->active = 0;
            $datatb->save();


            $products = ProductModel::where('product_vender_id',$id)->get();
            foreach ($products as $key => $row) {
                ProductModel::where('id', $row->id)->update(['deleted' => 1]);
            }

            $status = "1";
            $message = "Vendor removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }
    public function approve(Request $request)
    {
        $status = "0";
        $message = "";
        if (VendorModel::where('id', $request->id)->update(['approve' => $request->status])) {
            $status = "1";
            $msg = "Successfully approved";
            if($request->status == 2) {
                $msg = "Successfully rejected";
            }
            $message = $msg;
        } else {
            $message = "Something went wrong";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    public function add_services( Request $request ){
        $vendor_id = $request->vendor_id ?? 0;
        $services_id = $request->ven_services ?? '';

        if ( $services_id != "" && $vendor_id > 0) {
                $ven_service = explode(',', $services_id);
                if ( isset ( $ven_service ) && count ( $ven_service ) > 0 ) {

                    foreach ( $ven_service as $index => $service_id ) {
                        DB::table('vendor_services')->updateOrInsert(
                            ['vendor_id' => $vendor_id, 'service_id' => $service_id],
                            ['created_at' => gmdate("Y-m-d H:s"), 'updated_at' => gmdate("Y-m-d H:s")]
                        );
                    }
                }

            echo json_encode(['status' => "1", 'message' => "Service added successfully"]);
            exit;

        }

        echo json_encode(['status' => "0", 'message' => "Invalid record"]);
        exit;

    }

    public function view_services()
    {
        $page_heading = "Vendor Services";

        $vendor_id = 0;
        if ( isset ( $_REQUEST['vendor'] ) && $_REQUEST['vendor'] > 0 ) {
            $vendor_id = $_REQUEST['vendor'];
        }

        $datamain = VendorModel::select('*')
            ->where(['role'=>'3','id'=>$vendor_id])->first();
            
        $activity_id = $datamain->activity_id;
        $vendor_name = '';
        $vendor_details = VendorDetailsModel::where('user_id',$vendor_id)->first();

        if ( isset ( $datamain ) )
            $vendor_name = $vendor_details->company_name;


        $category_with_services = DB::table('service_category_selected as scs')
            ->join('service_category as sc', 'sc.id', '=', 'scs.category_id')
            ->join('service as s', 's.id', '=', 'scs.service_id')
            ->join('vendor_services as vs', 'vs.service_id', '=', 's.id')
            ->select("sc.id as category_id", "sc.name as category_name", "s.id as service_id", "s.name as service_name", "vs.created_at", "vs.id as vendor_service_id", "vs.vendor_id")
            ->where('vs.vendor_id', $vendor_id)
            ->get();
            
      $selected_services = DB::table('service_category_selected as scs')
            ->join('service_category as sc', 'sc.id', '=', 'scs.category_id')
            ->join('service as s', 's.id', '=', 'scs.service_id')
            ->join('vendor_services as vs', 'vs.service_id', '=', 's.id')
            ->select("s.id as service_id")
            ->where('vs.vendor_id', $vendor_id)
            ->pluck('service_id')->toArray();
            
            
       $category_with_services = DB::table('service_category_selected as scs')
            ->join('service_category as sc', 'sc.id', '=', 'scs.category_id')
            ->join('service as s', 's.id', '=', 'scs.service_id')
            ->select("sc.id as category_id", "sc.name as category_name", "s.id as service_id", "s.name as service_name")
            ->where('sc.activity_id', $activity_id)
            ->where('sc.active', 1)
            ->whereNotIn('s.id',$selected_services)
            ->get();
            
              $aService_data = [];
        if ( isset ( $category_with_services ) && count ( $category_with_services ) > 0 ) {
            foreach ( $category_with_services as $index => $service ) {
                $aService_data[$service->category_id.'_'.$service->category_name][] = array
                (
                    "service_id" => $service->service_id,
                    "service_name" => $service->service_name
                );
            }
        }
        
         $category_with_services = DB::table('service_category_selected as scs')
            ->join('service_category as sc', 'sc.id', '=', 'scs.category_id')
            ->join('service as s', 's.id', '=', 'scs.service_id')
            ->join('vendor_services as vs', 'vs.service_id', '=', 's.id')
            ->select("sc.id as category_id", "sc.name as category_name", "s.id as service_id", "s.name as service_name", "vs.created_at", "vs.id as vendor_service_id", "vs.vendor_id")
            ->where('vs.vendor_id', $vendor_id)->where(['s.active'=>1,'s.deleted'=>0])
            ->get();

        return view("admin.vendor.services_list", compact("category_with_services", "page_heading", "vendor_name","aService_data","vendor_id"));

    }

    public function delete_services(Request $request){

        $service_id = 0;
        $vendor_id = 0;

        if ( isset ( $_REQUEST['service_id'] ) )
            $service_id = $_REQUEST['service_id'] ?? 0;

        if ( isset ( $_REQUEST['vendor_id'] ) )
            $vendor_id = $_REQUEST['vendor_id'] ?? 0;

        DB::table("vendor_services")->where('id', $service_id)->where('vendor_id', $vendor_id)->delete();

        echo json_encode(['status' => "1", 'message' => "Service deleted successfully"]);
        exit;

    }
}
