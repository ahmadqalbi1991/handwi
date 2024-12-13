<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\ActivityType;
use Illuminate\Http\Request;
use Validator;

class ActivityTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // if (!get_user_permission('activity_types', 'r')) {
        //     return redirect()->route('admin.restricted_page');
        // }
        $page_heading = "Activity Type";
        $activityTypes = ActivityType::with('account')->where(['deleted' => '0'])
            ->orderBy('sort_order', 'asc')->get();
        return view('admin.activity_tpe.list', compact('page_heading', 'activityTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // if (!get_user_permission('activity_types', 'c')) {
        //     return redirect()->route('admin.restricted_page');
        // }
        $page_heading = "Activity Type";
        $mode = "create";
        $id = "";
        $activityType = "";
        $name = "";
        $description = "";
        $account_id = "";
        $logo = "";
        $status="";
        $availbale_for = '';
        $indvidual_name = '';
        $indvidual_logo = '';
        // AccountType::where('id',1)->update(['name' => 'Commercial Centers(SHOPS)']);
        $accounts = AccountType::where(['deleted' => '0'])->get();
        $account_id = AccountType::where(['deleted' => '0'])->first()->id ?? '';
        // dd($accounts);
        return view("admin.activity_tpe.create", compact('page_heading', 'account_id', 'accounts', 'activityType', 'description', 'id', 'name','logo','status','availbale_for','indvidual_name','indvidual_logo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $path = null;
        // if($request->file('logo')){
        //     $extension  = $request->file('logo')->getClientOriginalExtension();
        //     $image_name = time() . '.' . $extension;
        //     $path = $request->file('logo')->storeAs(config('global.activity_image_upload_dir'),$image_name,config('global.upload_bucket'));
        // }
        if($request->file("logo")){
            $response = image_upload($request,'activity','logo');
            if($response['status']){
                $path = $response['link'];
            }
        }

        if ($request->file("image")) {
            $response = image_upload($request, 'activity', 'image');
            if ($response['status']) {
                $banner_image = $response['link'];
            }
        }
        $indvidual_logo_path = null;
        if ($request->file("indvidual_logo")) {
            $response = image_upload($request, 'activity', 'indvidual_logo');
            if ($response['status']) {
                $indvidual_logo_path = $response['link'];
            }
        }
        
        // if($request->file('indvidual_logo')){
        //     $extension  = $request->file('indvidual_logo')->getClientOriginalExtension();
        //     $image_name = time() . '.' . $extension;
        //     $indvidual_logo_path = $request->file('indvidual_logo')->storeAs(config('global.activity_image_upload_dir'),$image_name,config('global.upload_bucket'));
        // }


        $status = "0";
        $message = "";
        $errors = [];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'account_id' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $input = $request->all();

            $luser_name = strtolower($request->name);
            $check_user_name_exist = ActivityType::whereRaw("LOWER(name) = '$luser_name'")
                ->where('account_id', '=', $request->account_id)
                ->where('id', '!=', $request->id)->get()->toArray();
            if ($check_user_name_exist) {
                $status = "0";
                $message = "name should be unique";
                $errors['name'] = "Already exist";
                echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
                die();
            }

            $ins = [
                'name' => $request->name,
                'account_id' => $request->account_id,
                'description' => $request->description ?? '',
                'status' => $request->status,
                'availbale_for'=>$request->availbale_for,
                'indvidual_name'=>$request->indvidual_name
            ];
            if($path != ''){
                $ins['logo'] = $path;
            }
            if($indvidual_logo_path != ''){
                $ins['indvidual_logo'] = $indvidual_logo_path;
            }
            If(!empty($banner_image))
            {
                $ins['banner_image'] = $banner_image;
            }
            

            if ($request->id != "") {
                $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                $user = ActivityType::find($request->id);
                $user->update($ins);

                $status = "1";
                $message = "Activity Type updated succesfully";
            } else {
                $ins['created_at'] = gmdate('Y-m-d H:i:s');
                $activity_tpe_id = ActivityType::create($ins)->id;

                $status = "1";
                $message = "Activity Type added successfully";
            }
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // if (!get_user_permission('activity_types', 'u')) {
        //     return redirect()->route('admin.restricted_page');
        // }
        $page_heading = "Edit Activity Type";
        $activityType = ActivityType::find($id);
        if (!$activityType) {
            abort(404);
        }

        if ($activityType) {
            $name = $activityType->name;
            $description = $activityType->description;
            $account_id = $activityType->account_id;
            $logo = $activityType->logo;
            $status = $activityType->status;
            $availbale_for = $activityType->availbale_for;
            $indvidual_name = $activityType->indvidual_name;
            $indvidual_logo = $activityType->indvidual_logo;
            $banner_image = $activityType->banner_image??'';
            $accounts = AccountType::where(['deleted' => '0'])->get();
            return view("admin.activity_tpe.create", compact('page_heading', 'account_id', 'accounts',
                'name', 'description', 'id','logo','status','availbale_for','indvidual_name','indvidual_logo','banner_image'));
        } else {
            abort(404);
        }
    }


    public function destroy($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $datatb = ActivityType::find($id);
        if ($datatb) {
            $datatb->deleted = 1;
            $datatb->save();
            $status = "1";
            $message = "Activity Type removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }

    public function get_activities(Request $request){

        $activity_types = ActivityType::select('id','name as activity_name')->where(['deleted' => 0,'account_id'=>$request->account_id])->get();
        $html = view("admin.activity_tpe.options", compact('activity_types'))->render();
        return response()->json(['html' => $html],200);
    }

    public function sort(Request $request)
    {
        if ($request->ajax()) {
            $status = 0;
            $message = '';

            $items = $request->items;
            $items = explode(",", $items);
            $sorted = ActivityType::sort_item($items);
            if ($sorted) {
                $status = 1;
            }

            echo json_encode(['status' => $status, 'message' => $message]);

        } else {
            $page_heading = "Sort Activity Type";

            $list = ActivityType::where(['deleted' => 0])->orderBy('sort_order', 'asc')->get();
            $back = url("admin/activity_type");
            return view("admin.sort", compact('page_heading', 'list','back'));
        }
    }

    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (ActivityType::where('id', $request->id)->update(['status' => $request->status])) {
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
}
