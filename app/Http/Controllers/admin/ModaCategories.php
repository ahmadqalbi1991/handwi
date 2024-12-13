<?php

namespace App\Http\Controllers\Admin;

use App\Models\ModaSubCategories;
use App\Models\ModaMainCategories;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IndustryTypes;
use Validator;

class ModaCategories extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Moda Category";
        $datamain = ModaSubCategories::select('moda_sub_categories.*','moda_main_categories.name as main_category')
        ->where(['moda_sub_categories.deleted' => 0])
        ->leftjoin('moda_main_categories','moda_main_categories.id','=','moda_sub_categories.main_category')->orderby('sort_order','asc')->get();
        
        return view('admin.moda_category.list', compact('page_heading', 'datamain'));
    }
    public function moda_sub_category_by_category(Request $request)
    {
        $list = ModaSubCategories::where(['deleted' => 0,'active'=>1,'main_category'=>$request->id])->orderby('sort_order','asc')->get();
            foreach ($list as $key => $val) {
                $gender = $val->gender == 1 ? 'Male' : 'Female';
                $list[$key]->name = $val->name . '(' . $gender.')';
            }
        echo json_encode(['list' => $list]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Moda Category";
        $mode = "create";
        $id = "";
        $name = "";
        $gender = "";
        $main_category = "";
        $image = "";
        $active = "1";
        $main_categories= ModaMainCategories::get();
        return view("admin.moda_category.create", compact('page_heading', 'mode', 'id', 'name', 'gender', 'image', 'active', 'main_categories','main_category'));
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
            $check_exist = ModaSubCategories::where(['deleted' => 0, 'name' => $request->name, 'main_category' => $request->main_category,'gender'=>$request->gender])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'main_category' => $request->main_category,
                    'gender' => $request->gender,
                    'active' => $request->active,
                ];

                if($request->file("image")){
                    $response = image_upload($request,'moda_category','image');
                    if($response['status']){
                        $ins['image'] = $response['link'];
                    }
                }
               
                if ($request->id != "") {
                    $category = ModaSubCategories::find($request->id);
                    $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                    $category->update($ins);
                    $status = "1";
                    $message = "Moda Category updated succesfully";
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    $ins['sort_order'] = 9999;
                    $ins['active'] = 1;
                    ModaSubCategories::create($ins);
                    $status = "1";
                    $message = "Moda Category added successfully";
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $datamain = ModaSubCategories::find($id);
        if ($datamain) {
            $page_heading = "Category ";
            $mode = "edit";
            $id = $datamain->id;
            $name = $datamain->name;
            $gender = $datamain->gender;
            $main_category = $datamain->main_category;
            $image = $datamain->image;
            $active =$datamain->active;
            $main_categories= ModaMainCategories::get();

            return view("admin.moda_category.create", compact('page_heading', 'mode', 'id', 'name', 'gender', 'image', 'active', 'main_categories','main_category'));
        } else {
            abort(404);
        }
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
        $category = ModaSubCategories::find($id);
        if ($category) {
            $category->deleted = 1;
            $category->active = 0;
            $category->updated_at = gmdate('Y-m-d H:i:s');
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
        if (ModaSubCategories::where('id', $request->id)->update(['active' => $request->status])) {
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
            $sorted = ModaSubCategories::sort_item($items);
            if ($sorted) {
                $status = 1;
            }
            echo json_encode(['status' => $status, 'message' => $message]);
        } else {
            $page_heading = "Sort Categories";
            $list = ModaSubCategories::select('moda_sub_categories.*','moda_main_categories.name as main_category')
            ->where(['moda_sub_categories.deleted' => 0])
            ->leftjoin('moda_main_categories','moda_main_categories.id','=','moda_sub_categories.main_category')->orderby('sort_order','asc')->get();
            foreach($list as $key =>$val){
                $gender = $val->gender==1 ? 'Male' :'Female';
                $list[$key]->name = $val->main_category.'->'.$val->name.'->'.$gender;
            }
            return view("admin.sort", compact('page_heading', 'list'));
        }
    }
}
