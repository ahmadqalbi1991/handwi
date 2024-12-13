<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\BuildingTypes;
use Illuminate\Http\Request;
use Validator;
use DB;


class BuildingTypeController extends Controller
{
    

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Building Types";
        $buildingTypes = BuildingTypes::where(['deleted' => 0])->get();

        return view('admin.building_types.list', compact('page_heading', 'buildingTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Building Types";
        $mode = "create";
        $id = "";
        $name = "";
        $active = "1";
        $description = "";
        
        
        return view("admin.building_types.create", compact('page_heading', 'mode', 'id', 'name', 'active', 'description'));
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
            $check_exist = BuildingTypes::where(['deleted' => 0, 'name' => $request->name])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name'            => $request->name,
                    'updated_at'      => gmdate('Y-m-d H:i:s'),
                    'description'     => $request->description,
                    'active'          => $request->active,
                ];
               
                if ($request->id != "") {
                    $category = BuildingTypes::find($request->id);
                    $category->update($ins);
                    $status = "1";
                    $message = "Building Type updated succesfully";
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    $buildingType =  BuildingTypes::create($ins);
                    $status = "1";
                    $message = "Building Type added successfully";
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
        $buildingType = BuildingTypes::find($id);
        
        if($buildingType){
            $page_heading   = "Building Type";
            $mode           = "edit";
            $id             = $buildingType->id;
            $name           = $buildingType->name;
            $active         = $buildingType->active;
            $description    = $buildingType->description;
            
            return view("admin.building_types.create", compact('page_heading', 'buildingType', 'mode', 'id', 'name', 'active', 'description'));
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
        $buildingType = BuildingTypes::find($id);
        if ($buildingType) {
            $buildingType->deleted = 1;
            $buildingType->active = 0;
            $buildingType->updated_at = gmdate('Y-m-d H:i:s');
            $buildingType->save();
            $status = "1";
            $message = "Building Type removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (BuildingTypes::where('id', $request->id)->update(['active' => $request->status])) {
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
            $sorted = BuildingTypes::sort_item($items);
            if ($sorted) {
                $status = 1;
            }

            echo json_encode(['status' => $status, 'message' => $message]);

        } else {
            $page_heading = "Sort Categories";

            $list = BuildingTypes::where(['deleted' => 0, 'parent_id' => 0])->get();

            return view("admin.sort", compact('page_heading', 'list'));
        }
    }
}
