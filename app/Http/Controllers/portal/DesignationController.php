<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designations;
use Validator;

class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Designation";
        $records = Designations::where('user_id', auth()->user()->id)->where('user_type', 3)->get();
        return view('vendor.designations.list', compact('page_heading', 'records'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Designation";
        return view("vendor.designations.create", compact('page_heading'));
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
            $message = "Please fill all required fields";
            $errors = $validator->messages();
        } else {

            $check_exist = Designations::where(['designation' => $request->name])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {

                if ($request->id != "" && $request->id > 0) {

                    $storemanager = Designations::find($request->id);
                    $storemanager->updated_at = gmdate('Y-m-d H:i:s');
                    $status = "1";
                    $message = "Designation updated successfully";
                } else {
                    $storemanager = new Designations();
                    $storemanager->created_at = gmdate('Y-m-d H:i:s');
                    $status = "1";
                    $message = "Designation added successfully";
                }

                $storemanager->user_id = auth()->user()->id;
                $storemanager->user_type = 3;
                $storemanager->designation = $request->name;
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
        $record = Designations::where('user_id',auth()->user()->id)->where('id',$id)->first();

        if ($record) {
            $page_heading = "Designation";
            $mode = "edit";
            return view("vendor.designations.create", compact('page_heading', 'mode', 'record'));
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
        if (Designations::find($id)) {
            Designations::where('id', $id)->delete();
            $status = "1";
            $message = "Designation removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }
}
