<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IndustryTypes;
use Illuminate\Http\Request;
use Validator;

class IndustryTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Industry Types";
        $industry_types = IndustryTypes::where(['deleted' => 0])->get();
        return view('admin.industry_types.list', compact('page_heading', 'industry_types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Industry Types";
        $mode = "create";
        $id = "";
        $name = "";
        $active = "1";
        $industry_types = [];
        return view("admin.industry_types.create", compact('page_heading', 'industry_types', 'mode', 'id', 'name', 'active'));
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
            $check_exist = IndustryTypes::where(['deleted' => 0, 'name' => $request->name])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
                    'updated_uid' => session("user_id"),
                    'active' => $request->active,
                ];

                if ($request->id != "") {
                    $industry_type = IndustryTypes::find($request->id);
                    $industry_type->update($ins);
                    $status = "1";
                    $message = "Industry Type updated succesfully";
                } else {
                    $ins['created_uid'] = session("user_id");
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    IndustryTypes::create($ins);
                    $status = "1";
                    $message = "Industry Type added successfully";
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
        $industry_type = IndustryTypes::find($id);
        if ($industry_type) {
            $page_heading = "Industry Type ";
            $mode = "edit";
            $id = $industry_type->id;
            $name = $industry_type->name;
            $active = $industry_type->active;
            return view("admin.industry_types.create", compact('page_heading', 'industry_type', 'mode', 'id', 'name', 'active'));
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
        $industry_type = IndustryTypes::find($id);
        if ($industry_type) {
            $industry_type->deleted = 1;
            $industry_type->active = 0;
            $industry_type->updated_at = gmdate('Y-m-d H:i:s');
            $industry_type->updated_uid = session("user_id");
            $industry_type->save();
            $status = "1";
            $message = "Industry Type removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (IndustryTypes::where('id', $request->id)->update(['active' => $request->status])) {
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
            $sorted = IndustryTypes::sort_item($items);
            if ($sorted) {
                $status = 1;
            }

            echo json_encode(['status' => $status, 'message' => $message]);

        } else {
            $page_heading = "Sort Industry Types";

            $list = IndustryTypes::where(['deleted' => 0])->get();

            return view("admin.sort", compact('page_heading', 'list'));
        }
    }
}
