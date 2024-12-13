<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cities;
use App\Models\City;
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
        $cities = City::where(['deleted' => 0, 'city_language_code' => 1])->with('country')->get();

        return view('admin.cities.list', compact('page_heading', 'cities'));
    }
    public function get_by_state(Request $request)
    {
        $cities = City::select('id', 'name')->where(['deleted' => 0, 'active' => 1, 'state_id' => $request->id])->get();
        echo json_encode(['cities' => $cities]);
    }
     public function get_by_country(Request $request)
    {
        $cities = City::select('id', 'name')->where(['deleted' => 0, 'active' => 1, 'country_id' => $request->id])->get();
        echo json_encode(['cities' => $cities]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "City";
        $mode = "create";
        $id = "";
        $name = "";
        $country_id = "";
        $state_id = "";
        $active = "1";
        $states = [];
        $countries = CountryModel::where(['deleted' => 0, 'ountry_status' => 1])->orderBy('name', 'asc')->get();
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
            //'state_id' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $check_exist = City::where(['deleted' => 0, 'city_name' => $request->name, 'city_country_id' => $request->country_id])->where('city_id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'city_name' => $request->name,
                    'city_country_id' => $request->country_id,
                    'city_status' => $request->active,
                    'city_language_code' => 1
                ];

                if ($request->id != "") {
                    $cities = City::find($request->id);
                    $cities->update($ins);
                    $status = "1";
                    $message = "City updated succesfully";
                } else {
                    City::create($ins);
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
        $cities = City::find($id);
        if ($cities) {
            $page_heading = "City";
            $mode = "edit";
            $id = $cities->city_id;
            $name = $cities->city_name;
            $active = $cities->city_status;
            $country_id = $cities->city_country_id;
            $countries = CountryModel::where(['deleted' => 0, 'country_status' => 1, 'country_language_code' => 1])->orderBy('country_name', 'asc')->get();

            return view("admin.cities.create", compact('page_heading', 'mode', 'id', 'name', 'active', 'country_id', 'countries'));
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
        $cities = City::find($id);
        if ($cities) {
            $cities->deleted = 1;
            $cities->city_status = 0;
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
        if (City::where('city_id', $request->id)->update(['city_status' => $request->status])) {
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
