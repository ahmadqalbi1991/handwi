<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brands;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IndustryTypes;
use App\Models\ActivityType;
use App\Models\AccountType;
use Validator;

class Brand extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Brand";
        $datamain = Brands::select('brand.*','brand.name as name','industry_types.name as industry')
        ->where(['brand.deleted' => 0])
        ->leftjoin('industry_types','industry_types.id','=','brand.industry_type')
        ->get();
        
        return view('admin.brand.list', compact('page_heading', 'datamain'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Brand";
        $mode = "create";
        $id = "";
        $name = "";
        $industry_type = "";
        $image = "";
        $active = "1";
        $banner_image = "";
        $category = [];
        $activity_id = "";

        $industry   = IndustryTypes::where(['deleted' => 0])->get();
        $activity_types = ActivityType::select('id','name as activity_name')->where(['deleted' => 0, 'account_id' => AccountType::COMMERCIAL_CENTER])->get();
        return view("admin.brand.create", compact('page_heading', 'mode', 'id', 'name', 'industry_type', 'image', 'active', 'banner_image','industry','activity_types','activity_id'));
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
            $check_exist = Brands::where(['deleted' => 0, 'name' => $request->name, 'industry_type' => $request->industry_type])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
                    'industry_type' => $request->industry_type ?? 0,
                    'active' => $request->active,
                    'activity_id' => $request->activity_id,
                ];

                if($request->file("image")){
                    $response = image_upload($request,'brand','image');
                    if($response['status']){
                        $ins['image'] = $response['link'];
                    }
                }
                if($request->file("banner_image")){
                    $response = image_upload($request,'brand','banner_image');
                    if($response['status']){
                        $ins['banner_image'] = $response['link'];
                    }
                }
                if ($request->id != "") {
                    $brand = Brands::find($request->id);
                    $brand->update($ins);
                    $status = "1";
                    $message = "Brand updated succesfully";
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    Brands::create($ins);
                    $status = "1";
                    $message = "Brand added successfully";
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
        $datamain = Brands::find($id);
        if ($datamain) {
            $page_heading = "Brand ";
            $mode = "edit";
            $id = $datamain->id;
            $name = $datamain->name;
            $industry_type = $datamain->industry_type;
            $image = $datamain->image;
            $active = $datamain->active;
            $banner_image = $datamain->banner_image;
            $activity_id = $datamain->activity_id;
            $industry   = IndustryTypes::where(['deleted' => 0])->get();
            $activity_types = ActivityType::select('id','name as activity_name')->where(['deleted' => 0, 'account_id' => AccountType::COMMERCIAL_CENTER])->get();
            return view("admin.brand.create", compact('page_heading', 'datamain', 'mode', 'id', 'name', 'image', 'active', 'banner_image','industry','industry_type','activity_types','activity_id'));
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
        $category = Brands::find($id);
        if ($category) {
            $category->deleted = 1;
            $category->active = 0;
            $category->updated_at = gmdate('Y-m-d H:i:s');
            //$category->updated_uid = session("user_id");
            $category->save();
            $status = "1";
            $message = "Brand removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (Categories::where('id', $request->id)->update(['active' => $request->status])) {
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
    public function sort(Request $request)
    {
        if ($request->ajax()) {
            $status = 0;
            $message = '';

            $items = $request->items;
            $items = explode(",", $items);
            $sorted = Categories::sort_item($items);
            if ($sorted) {
                $status = 1;
            }

            echo json_encode(['status' => $status, 'message' => $message]);

        } else {
            $page_heading = "Sort Categories";

            $list = Categories::where(['deleted' => 0, 'parent_id' => 0])->get();

            return view("admin.sort", compact('page_heading', 'list'));
        }
    }
}
