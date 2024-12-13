<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use App\Models\CountryModel;
use App\Models\States;
use Illuminate\Http\Request;
use Validator;

class StatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "States";
        $states = States::where(['deleted' => 0])->get();
        return view('admin.states.list', compact('page_heading', 'states'));
    }
    public function get_by_country(Request $request)
    {
        $states = States::select('id','name')->where(['deleted' => 0,'active'=>1,'country_id'=>$request->id])->get();
        echo json_encode(['states' => $states]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "States";
        $mode = "create";
        $id = "";
        $name = "";
        $country_id = "";
        $active = "1";
        $countries = CountryModel::where(['deleted' => 0, 'active' => 1])->orderBy('name', 'asc')->get();
        return view("admin.states.create", compact('page_heading', 'countries', 'mode', 'id', 'name', 'active', 'country_id'));
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
        $errors = [];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'country_id' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $check_exist = States::where(['deleted' => 0, 'name' => $request->name, 'country_id' => $request->country_id])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'country_id' => $request->country_id,
                    'active' => $request->active,
                ];

                if ($request->id != "") {
                    $ins['updated_uid'] = session("user_id");
                    $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                    $states = States::find($request->id);
                    $states->update($ins);
                    $status = "1";
                    $message = "State updated succesfully";
                } else {
                    $ins['created_uid'] = session("user_id");
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    States::create($ins);
                    $status = "1";
                    $message = "State added successfully";
                }
            } else {
                $status = "0";
                $message = "State added already";
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
        $states = States::find($id);
        if ($states) {
            $page_heading = "States";
            $mode = "edit";
            $id = $states->id;
            $name = $states->name;
            $active = $states->active;
            $country_id = $states->country_id;
            $countries = CountryModel::where(['deleted' => 0, 'active' => 1])->orderBy('name', 'asc')->get();
            return view("admin.states.create", compact('page_heading', 'mode', 'id', 'name', 'active', 'country_id', 'countries'));
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
        $states = States::find($id);
        if ($states) {
            $states->deleted = 1;
            $states->active = 0;
            $states->updated_at = gmdate('Y-m-d H:i:s');
            $states->updated_uid = session("user_id");
            $states->save();
            $status = "1";
            $message = "State removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (States::where('id', $request->id)->update(['active' => $request->status])) {
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
}
