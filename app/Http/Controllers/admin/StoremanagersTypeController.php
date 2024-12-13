<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreManagersTypes;
use Illuminate\Http\Request;
use Validator;

class StoremanagersTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Store Managers type";
        $datamain = StoreManagersTypes::get();
        // dd($countries);
        return view('admin.store_managers_type.list', compact('page_heading', 'datamain'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Store Managers type";
        $mode = "create";
        $id = "";
        $prefix = "";
        $name = "";
        $dial_code = "";
        $image = "";
        $active = "1";
        return view("admin.store_managers_type.create", compact('page_heading', 'mode', 'id', 'name', 'dial_code', 'active','prefix'));
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
            $check_exist = StoreManagersTypes::where(['name' => $request->name])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                

                if ($request->id != "") {
                    $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                    $storemanager = StoreManagersTypes::find($request->id);
                    $status = "1";
                    $message = "Store Managers type updated succesfully";
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    $storemanager = new StoreManagersTypes();
                    $status = "1";
                    $message = "Store Managers type added successfully";
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
        $country = StoreManagersTypes::find($id);
        if ($country) {
            $page_heading = "Store Managers type";
            $mode = "edit";
            $id = $country->id;
            $name = $country->name;
            $prefix = $country->prefix;
            $dial_code = $country->dial_code;
            $active = $country->active;
            return view("admin.store_managers_type.create", compact('page_heading', 'mode', 'id', 'name', 'dial_code', 'active','prefix'));
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
        $country = StoreManagersTypes::find($id);
        if ($country) {
            StoreManagersTypes::where('id', $id)->delete();
            $status = "1";
            $message = "Store Managers type removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }
}
