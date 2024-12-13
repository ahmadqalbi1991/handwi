<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Areas;
use App\Models\CountryModel;
use App\Models\Cities;
use App\Models\VendorLocation;
use App\Models\ServiceMainActivity;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class VendorLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        
        $page_heading = "Vendor Locations";
        $own_locations = VendorLocation::where('user_id', $id)->orderBy('id','desc')->paginate(10);

        return view('admin.vendor_locations.list', compact('page_heading', 'own_locations','id'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $user_id)
    {
        
        $dblocation = new VendorLocation();

        $page_heading = "location";
        $mode = "edit";
        $id = $dblocation->id;
        $name = '';//$location->name;
        $location = $dblocation->location;
        $latitude = $dblocation->latitude ??'25.204819';
        $longitude = $dblocation->longitude ??'55.270931';
        $active = '';//$location->active;
        $country_id = '';//$location->country_id;
        $city_id = '';//$location->city_id;
        $countries = [];
        $cities = [];

        $user_id = $user_id;


        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';
        
        
        // $o_data['model'] = view('front_end.partials.location_edit_model', compact('location','activities','selected_activities_id','selected_main_activities'))->render();

        $page_heading = "Location Edit";

        return view("admin.vendor_locations.create", compact('page_heading','dblocation', 'mode', 'id', 'name', 'active', 'country_id', 'countries', 'cities', 'city_id','latitude','longitude','location','user_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $status = "1";
        $message = "";
        $errors = [];

        $location = VendorLocation::where('id',$request->id)->first();
        if(!$location){
            $location = new VendorLocation();
            $location->user_id = $request->user_id;

        }
        $lat_lon = explode(',', $request->location);
        $location->location = $request->txt_location;
        $location->latitude = $lat_lon[0] ?? '';
        $location->longitude = $lat_lon[1] ?? '';
        $location->save();

        echo json_encode(['status' => $status, 'message' => 'Record has been saved.', 'errors' => $errors]);
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
        
        $dblocation = VendorLocation::where('id',$id)->first();
        if ($dblocation) {
            $page_heading = "location";
            $mode = "edit";
            $id = $dblocation->id;
            $name = '';//$location->name;
            $location = $dblocation->location;
            $latitude = $dblocation->latitude ??'25.204819';
            $longitude = $dblocation->longitude ??'55.270931';
            $active = '';//$location->active;
            $country_id = '';//$location->country_id;
            $city_id = '';//$location->city_id;
            $countries = [];
            $cities = [];

            $user_id = $dblocation->user_id;


            $status = "0";
            $message = "";
            $o_data = [];
            $errors = [];
            $redirectUrl = '';
            
            // $o_data['model'] = view('front_end.partials.location_edit_model', compact('location','activities','selected_activities_id','selected_main_activities'))->render();

            $page_heading = "Location Edit";

            return view("admin.vendor_locations.create", compact('page_heading','dblocation', 'mode', 'id', 'name', 'active', 'country_id', 'countries', 'cities', 'city_id','latitude','longitude','location','user_id'));


            // return view('admin.professional.location_form', compact('location','activities','selected_activities_id','selected_main_activities','id','user_id','page_heading'));

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
        $record = VendorLocation::find($id);
        if ($record) {
            if($record->is_default == 1){
                $message = "You cant remove the default location";
            }else{
                $record->delete();
                $status = "1";
                $message = "Record removed successfully";
            }
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    
}
