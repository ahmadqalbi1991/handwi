<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CountriesModel;
use Illuminate\Http\Request;
use Validator;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_heading = "Country";
        $countries = CountriesModel::where('deleted', 0)->get();
        // dd($countries);
        return view('admin.country.list', compact('page_heading', 'countries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Country";
        $mode = "create";
        $id = "";
        $prefix = "";
        $name = "";
        $dial_code = "";
        $image = "";
        $active = "1";
        return view("admin.country.create", compact('page_heading', 'mode', 'id', 'name', 'dial_code', 'active','prefix'));
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
            $check_exist = CountriesModel::where(['countries_nice_name' => $request->name, 'deleted' => 0])->where('countries_id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'countries_nice_name' => $request->name,
                    'countries_phonecode' => $request->dial_code,
                    'country_status' => $request->active,
                ];

                if ($request->id != "") {
                    $country = CountriesModel::find($request->id);
                    $country->update($ins);
                    $status = "1";
                    $message = "Country updated succesfully";
                } else {
                    CountriesModel::create($ins);
                    $status = "1";
                    $message = "Country added successfully";
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
        $country = CountriesModel::find($id);
        if ($country) {
            $page_heading = "Country";
            $mode = "edit";
            $id = $country->countries_id;
            $name = $country->countries_name;
            $dial_code = $country->countries_phonecode;
            $active = $country->country_status;
            return view("admin.country.create", compact('page_heading', 'mode', 'id', 'name', 'dial_code', 'active'));
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
        $country = CountriesModel::find($id);
        if ($country) {
            $country->deleted = 1;
            $country->country_status = 0;
            $country->save();
            $status = "1";
            $message = "Country removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }
}
