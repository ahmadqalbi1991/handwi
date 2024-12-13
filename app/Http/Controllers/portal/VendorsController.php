<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorModel;
use App\Models\VendorDetailsModel;
use App\Models\BankdataModel;
use App\Models\CountryModel;
use App\Models\StoreType;
use App\Models\States;
use App\Models\Cities;
use App\Models\BankModel;
use App\Models\BankCodetypes;
use App\Models\ActivityType;
use App\Models\VendorTimings;
use App\Models\VendorServiceTimings;
use Illuminate\Http\Request;
use Validator;
use Hash;
use DB;
use Auth;
use App\Models\VendorMenuImages;
use App\Models\VendorCuisines;
use App\Models\Cuisine;

class VendorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Vendors";
        $datamain = VendorModel::select('*','users.name as name','industry_types.name as industry','users.active as active','users.id as id')
            ->where(['role'=>'3','users.deleted'=>'0'])
            //->with('vendordata')
            ->leftjoin('vendor_details','vendor_details.user_id','=','users.id')
            ->leftjoin('industry_types','industry_types.id','=','vendor_details.industry_type')
            ->orderBy('users.id','desc')->get();

        return view('portal.vendor.list', compact('page_heading', 'datamain'));
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
        $states = [];
        $cities = [];
        $countries  = CountryModel::orderBy('name','asc')->get();
        $industry   = IndustryTypes::where(['deleted' => 0])->get();
        $banks      = BankModel::get();
        return view("portal.vendor.create", compact('page_heading', 'industry', 'id', 'name', 'dial_code', 'active','prefix','countries','states','cities','banks'));
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
            'company_legal_name' => 'required',
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
                        'state_id'   => 0,
                        'city_id'    => $request->city_id,
                        'minimum_order_amount'  => $request->minimum_amount,
                        'is_dinein' => $request->is_dinein ?? 0,
                        'is_delivery' => $request->is_delivery ?? 0,
                        'delivery_charge' => $request->delivery_charge ?? 0,
                    ];



                    if($request->password){
                        $ins['password'] = bcrypt($request->password);
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
                    } else {
                        $ins['created_at'] = gmdate('Y-m-d H:i:s');
                        $userid = VendorModel::create($ins)->id;


                        $vendordatils = new VendorDetailsModel();
                        $vendordatils->user_id = $userid;

                        $bankdata = new BankdataModel();
                        $bankdata->user_id = $userid;

                        $status = "1";
                        $message = "Vendor added successfully";
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

                    $lat = "";
                    $long = "";
                    if ($request->location) {
                    $location = explode(",", $request->location);
                    $lat = $location[0];
                    $long = $location[1];
                    }

                    $vendordatils->industry_type = $industrytype;
                    $vendordatils->homedelivery  = 0;
                    $vendordatils->branches      = 0;
                    $vendordatils->company_name       = $request->company_legal_name;
                    $vendordatils->company_brand      = $request->company_brand_name;
                    $vendordatils->reg_date           = $request->business_registration_date;
                    $vendordatils->trade_license           = $request->trade_licene_number;
                    $vendordatils->trade_license_expiry    = $request->trade_licene_expiry;
                    $vendordatils->vat_reg_number      = $request->vat_registration_number;
                    $vendordatils->vat_reg_expiry      = $request->vat_expiry_date;

                    $vendordatils->location = $request->txt_location;
                    $vendordatils->latitude = $lat;
                    $vendordatils->longitude = $long;

                    $vendordatils->address1      = $request->address1;
                    $vendordatils->address2      = $request->address2;
                    $vendordatils->street        = $request->street;
                    $vendordatils->state         = 0;
                    $vendordatils->city          = $request->city_id;
                    $vendordatils->area          = $request->area;
                    $vendordatils->zip           = $request->zip;
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
                    //logo end

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


                    //cover image
                 if($request->file("cover_image")){
                    $response = image_upload($request,'company','cover_image');
                    if($response['status']){
                 $vendordatils->cover_image = $response['link'];
                    }
                 }
                 //cover image end

             
                     if ($request->file("trade_licence")) {
                        $response = image_upload($request, 'company', 'trade_licence');
                        if ($response['status']) {
                            $vendordatils->trade_license_doc = $response['link'];
                        }
                    }
                    $vendordatils->save();

                    $bankdata->bank_name       = $request->bank_id;
                    $bankdata->country         = $request->bankcountry;
                    $bankdata->company_account = $request->company_account;
                    $bankdata->account_no      = $request->bank_account_number;
                    $bankdata->branch_code     = $request->bank_branch_code;
                    $bankdata->branch_name     = $request->branch_name;
                    $bankdata->iban            = $request->iban;
                    if($request->file("bank_statement")){
                        $response = image_upload($request,'company','bank_statement');
                        if($response['status']){
                     $bankdata->bank_statement_doc     = $response['link'];
                        }
                     }

                    $bankdata->save();


                    // VendorServiceTimings::where('vendor',$request->id)->delete();
                    // $days = Config('global.days') ;
                    // $gr_availability['vendor'] = $request->id??$userid;
                    // $gr_availability['service_id'] = 2;
                    // foreach($days as $key =>$val){
                    //     $vet_val = $val.'_grooming';
                    //     if(isset($request->{$vet_val})) {
                    //         $gr_availability[$val] = $request->{$vet_val} ;
                    //         $gr_availability[$key.'_from'] = $request->{$key.'_from_grooming'} ;
                    //         $gr_availability[$key.'_to'] = $request->{$key.'_to_grooming'} ;
                    //     } else {
                    //         $gr_availability[$val] = 0;
                    //         $gr_availability[$key.'_from'] = '00:00';
                    //         $gr_availability[$key.'_to'] = '00:00';
                    //     }
                    // }
                    // VendorServiceTimings::create($gr_availability);

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
        $page_heading = "Edit Vendor";
        $datamain = VendorModel::with('menu_images')->find($id);
        $datamain->vendordatils = VendorDetailsModel::where('user_id',$id)->first();
        $datamain->bankdetails = BankdataModel::where('user_id',$id)->first();
       
        $countries = CountryModel::orderBy('name','asc')->get();
        $industry = IndustryTypes::where(['deleted' => 0])->get();
        $banks      = BankModel::get();
        $user_image = asset($datamain->user_image);
        $states = States::where(['deleted' => 0, 'active' => 1, 'country_id' => $datamain->country_id])->orderBy('name', 'asc')->get();

        $Cuisines = Cuisine::where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->get();
        $Cuisines_ids = VendorCuisines::where('vendor_id',$datamain->id)->pluck('cuisine_id')->toArray();

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
        if ($datamain) {
            return view("vendor.vendor.create", compact('page_heading','selected_days', 'datamain','id','countries','states','cities','user_image','industry','banks','Cuisines','Cuisines_ids'));
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
    public function destroy($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $datatb = VendorModel::find($id);
        if ($datatb) {
            $datatb->deleted = 1;
            $datatb->active = 0;
            $datatb->save();
            $status = "1";
            $message = "Vendor removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }

    public function MyProfile(){

        $id  = auth()->user()->id;

        $page_heading = "My Profile";
        $datamain = VendorModel::with('menu_images')->find($id);
        $datamain->vendordatils = VendorDetailsModel::where('user_id',$id)->first();
        $datamain->bankdetails = BankdataModel::where('user_id',$id)->first();
        $datamain->gr_availablity =VendorServiceTimings::where(['service_id'=>2,'vendor'=>$id])->first();


        $countries = CountryModel::orderBy('name','asc')->get();
        $storetype  = StoreType::where(['deleted' => 0])->get();
        $banks_codes = BankCodetypes::get();
        $banks      = BankModel::get();
        $user_image = asset($datamain->user_image);
        $activityTypes = ActivityType::with('account')->where(['deleted' => '0'])
            ->orderBy('sort_order', 'asc')->get();
        $states = States::where(['deleted' => 0, 'active' => 1, 'country_id' => $datamain->country_id])->orderBy('name', 'asc')->get();

        $cities = Cities::where(['deleted' => 0, 'active' => 1, 'country_id' => $datamain->city_id])->orderBy('name', 'asc')->get();

        $Cuisines = Cuisine::where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->get();
        $Cuisines_ids = VendorCuisines::where('vendor_id',$datamain->id)->pluck('cuisine_id')->toArray();

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

            

        if ($datamain) {
            return view("portal.vendor.profile", compact('page_heading', 'selected_days','datamain','id','countries','states','cities','activityTypes','user_image','banks','storetype','banks_codes','Cuisines','Cuisines_ids'));
        } else {
            abort(404);
        }
    }
    public function add_services( Request $request ){

        $vendor_id = Auth::user()->id;
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
        if(Auth::user()->activity_id != 6 && Auth::user()->activity_id != 4 && Auth::user()->activity_id != 1)
        {
            return redirect('portal');
        }
        $page_heading = "Vendor Services";

        $vendor_id = Auth::user()->id;
        $activity_id = Auth::user()->activity_id;
       
        $datamain = VendorModel::select('*')
            ->where(['role'=>'3','id'=>$vendor_id])->first();

        $vendor_name = '';

        if ( isset ( $datamain ) )
            $vendor_name = $datamain->first_name . ' ' . $datamain->last_name;


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

   


            

        return view("portal.vendor.services_list", compact("category_with_services", "page_heading", "vendor_name","aService_data"));

    }
    public function delete_services(Request $request){

        $service_id = 0;
        $vendor_id = 0;

        if ( isset ( $_REQUEST['service_id'] ) )
            $service_id = $_REQUEST['service_id'] ?? 0;

        
            $vendor_id = Auth::user()->id;

        DB::table("vendor_services")->where('id', $service_id)->where('vendor_id', $vendor_id)->delete();

        echo json_encode(['status' => "1", 'message' => "Service deleted successfully"]);
        exit;

    }
}
