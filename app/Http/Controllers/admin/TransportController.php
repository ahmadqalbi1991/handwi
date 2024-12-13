<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IndustryTypes;
use App\Models\Categories;
use Validator;

class TransportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!GetUserPermissions('brand','View')) {
            abort(404);
        }
        $page_heading = "Transport";
        $datamain = Transport::orderBy('id','desc')->
        get();
        
        return view('admin.transport.list', compact('page_heading', 'datamain'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!GetUserPermissions('brand','Create')) {
            abort(404);
        }
        $page_heading = "Transport";
        $mode = "create";
        $id = "";
        $name = "";
        $name_ar = "";
        $industry_type = "";
        $image = "";
        $active = "1";
        $banner_image = "";
        $category = [];
        $industry   = Transport::orderBy('id','desc')->get();
        return view("admin.transport.create", compact('page_heading', 'mode', 'id', 'name', 'industry_type', 'image', 'active', 'banner_image','industry','name_ar'));
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
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $input = $request->all();
            $check_exist = Transport::where(['title' => $request->title])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'title' => $request->title,
                    'description' => $request->description,
                    'url' => $request->url,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
                    'active' => $request->active,
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
                    $brand = Transport::find($request->id);
                    $brand->update($ins);
                    $status = "1";
                    $message = "Transport updated succesfully";
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    Transport::create($ins);
                    $status = "1";
                    $message = "Transport added successfully";
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
        if (!GetUserPermissions('brand','Edit')) {
            abort(404);
        }
        $datamain = Transport::find($id);
        if ($datamain) {
            $page_heading = "Transport";
            $mode = "edit";
            $id = $datamain->id;
            $name = $datamain->name;
            $name_ar = $datamain->name_ar;
            $industry_type = $datamain->category_id;
            $image = $datamain->image;
            $active = $datamain->active;
            $banner_image = $datamain->banner_image;
            $industry   = Categories::where(['deleted' => 0])->get();
            return view("admin.transport.create", compact('page_heading', 'datamain', 'mode', 'id', 'name', 'image', 'active', 'banner_image','industry','industry_type','name_ar'));
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
        $category = Transport::find($id);
        if ($category) {
            Transport::where('id',$id)->delete();
            $status = "1";
            $message = "Transport removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        // dd($request->all());
        $status = "0";
        $message = "";
        if (Transport::where('id', $request->id)->update(['active' => $request->status])) {
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
