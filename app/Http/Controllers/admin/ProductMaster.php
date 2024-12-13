<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProductMasterModel;
use App\Models\ProductModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class ProductMaster extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Product Master";
        $categories = ProductMasterModel::where(['deleted'=> 0])->orderBy('id', 'DESC')->get();
        
        return view('admin.product_master.list', compact('page_heading', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Product Master";
        $mode = "create";
        $id = "";
        $name = "";
        $parent_id = "";
        $image = "";
        $active = "1";
        $banner_image = "";
        $category = [];
        $categories = ProductMasterModel::get();
        return view("admin.product_master.create", compact('page_heading', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image'));
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
            $check_exist = ProductMasterModel::where(['name' => $request->name,'deleted' => 0])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'active' => $request->active,
                ];

                if($request->file("banner_image")){
                    $response = image_upload($request,'category','banner_image');
                    if($response['status']){
                        $ins['banner_image'] = $response['link'];
                    }
                }
                if ($request->id != "") {
                    $category = ProductMasterModel::find($request->id);
                    $category->update($ins);
                    $status = "1";
                    $message = "Product Master updated succesfully";
                } else {
                    ProductMasterModel::create($ins);
                    $status = "1";
                    $message = "Product Master added successfully";
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
        $category = ProductMasterModel::find($id);
        if ($category) {
            $page_heading = "Product Master";
            $mode = "edit";
            $id = $category->id;
            $name = $category->name;
            $parent_id = $category->parent_id;
            $image = $category->image;
            $active = $category->active;
            $banner_image = "";
            $categories = ProductMasterModel::where('id', '!=', $id)->get();
            return view("admin.product_master.create", compact('page_heading', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image'));
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
        $category = ProductMasterModel::find($id);
        if ($category) {
            $check = ProductModel::where('master_product',$id)->get()->count();
            if($check > 0)
            {
                $status = "0";
                $message = "Unable to delete already using";
            }
            else
            {
               $category->deleted = 1;
               $category->active = 0;
               $category->updated_at = gmdate('Y-m-d H:i:s');
               $category->save();
               $message = "Deleted successfully";
            }
          
            
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (ProductMasterModel::where('id', $request->id)->update(['active' => $request->status])) {
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
