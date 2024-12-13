<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorModel;
use App\Models\VendorDetailsModel;
use App\Models\BankdataModel;
use App\Models\CountryModel;
use App\Models\Designations;
use App\Models\States;
use App\Models\Cities;
use App\Models\Stores;
use Illuminate\Http\Request;
use Validator;
use Hash;

class StoremanagersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Role";
        $datamain = VendorModel::select('*','users.name as name','users.active as active','users.id as id','stores.store_name')
            ->leftjoin('stores','stores.id','=','users.store')
            ->where(['role'=>'4','users.deleted'=>'0', 'vendor_id' => auth()->user()->id])
            ->orderBy('users.id','desc')->get();

        return view('vendor.store_managers.list', compact('page_heading', 'datamain'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Role";
        $mode = "create";
        $id = "";
        $prefix = "";
        $name = "";
        $dial_code = "";
        $image = "";
        $active = "1";
        $states = [];
        $cities = [];
        $countries     = CountryModel::orderBy('name','asc')->get();
        $designation   = Designations::where('user_id', auth()->user()->id)->where('user_type', 3)->where('status', 1)->get();
        $vendors       = VendorModel::where(['role'=>'3','users.deleted'=>'0', 'id' => auth()->user()->id])->orderBy('users.name','desc')->get();
        $stores        = Stores::select('id','store_name')->where(['deleted' => 0,'active'=>1,'vendor_id'=>auth()->user()->id])->get();
        return view("vendor.store_managers.create", compact('page_heading', 'designation', 'id', 'name', 'dial_code', 'active','prefix','countries','states','cities','vendors','stores'));
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
                        'role'       => '4',//store manager
                        'vendor'     => $request->seller_id,
                        'store'      => $request->store_id,
                        'previllege' => $request->designation,
                        'designation_id' => $request->designation,
                        'first_name' => '',
                        'last_name'  => '',
                        'state_id'   => $request->state_id,
                        'city_id'    => $request->city_id,
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
                        $message = "Role details updated succesfully";
                    } else {
                        $ins['created_at'] = gmdate('Y-m-d H:i:s');
                        $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                        $userid = VendorModel::create($ins)->id;


                        $vendordatils = new VendorDetailsModel();
                        $vendordatils->user_id = $userid;

                        $bankdata = new BankdataModel();
                        $bankdata->user_id = $userid;

                        $status = "1";
                        $message = "Role added successfully";
                    }

                    $vendordatils->industry_type  = 1;
                    $vendordatils->homedelivery   = 0;
                    // $vendordatils->branches      = $request->no_of_branches;
                    // $vendordatils->company_name       = $request->company_legal_name;
                    // $vendordatils->company_brand      = $request->company_brand_name;
                    // $vendordatils->reg_date           = $request->business_registration_date;
                    // $vendordatils->trade_license           = $request->trade_licene_number;
                    // $vendordatils->trade_license_expiry    = $request->trade_licene_expiry;
                    // $vendordatils->vat_reg_number      = $request->vat_registration_number;
                    // $vendordatils->vat_reg_expiry      = $request->vat_expiry_date;

                    $vendordatils->address1      = $request->address1;
                    $vendordatils->address2      = $request->address2;
                    $vendordatils->street        = $request->street;
                    $vendordatils->state         = $request->state_id;
                    $vendordatils->city          = $request->city_id;
                    $vendordatils->zip           = $request->zip;


                    //logo
                    if($request->file("logo")){
                        $response = image_upload($request,'company','logo');
                        if($response['status']){
                            $vendordatils->logo = $response['link'];
                        }
                    }
                    //logo end
                    $vendordatils->save();





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
        $page_heading = "Edit Role";
        $datamain = VendorModel::find($id);
        $datamain->vendordatils = VendorDetailsModel::where('user_id',$id)->first();
        $datamain->bankdetails = BankdataModel::where('user_id',$id)->first();
        $vendors       = VendorModel::where(['role'=>'3','users.deleted'=>'0', 'id' => auth()->user()->id])->orderBy('users.name','desc')->get();

        $countries = CountryModel::orderBy('name','asc')->get();
        $user_image = asset($datamain->user_image);
        $states = States::where(['deleted' => 0, 'active' => 1, 'country_id' => $datamain->country_id])->orderBy('name', 'asc')->get();
        $designation   = Designations::where('user_id', auth()->user()->id)->where('user_type', 3)->where('status', 1)->get();
        $stores        = Stores::select('id','store_name')->where(['deleted' => 0,'active'=>1,'vendor_id'=>$datamain->vendor])->get();


        $cities = Cities::where(['deleted' => 0, 'active' => 1, 'state_id' => $datamain->state_id])->orderBy('name', 'asc')->get();
        if ($datamain) {
            return view("vendor.store_managers.create", compact('page_heading', 'datamain','id','countries','states','cities','user_image','designation','vendors','stores'));
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
            $message = "Role removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }
}
