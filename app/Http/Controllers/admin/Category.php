<?php

namespace App\Http\Controllers\Admin;

use App\Models\Categories;
use App\Models\ActivityType;
use App\Models\AccountType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class Category extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Category";
        $categories = Categories::with('activity')->where(['deleted' => 0])->orderBy('id','desc')->get();
        foreach ($categories as $key => $val) {
            $prnt = Categories::where(['id' => $val->parent_id])->first();
            $categories[$key]->parent_name = isset($prnt->name) ? $prnt->name : '';
        }
        return view('admin.category.list', compact('page_heading', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        $page_heading = "Category";
        $mode = "create";
        $id = "";
        $name = "";
        $parent_id = "";
        $image = "";
        $active = "1";
        $banner_image = "";
        $activity_id = "";
        $category = [];
        $activity_types = ActivityType::select('id','name as activity_name')->where(['deleted' => 0, 'account_id' => AccountType::COMMERCIAL_CENTER])->get();
        $categories = Categories::where(['deleted' => 0, 'parent_id' => 0])->get();
        return view("admin.category.create", compact('page_heading', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image','activity_types','activity_id'));
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
            $check_exist = Categories::where(['deleted' => 0, 'name' => $request->name, 'parent_id' => $request->parent_id])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
                    'updated_uid' => session("user_id"),
                    'parent_id' => $request->parent_id ?? 0,
                    'active' => $request->active,
                    'activity_id' => $request->activity_id,
                ];

                if($request->file("image")){
                    $response = image_upload($request,'category','image');
                    if($response['status']){
                        $ins['image'] = $response['link'];
                    }
                }
                if($request->file("banner_image")){
                    $response = image_upload($request,'category','banner_image');
                    if($response['status']){
                        $ins['banner_image'] = $response['link'];
                    }
                }
                if ($request->id != "") {
                    $category = Categories::find($request->id);
                    $category->update($ins);
                    $status = "1";
                    $message = "Category updated succesfully";
                } else {
                    $ins['created_uid'] = session("user_id");
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    Categories::create($ins);
                    $status = "1";
                    $message = "Category added successfully";
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
        $category = Categories::find($id);
        if ($category) {
            $page_heading = "Category ";
            $mode = "edit";
            $id = $category->id;
            $name = $category->name;
            $parent_id = $category->parent_id;
            $image = $category->image;
            $active = $category->active;
            $banner_image = $category->banner_image;
            $activity_id = $category->activity_id;
            $activity_types = ActivityType::select('id','name as activity_name')->where(['deleted' => 0, 'account_id' => AccountType::COMMERCIAL_CENTER])->get();
            $categories = Categories::where(['deleted' => 0, 'parent_id' => 0])->where('id', '!=', $id)->where('activity_id',$activity_id)->get();
            return view("admin.category.create", compact('page_heading', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image','activity_types','activity_id'));
        } else {
            abort(404);
        }
    }
    public function get_categories_by_activity_id(Request $request){

        $activity_id = $request->activity_id;
        $category_list = [];
        $cat_view = '';
        if($activity_id){
            $categories = Categories::where(['deleted' => 0, 'parent_id' => 0])->where('activity_id',$activity_id)->get();
            $parent_id = '';
            
            $cat_view = view('admin.category.cat_options',compact('categories','parent_id'))->render();

        }
        return  json_encode(['status' => '1','cat_view'=>$cat_view, 'message' => '']);

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
        $category = Categories::find($id);
        if ($category) {
            $category->deleted = 1;
            $category->active = 0;
            $category->updated_at = gmdate('Y-m-d H:i:s');
            $category->updated_uid = session("user_id");
            $category->save();
            $status = "1";
            $message = "Category removed successfully";
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
            if($request->acitivity_id){
                $list = Categories::with('parent')->where(['deleted' => 0,'activity_id' => $request->acitivity_id])->orderBy('sort_order','asc')->get();
            }else{
                $list = Categories::with(['activity','parent'])->where(['deleted' => 0])->orderBy('sort_order','asc')->get();
            }
            //printr($list->toArray()); exit;

            return view("admin.sort", compact('page_heading', 'list'));
        }
    }
    
}
