<?php

namespace App\Http\Controllers\portal;

use App\Categories;
use App\CityModel;
use App\CountryModel;
use App\Http\Controllers\Controller;
use App\StateModel;
use App\UserDocsModel;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
use App\PackageModel;
use App\IndustryTypesModel;
use App\TransactionFeesModel;
use App\AccountTypesModel;
use App\Partnership;
use Illuminate\Support\Facades\Auth;
class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_heading = "Users";
        return view("admin.users.buyer_list", compact("page_heading"));
    }
    public function buyers()
    {
        $page_heading = "Users";
        $filter = ['res_groups.name' => 'buyer'];
        $params = [];
        $search_key = $_GET['search_key'] ?? '';
        $params['search_key'] = $search_key;
        $list = User::get_users_list($filter, $params)->orderBy('res_users.id','desc')->paginate(10);
        $packages = PackageModel::where('deleted',0)->get()->toArray();
        return view("admin.users.user_list", compact("page_heading", "list",'search_key','packages'));
    }
    public function sellers()
    {
        $page_heading = "Vendors";
        $filter = ['res_groups.name' => 'seller','res_users.deleted'=>0];
        $params = [];
        $search_key = $_GET['search_key'] ?? '';
        $params['search_key'] = $search_key;
        $list = Users::get_users_list($filter, $params)->orderBy('res_users.id','desc')->paginate(10);
        $packages = PackageModel::where('deleted',0)->get()->toArray();
        return view("admin.users.buyer_list", compact("page_heading", "list",'search_key','packages'));
    }
    public function trashed()
    {
        $page_heading = "Trashed Members";
        $filter = ['res_groups.name' => 'seller','res_users.deleted'=>1];
        $params = [];
        $search_key = $_GET['search_key'] ?? '';
        $params['search_key'] = $search_key;
        $list = Users::get_users_list($filter, $params)->paginate(10);
        $packages = PackageModel::where('deleted',0)->get()->toArray();
        return view("admin.users.trashed_user_list", compact("page_heading", "list",'search_key','packages'));
    }
    public function delete_document($id = '')
    {
        UserDocsModel::where('id', $id)->delete();
        $status = "1";
        $message = "Document removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function delete_user($id = '')
    {
        Users::delete_user($id);
        $status = "1";
        $message = "User removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function active_user($id = '')
    {
        Users::active_user($id);
        $status = "1";
        $message = "User activated successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    public function verify_user($id){
        $status = "0";
        $message = "";
        $user = Users::find($id);
        if($user){
            if($user->user_verified == 1){
                $user_verified = 0;
            }else{
                // if(!$user->user_package){
                //$message = "Membership not assigned!! Please assign membership package first";
                // echo json_encode(['status' => $status, 'message' => $message,'user_id'=>$id,'st'=>'membrshp_not_asgnd']);die();
                //}
                $user_verified = 1;
            }

            $ret = Users::update_user(['user_verified'=>$user_verified,'updated_on'=>gmdate('Y-m-d H:i:s'),'updated_uid'=>session('user_id')],$id);
            if($ret){
                if($user_verified==1 ){
                    $link = url('portal/login');
                    $mailbody =  view("web.emai_templates.verify_mail",compact('user','link'));
                    $res = send_email($user->email,'Your Membership To The My events Marketplace Has Been Approved',$mailbody);
                    if($res){
                        Users::update_user([
                            'is_verify_email_sent'=>1
                        ],$id);
                    }
                }
                $status = "1";
                $message = $user_verified==1?"Verified successfully":'User Rejected';
            }else{
                $message = "Faild to update";
            }
        }else{
            $message = "User not found";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function assign_package(Request $request){

        $status = "0";
        $message = "";
        $errors = '';
        $validator = Validator::make($request->all(), [
                'membership_package' => 'required',
                'alt_api_key'=>'required',
                'alt_secret_key'=>'required',
                'alt_merchant_id'=>'required',
            ]
        );
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $id=$request->user_id;
            $user = Users::find($id);
            if($user){
                if($user->user_verified == 1){
                    $user_verified = 0;
                }else{
                    $user_package = $request->membership_package;
                    $user_verified = 1;
                }
                $ret = Users::update_user([
                    'user_verified'=>$user_verified,
                    'user_package'=>$user_package,
                    'updated_on'=>gmdate('Y-m-d H:i:s'),
                    'updated_uid'=>session('user_id'),
                    'alt_api_key'   =>  $request->alt_api_key,
                    'alt_secret_key'    =>  $request->alt_secret_key,
                    'alt_merchant_id'   =>  $request->alt_merchant_id
                ],$id);
                if($ret){
                    if($user_verified==1 && !$user->is_verify_email_sent){
                        $link = url('login');
                        $mailbody =  view("web.emai_templates.verify_mail",compact('user','link'));
                        $res = send_email($user->email,'Your Membership To The Oodle Marketplace Has Been Approved',$mailbody);
                        if($res){
                            Users::update_user([
                                'is_verify_email_sent'=>1
                            ],$id);
                        }
                    }
                    $status = "1";
                    $message = $user_verified==1?"Verified successfully":'User Rejected';
                }else{
                    $message = "Faild to update";
                }
            }else{
                $message = "User not found";
            }
        }
        echo json_encode(['status' => $status, 'message' => $message,'errors'=>$errors]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function edit_users($id)
    {

        $rltn = [
            'docs' => function ($qr) {
                $qr->orderBy('id', 'asc');
            },
            'user_categories' => function ($qr1) {
                $qr1->orderBy('id', 'asc');
            },
        ];

        $data['user'] = Users::select('res_users.*', 'phone', 'place', 'passport_number', 'passport_file', 'trade_licence_number', 'trade_licence_file', 'res_user_additional_info.description','po_box','website_url','industry_type','account_type','transaction_fee','photo_id')->leftjoin('res_user_additional_info', 'res_user_additional_info.user_id', '=', 'res_users.id')->join('res_users_groups', 'res_users_groups.user_id', '=', 'res_users.id')
            ->join('res_groups', 'res_groups.id', '=', 'res_users_groups.group_id')
            ->where('res_groups.name', 'buyer')->where('res_users.id', $id)
            ->with($rltn)->get()->toArray();
        if (!$data['user']) {
            abort(404);
        } else {
            $data['user'] = $data['user'][0];
            // $data['user']['states'] = StateModel::select('id', 'name')->where(['deleted' => 0, 'active' => 1, 'country_id' => $data['user']['country_id']])->get();

            // $data['user']['cities'] = CityModel::select('id', 'name')->where(['deleted' => 0, 'active' => 1, 'state_id' => $data['user']['state_id']])->get();
        }
        $data['user']['user_categories'] = array_column($data['user']['user_categories'], 'category_id');
        $data['country_list'] = CountryModel::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        $data['parent_categories'] = Categories::where(['deleted' => 0, 'active' => 1, 'parent_id' => 0])->get();
        $industry_types = IndustryTypesModel::where(['deleted'=>0,'active'=>1])->orderBy('name','asc')->get();
        $account_types = AccountTypesModel::where(['deleted'=>0,'active'=>1])->orderBy('name','asc')->get();
        $transaction_fees = TransactionFeesModel::where(['deleted'=>0,'active'=>1])->orderBy('name','asc')->get();
        $page_heading = "Edit Profile";
        $packages = PackageModel::where('deleted',0)->get()->toArray();
        return view("admin.users.users_details", compact('page_heading', "data",'packages','industry_types','account_types','transaction_fees'));
    }
    public function edit($id)
    {

        $rltn = [
            'docs' => function ($qr) {
                $qr->orderBy('id', 'asc');
            },
            'user_categories' => function ($qr1) {
                $qr1->orderBy('id', 'asc');
            },
        ];

        $data['user'] = Users::select('res_users.*', 'phone', 'place', 'passport_number', 'res_users.description as description','res_users.photo_id', 'res_users.passport_file', 'res_users.trade_licence_number', 'res_users.trade_licence_file','po_box','website_url','industry_type','account_type','transaction_fee')->leftjoin('res_user_additional_info', 'res_user_additional_info.user_id', '=', 'res_users.id')->join('res_users_groups', 'res_users_groups.user_id', '=', 'res_users.id')
            ->join('res_groups', 'res_groups.id', '=', 'res_users_groups.group_id')
            ->where('res_groups.name', 'seller')->where('res_users.id', $id)
            ->with($rltn)->get()->toArray();
        if (!$data['user']) {
            abort(404);
        } else {
            $data['user'] = $data['user'][0];
            // $data['user']['states'] = StateModel::select('id', 'name')->where(['deleted' => 0, 'active' => 1, 'country_id' => $data['user']['country_id']])->get();

            // $data['user']['cities'] = CityModel::select('id', 'name')->where(['deleted' => 0, 'active' => 1, 'state_id' => $data['user']['state_id']])->get();
        }
        $data['user']['user_categories'] = array_column($data['user']['user_categories'], 'category_id');
        $data['country_list'] = CountryModel::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        $data['parent_categories'] = Categories::where(['deleted' => 0, 'active' => 1, 'parent_id' => 0])->get();
        $industry_types = IndustryTypesModel::where(['deleted'=>0,'active'=>1])->orderBy('name','asc')->get();
        $account_types = AccountTypesModel::where(['deleted'=>0,'active'=>1])->orderBy('name','asc')->get();
        $transaction_fees = TransactionFeesModel::where(['deleted'=>0,'active'=>1])->orderBy('name','asc')->get();
        $page_heading = "Edit Profile";
        $packages = PackageModel::where('deleted',0)->get()->toArray();
        return view("admin.users.detail", compact('page_heading', "data",'packages','industry_types','account_types','transaction_fees'));
    }
    public function update_seller(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:res_users,email,' . $request->id . ',id',
            'country_id' => 'required',
            //'state' => 'required',
            'city' => 'required',
            //'phone' => 'required|numeric',
            'dial_code' => 'required',
            'mobile' => 'required|numeric',
            'place' => 'required',
            //'passport_number' => 'required',
            //'trade_licenece_number'=> 'required',
            // 'password'       => 'required|confirmed',
            'passport_file' => 'mimes:jpeg,png,jpg,pdf',
            'trade_licence' => 'mimes:jpeg,png,jpg,pdf',
            'user_image' => 'image',
            'description'=>'required',
            //'po_box'=>'required',
            // 'website_url'=>'url',
            'business_name'=>'required',
            //'industry_type'=>'required',
            //'dob'=>'required',
            //'account_type'=>'required',
            //'transaction_fee'=>'required',
            'photo_id'=>'mimes:jpeg,png,jpg,pdf',
            'commission'=> 'required|numeric|min:0|max:99',
            //'public_wallet_address'=>'required',
            //'alt_api_key'=>'required',
            //'alt_secret_key'=>'required',
            //'alt_merchant_id'=>'required',
        ],
            [
                'passport_file.required' => 'P.O.A required',
                'country_id.required' => 'Country required',
                'state.required' => 'State required',
                'city.required' => 'City required',
                'mobile.integer' => 'Enter valid mobile',
                'phone.integer' => 'Enter valid contact number',
                'passport_file.image' => 'should be in image format (.jpg,.jpeg,.png)',
                'trade_licence.image' => 'should be in image format (.jpg,.jpeg,.png)',
                'user_image.image' => 'should be in image format (.jpg,.jpeg,.png)',
            ]
        );
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $id = $request->id;
            $category_ids = $request->category_id;

            $user_table_ins = [
                'username' => $request->email,
                'email' => strtolower($request->email),
                'dial_code' => $request->dial_code,
                'mobile' => $request->mobile,
                'mobile_verified' => 1,
                'updated_on' => gmdate('Y-m-d H:i:s'),
                'updated_uid' => 0,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'display_name' => $request->first_name . " " . $request->last_name,
                'country_id' => $request->country_id,
                'state' => $request->state,
                'city' => $request->city,
                'ip_address' => $request->ip(),
                'user_verified' => $request->user_verified,
                'user_package' => $request->user_package ?? 0,
                'business_name'           =>  $request->business_name,
                'business_name_arabic'    =>  $request->business_name_arabic,
                'dob'           =>  date('Y-m-d',strtotime($request->dob)),
                'public_wallet_address' =>$request->public_wallet_address,
                'alt_api_key'   =>  $request->alt_api_key,
                'alt_secret_key'    =>  $request->alt_secret_key,
                'alt_merchant_id'   =>  $request->alt_merchant_id,
                'commission'        =>  $request->commission,
                'address_1'             => $request->place,
                'trade_licence_number'  => $request->trade_licenece_number,
                'description'  =>  $request->description,

            ];
            if ($request->password) {
                $user_table_ins['password'] = md5($request->password);
            }
            $additional_info = [
                'phone' => $request->phone,
                'place' => $request->place,
                'passport_number' => $request->passport_number,
                'trade_licence_number' => $request->trade_licenece_number,
                'description' =>$request->description,
                'po_box'=>$request->po_box,
                'website_url'=>$request->website_url,
                'industry_type'=>$request->industry_type,
                'account_type'=>$request->account_type,
                'transaction_fee'=>$request->transaction_fee,
            ];

            if ($file = $request->file("passport_file")) {
                $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');
                $file_name = time() . $file->getClientOriginalName();
                $file->move($dir, $file_name);
                //$file->storeAs(config('global.user_image_upload_dir'),$file_name,'s3');
                $user_table_ins['passport_file'] = $file_name;
            }
            if ($file = $request->file("trade_licence")) {
                $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');
                $file_name = time() . $file->getClientOriginalName();
                $file->move($dir, $file_name);
                //$file->storeAs(config('global.user_image_upload_dir'),$file_name,'s3');
                $user_table_ins['trade_licence_file'] = $file_name;
                $additional_info['trade_licence_file'] = $file_name;
            }
            if ($file = $request->file("user_image")) {
                if(isset($request->cropped_user_image) && $request->cropped_user_image){
                    $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');
                    $image_parts = explode(";base64,", $request->cropped_user_image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $imageName = uniqid() .time(). '.'.$image_type;
                    //$path = \Storage::disk('s3')->put(config('global.user_image_upload_dir').$imageName, $image_base64);
                    //$path = \Storage::disk('s3')->url($path);
                    file_put_contents($dir.'/'.$imageName, $image_base64);
                    $user_table_ins['user_image'] = $imageName;
                }else{
                    $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');
                    $file_name = time() . $file->getClientOriginalName();
                    // $file->move($dir, $file_name);
                    $file->storeAs(config('global.user_image_upload_dir'),$file_name,'s3');
                    $user_table_ins['user_image'] = $file_name;
                }
            }
            if($file = $request->file("photo_id")){
                $dir = config('global.upload_path')."/".config('global.user_image_upload_dir');
                $file_name = time().$file->getClientOriginalName();
                $file->move($dir,$file_name);
                //$file->storeAs(config('global.user_image_upload_dir'),$file_name,'s3');
                $user_table_ins['photo_id'] = $file_name;
            }

            $button_counter = $request->button_counter;
            $other_doc_ins = [];

            for ($i = 1; $i <= $button_counter; $i++) {
                if ($file = $request->file("other_doc_image_" . $i)) {
                    $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');

                    $file_name = time() . $file->getClientOriginalName();
                    // $file->move($dir, $file_name);
                    $file->storeAs(config('global.user_image_upload_dir'),$file_name,'s3');
                    $other_doc_ins[] = ['title' => $request->{"other_doc_title_$i"}, 'doc_path' => $file_name];
                }
            }
            $ret = Users::edit_user($user_table_ins, $additional_info, $category_ids, $other_doc_ins, $id);
            if ($ret) {
                $status = "1";
                $message = "Successfully updated";
            } else {
                $status = "0";
                $message = "Something went wrong";
            }
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }
    public function update_user(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:res_users,email,' . $request->id . ',id',
            'country_id' => 'required',
            //'state' => 'required',
            'city' => 'required',
            //'phone' => 'required|numeric',
            'dial_code' => 'required',
            'mobile' => 'required|numeric',
            'place' => 'required',
            //'passport_number' => 'required',
            'trade_licenece_number'=> '',
            // 'password'       => 'required|confirmed',
            'passport_file' => 'mimes:jpeg,png,jpg,pdf',
            'trade_licence' => 'mimes:jpeg,png,jpg,pdf',
            'user_image' => 'image',
            'description'=>'',
            //'dob'=>'required',
            //'account_type'=>'required',
            //'transaction_fee'=>'required',
            'photo_id'=>'mimes:jpeg,png,jpg,pdf',
            //'public_wallet_address'=>'required',
            //'alt_api_key'=>'required',
            //'alt_secret_key'=>'required',
            //'alt_merchant_id'=>'required',
        ],
            [
                'passport_file.required' => 'P.O.A required',
                'country_id.required' => 'Country required',
                'state.required' => 'State required',
                'city.required' => 'City required',
                'mobile.integer' => 'Enter valid mobile',
                'phone.integer' => 'Enter valid contact number',
                'passport_file.image' => 'should be in image format (.jpg,.jpeg,.png)',
                'trade_licence.image' => 'should be in image format (.jpg,.jpeg,.png)',
                'user_image.image' => 'should be in image format (.jpg,.jpeg,.png)',
            ]
        );
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $id = $request->id;
            $category_ids = $request->category_id;

            $user_table_ins = [
                'username' => $request->email,
                'email' => strtolower($request->email),
                'dial_code' => $request->dial_code,
                'mobile' => $request->mobile,
                'mobile_verified' => 1,
                'updated_on' => gmdate('Y-m-d H:i:s'),
                'updated_uid' => 0,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'display_name' => $request->first_name . " " . $request->last_name,
                'country_id' => $request->country_id,
                'state' => $request->state,
                'city' => $request->city,
                'ip_address' => $request->ip(),
                'user_verified' => $request->user_verified,
                'user_package' => $request->user_package ?? 0,
                'business_name'           =>  $request->business_name,
                'business_name_arabic'    =>  $request->business_name_arabic,
                'dob'           =>  date('Y-m-d',strtotime($request->dob)),
                'public_wallet_address' =>$request->public_wallet_address,
                'alt_api_key'   =>  $request->alt_api_key,
                'alt_secret_key'    =>  $request->alt_secret_key,
                'alt_merchant_id'   =>  $request->alt_merchant_id
            ];
            if ($request->password) {
                $user_table_ins['password'] = md5($request->password);
            }
            $additional_info = [
                'phone' => $request->phone,
                'place' => $request->place,
                'passport_number' => $request->passport_number,
                'trade_licence_number' => $request->trade_licenece_number,
                'description' =>$request->description,
                'po_box'=>$request->po_box,
                'website_url'=>$request->website_url,
                'industry_type'=>$request->industry_type,
                'account_type'=>$request->account_type,
                'transaction_fee'=>$request->transaction_fee,
            ];

            if ($file = $request->file("passport_file")) {
                $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');
                $file_name = time() . $file->getClientOriginalName();
                // $file->move($dir, $file_name);
                $file->storeAs(config('global.user_image_upload_dir'),$file_name,'s3');
                $additional_info['passport_file'] = $file_name;
            }
            if ($file = $request->file("trade_licence")) {
                $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');
                $file_name = time() . $file->getClientOriginalName();
                $file->move($dir, $file_name);
                //$file->storeAs(config('global.user_image_upload_dir'),$file_name,'s3');

                $additional_info['trade_licence_file'] = $file_name;
            }
            if ($file = $request->file("user_image")) {
                if(isset($request->cropped_user_image) && $request->cropped_user_image){
                    $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');
                    $image_parts = explode(";base64,", $request->cropped_user_image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $imageName = uniqid() .time(). '.'.$image_type;
                    //$path = \Storage::disk('s3')->put(config('global.user_image_upload_dir').$imageName, $image_base64);
                    //$path = \Storage::disk('s3')->url($path);
                    file_put_contents($dir.'/'.$imageName, $image_base64);
                    $user_table_ins['user_image'] = $imageName;
                }else{
                    $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');
                    $file_name = time() . $file->getClientOriginalName();
                    // $file->move($dir, $file_name);
                    $file->storeAs(config('global.user_image_upload_dir'),$file_name,'s3');
                    $user_table_ins['user_image'] = $file_name;
                }
            }
            if($file = $request->file("photo_id")){
                $dir = config('global.upload_path')."/".config('global.user_image_upload_dir');
                $file_name = time().$file->getClientOriginalName();
                // $file->move($dir,$file_name);
                $file->storeAs(config('global.user_image_upload_dir'),$file_name,'s3');
                $additional_info['photo_id'] = $file_name;
            }

            $button_counter = $request->button_counter;
            $other_doc_ins = [];

            for ($i = 1; $i <= $button_counter; $i++) {
                if ($file = $request->file("other_doc_image_" . $i)) {
                    $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');

                    $file_name = time() . $file->getClientOriginalName();
                    // $file->move($dir, $file_name);
                    $file->storeAs(config('global.user_image_upload_dir'),$file_name,'s3');
                    $other_doc_ins[] = ['title' => $request->{"other_doc_title_$i"}, 'doc_path' => $file_name];
                }
            }
            $ret = Users::edit_user($user_table_ins, $additional_info, $category_ids, $other_doc_ins, $id);
            if ($ret) {
                $status = "1";
                $message = "Successfully updated";
            } else {
                $status = "0";
                $message = "Something went wrong";
            }
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
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
        //
    }
    public function change_password(Request $request)
    {
        if ($request->isMethod('post')) {
            $status = "0";
            $message = "";
            $errors = [];
            $validator = Validator::make($request->all(),
                [
                    'cur_pswd' => 'required',
                    'new_pswd' => 'required',
                ],
                [
                    'cur_pswd.required' => 'Current password required',
                    'new_pswd.required' => 'New password required',
                ]
            );
            if ($validator->fails()) {
                $status = "0";
                $message = "Validation error occured";
                $errors = $validator->messages();
            } else {
                $cur_pswd = $request->cur_pswd;
                $new_pswd = $request->new_pswd;
                $user_id = session("user_id");
                if(Auth::attempt(['id' => $user_id, 'password' => $cur_pswd])) {
                    $up['password'] = bcrypt($new_pswd);
                    $up['updated_on'] =gmdate('Y-m-d H:i:s');
                    if(User::update_password($user_id,$new_pswd)){
                        $status = "1";
                        $message = "Password successfully changed";
                        $errors = '';
                    }else{
                        $status = "0";
                        $message = "Unable to change password. Please try again later";
                        $errors = '';
                    }
                }else{
                    $status = "0";
                    $message = "Invalid Current Password";
                    $errors = '';
                }

            }
            echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
        }else{
            $page_heading = "Change Password";
            return view("portal.users.change_password", compact('page_heading'));
        }

    }
    public function send_mail( $id ){
        $id = base64_decode($id);
        $user = Users::where('id','=',$id)->get()->first();
        if(!empty($user)){
            $page_heading = "Send Mail to ".$user->display_name;
            return view("admin.users.send_mail", compact("page_heading","user"));
        }else{
            abort(404);
        }
    }
    public function submit_mail( REQUEST $request){
        $status = "0";
        $message = "";
        $user = Users::where('id','=',$request->id)->first();
        if(!empty($user)){
            $message = $request->message;
            $mailbody =  view("web.emai_templates.custom_mail",compact('message','user'));
            $ret = send_email($user->email,'OODLE',$mailbody);
            if($ret){
                $status = "1";
                $message = "Mail sent successfully";
            }else{
                $status = "0";
                $message ="Faild to sent mail";
            }
        }
        echo json_encode(['status'=>$status,'messsage'=>$message]);
    }
}
