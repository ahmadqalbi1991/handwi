<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminDesignation;
use Illuminate\Http\Request;
use Validator;

class AdminUserDesignation extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Admin User Designation";
        $datamain = AdminDesignation::where(['is_deletd'=>0])->get();
        // dd($countries);
        return view('admin.admin_designation.list', compact('page_heading', 'datamain'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Admin User Designation";
        $mode = "create";
        $id = "";
        $prefix = "";
        $name = "";
        $dial_code = "";
        $image = "";
        $active = "1";
        return view("admin.admin_designation.create", compact('page_heading', 'mode', 'id', 'name', 'dial_code', 'active','prefix'));
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
            $check_exist = AdminDesignation::where(['name' => $request->name,'is_deletd' => 0])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                

                if ($request->id != "") {
                    $storemanager = AdminDesignation::find($request->id);
                    $status = "1";
                    $message = "Admin User Designation updated succesfully";
                } else {
                    $storemanager = new AdminDesignation();
                    $status = "1";
                    $message = "Admin User Designation added successfully";
                }
                $storemanager->name = $request->name;
                $storemanager->save();

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
        $datamain = AdminDesignation::find($id);
        if ($datamain) {
            $page_heading = "Admin User Designation";
            $mode = "edit";
            $id = $datamain->id;
            $name = $datamain->name;
            return view("admin.admin_designation.create", compact('page_heading',  'id', 'name'));
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
        $datamain = AdminDesignation::find($id);
        if ($datamain) {
            $datamain->is_deletd = 1;
            $datamain->save();
            $status = "1";
            $message = "Admin User Designation type removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }
}
