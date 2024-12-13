<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cities;
use App\Models\CountryModel;
use App\Models\Area;

use Illuminate\Http\Request;
use Validator;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Areas";
        $Areas = Area::get();
        return view('admin.areas.list', compact('page_heading', 'Areas'));
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
        $page_heading = "Areas";
        $mode = "create";
        $id = "";
        $name = "";
        $country_id = "";
        $city_id = "";
        $active = "1";
        $states = [];
        $countries = CountryModel::where(['deleted' => 0, 'active' => 1])->orderBy('name', 'asc')->get();
        $cities = Cities::where(['deleted' => 0, 'active' => 1])->orderBy('name', 'asc')->get();
        return view("admin.areas.create", compact('page_heading', 'countries','cities', 'mode', 'id', 'name', 'active', 'country_id', 'city_id','states'));
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
            $check_exist = Area::where(['name' => $request->name, 'country_id' => $request->country_id, 'city_id' => $request->city_id])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'country_id' => $request->country_id,
                    'city_id' => $request->city_id??0,
                    'status' => $request->active,
                ];
                if ($request->id != "") {
                    // $ins['updated_uid'] = session("user_id");
                    $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                    $Area = Area::find($request->id);
                    $Area->name = $request->name;
                    $Area->country_id = $request->country_id;
                    $Area->city_id = $request->city_id;
                    $Area->status = $request->active;
                    $Area->save();
                    $status = "1";
                    $message = "Area updated succesfully";
                } else {
                    // $ins['created_uid'] = session("user_id");
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    $Area = new Area();
                    $Area->name = $request->name;
                    $Area->country_id = $request->country_id;
                    $Area->city_id = $request->city_id;
                    $Area->status = $request->active;
                    $Area->save();
                    $status = "1";
                    $message = "Area added successfully";
                }
            } else {
                $status = "0";
                $message = "Area added already";
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
        $area = Area::find($id);
        if ($area) {
            $page_heading = "Cities";
            $mode = "edit";
            $id = $area->id;
            $name = $area->name;
            $active = $area->status;
            $country_id = $area->country_id;
            $city_id = $area->city_id;
            $countries = CountryModel::where(['deleted' => 0, 'active' => 1])->orderBy('name', 'asc')->get();
            $cities = Cities::where(['deleted' => 0, 'active' => 1, 'country_id' => $country_id])->orderBy('name', 'asc')->get();

            return view("admin.areas.create", compact('page_heading', 'mode', 'id', 'name', 'active', 'country_id', 'countries', 'cities', 'city_id'));
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
        $area = Area::find($id);
        if ($area) {
            $area->delete();
            $status = "1";
            $message = "Area removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (Area::where('id', $request->id)->update(['status' => $request->status])) {
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