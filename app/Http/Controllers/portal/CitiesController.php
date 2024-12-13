<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use App\Models\Cities;
use App\Models\CountryModel;
use App\Models\States;
use Illuminate\Http\Request;
use Validator;

class CitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Cities";
        $cities = Cities::where(['deleted' => 0])->get();
        return view('admin.cities.list', compact('page_heading', 'cities'));
    }
    public function get_by_state(Request $request)
    {
        $cities = Cities::select('id', 'name')->where(['deleted' => 0, 'active' => 1, 'state_id' => $request->id])->get();
        echo json_encode(['cities' => $cities]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Cities";
        $mode = "create";
        $id = "";
        $name = "";
        $country_id = "";
        $state_id = "";
        $active = "1";
        $states = [];
        $countries = CountryModel::where(['deleted' => 0, 'active' => 1])->orderBy('name', 'asc')->get();
        return view("admin.cities.create", compact('page_heading', 'countries', 'mode', 'id', 'name', 'active', 'country_id', 'state_id','states'));
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
            'state_id' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $check_exist = Cities::where(['deleted' => 0, 'name' => $request->name, 'country_id' => $request->country_id, 'state_id' => $request->state_id])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'country_id' => $request->country_id,
                    'state_id' => $request->state_id,
                    'active' => $request->active,
                ];

                if ($request->id != "") {
                    $ins['updated_uid'] = session("user_id");
                    $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                    $cities = Cities::find($request->id);
                    $cities->update($ins);
                    $status = "1";
                    $message = "City updated succesfully";
                } else {
                    $ins['created_uid'] = session("user_id");
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    Cities::create($ins);
                    $status = "1";
                    $message = "City added successfully";
                }
            } else {
                $status = "0";
                $message = "City added already";
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
        $cities = Cities::find($id);
        if ($cities) {
            $page_heading = "Cities";
            $mode = "edit";
            $id = $cities->id;
            $name = $cities->name;
            $active = $cities->active;
            $country_id = $cities->country_id;
            $state_id = $cities->state_id;
            $countries = CountryModel::where(['deleted' => 0, 'active' => 1])->orderBy('name', 'asc')->get();
            $states = States::where(['deleted' => 0, 'active' => 1, 'country_id' => $country_id])->orderBy('name', 'asc')->get();

            return view("admin.cities.create", compact('page_heading', 'mode', 'id', 'name', 'active', 'country_id', 'countries', 'states', 'state_id'));
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
        $cities = Cities::find($id);
        if ($cities) {
            $cities->deleted = 1;
            $cities->active = 0;
            $cities->updated_at = gmdate('Y-m-d H:i:s');
            $cities->updated_uid = session("user_id");
            $cities->save();
            $status = "1";
            $message = "City removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (Cities::where('id', $request->id)->update(['active' => $request->status])) {
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
