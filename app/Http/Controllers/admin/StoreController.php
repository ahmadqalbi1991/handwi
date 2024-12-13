<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CountryModel;
use App\Models\IndustryTypes;
use App\Models\StoreImages;
use App\Models\Stores;
use App\Models\VendorModel;
use Illuminate\Http\Request;
use App\Models\States;
use App\Models\Cities;
use Validator;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vendor = isset($_GET['vendor']) ? $_GET['vendor'] : '';
        $params['vendor'] = $vendor;
        $page_heading = "Stores";
        $stores = Stores::where(['deleted' => 0])->orderBy('updated_at', 'DESC');
        if(!empty($vendor))
        {
        $stores = $stores->where('vendor_id',$vendor);    
        }
        $stores = $stores->get();
        return view('admin.store.list', compact('page_heading', 'stores'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Store";
        $mode = "create";
        $id = "";
        $industry_types = IndustryTypes::where(['deleted' => 0, 'active' => 1])->orderBy('sort_order', 'asc')->get();
        $countries = CountryModel::where(['deleted' => 0, 'active' => 1])->orderBy('name', 'asc')->get();
        $vendors = VendorModel::where(['role'=>'3','deleted'=>'0'])->get();
        $states = [];
        $cities = [];
        $vendorid = !empty($_GET['vendor']) ? $_GET['vendor']: '';
      
        return view("admin.store.create", compact('page_heading', 'mode', 'id', 'industry_types', 'countries', 'vendors','states','cities','vendorid'));
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
        $rules =[
            'vendor_id' => 'required',
            'industry_type' => 'required',
            'store_name' => 'required',
            'business_email' => 'required',
            'dial_code' => 'required',
            'mobile' => 'required',
            'description' => 'required',
            'txt_location' => 'required',
            'address_line1' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'zip' => 'required',
            'logo' => 'required|mimes:jpg,png,gif,jpeg',
            'cover_image' => 'required|mimes:jpg,png,gif,jpeg',
            'license_number' => 'required',
            'license_doc' => 'required|mimes:jpg,png,jpeg,pdf,doc,docx',
            'vat_cert_number' => 'required',
            'vat_cert_doc' => 'required|mimes:jpg,png,jpeg,pdf,doc,docx',
        ];
        if ($request->id != "") {
            $rules['logo'] ='mimes:jpg,png,gif,jpeg';
            $rules['cover_image'] ='mimes:jpg,png,gif,jpeg';
            $rules['license_doc'] ='mimes:jpg,png,jpeg,pdf,doc,docx';
            $rules['vat_cert_doc'] ='mimes:jpg,png,jpeg,pdf,doc,docx';
        }
        $validator = Validator::make($request->all(),
        $rules,
            [
                'logo.mimes' => 'should be in format (jpg,png,gif,jpeg)',
                'cover_image.mimes' => 'should be in format (jpg,png,gif,jpeg)',
                'license_doc.mimes' => 'should be in format (jpg,png,jpeg,pdf,doc,docx)',
                'vat_cert_doc.mimes' => 'should be in format (jpg,png,jpeg,pdf,doc,docx)',
                'country_id.required' => 'Country required',
                'state_id.required' => 'State required',
                'city_id.required' => 'City required',
                'txt_location.required' => 'Location required',

            ]
        );
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $lat = "";
            $long = "";
            if ($request->location) {
                $location = explode(",", $request->location);
                $lat = $location[0];
                $long = $location[1];
            }
            $ins = [
                'vendor_id' => $request->vendor_id,
                'industry_type' => $request->industry_type,
                'store_name' => $request->store_name,
                'business_email' => $request->business_email,
                'dial_code' => $request->dial_code,
                'mobile' => $request->mobile,
                'description' => $request->description,
                'location' => $request->txt_location,
                'address_line1' => $request->address_line1,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'zip' => $request->zip,
                'latitude' => $lat,
                'longitude' => $long,
                'license_number' => $request->license_number,
                'vat_cert_number' => $request->license_number,
                'address_line2' => $request->address_line2,
                'commission' => $request->commission,

            ];

            if ($request->file("logo")) {
                $response = image_upload($request, 'store', 'logo');
                if ($response['status']) {
                    $ins['logo'] = $response['link'];
                }
            }
            if ($request->file("cover_image")) {
                $response = image_upload($request, 'store', 'cover_image');
                if ($response['status']) {
                    $ins['cover_image'] = $response['link'];
                }
            }
            if ($request->file("license_doc")) {
                $response = image_upload($request, 'store', 'license_doc');
                if ($response['status']) {
                    $ins['license_doc'] = $response['link'];
                }
            }
            if ($request->file("vat_cert_doc")) {
                $response = image_upload($request, 'store', 'vat_cert_doc');
                if ($response['status']) {
                    $ins['vat_cert_doc'] = $response['link'];
                }
            }
            $banners = $request->file("banners");

            $banner_images = [];
            if ($banners) {
                foreach ($banners as $ikey => $img) {
                    $response = file_save($img, 'store');
                    if ($response['status']) {
                        $imageName = $response['link'];
                        $banner_images[$ikey] = $imageName;
                    }
                }
            }
            if ($request->id != "") {
                $ins['updated_uid'] = session("user_id");
                $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                $store = Stores::find($request->id);
                $store->update($ins);
                // StoreImages::where('store_id', $request->id)->delete();
                foreach ($banner_images as $bimg) {
                    $b['image'] = $bimg;
                    $b['store_id'] = $request->id;
                    StoreImages::insert($b);
                }
                $status = "1";
                $message = "Store updated succesfully";
            } else {
                $ins['created_uid'] = session("user_id");
                $ins['created_at'] = gmdate('Y-m-d H:i:s');
                $str_id = Stores::create($ins)->id;
                foreach ($banner_images as $bimg) {
                    $b['image'] = $bimg;
                    $b['store_id'] = $str_id;
                    StoreImages::insert($b);
                }
                $status = "1";
                $message = "Store added successfully";
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
        $store = Stores::find($id);
        if ($store) {
            $page_heading = "Store ";
            $mode = "edit";
            $id = $store->id;
            $industry_types = IndustryTypes::where(['deleted' => 0, 'active' => 1])->orderBy('sort_order', 'asc')->get();
            $countries = CountryModel::where(['deleted' => 0, 'active' => 1])->orderBy('name', 'asc')->get();
            $vendors = VendorModel::where(['role'=>'3','deleted'=>'0'])->get();
            $images = StoreImages::where('store_id', $id)->get();

            $states = States::where(['deleted' => 0, 'active' => 1, 'country_id' => $store->country_id])->orderBy('name', 'asc')->get();

            $cities = Cities::where(['deleted' => 0, 'active' => 1, 'state_id' => $store->state_id])->orderBy('name', 'asc')->get();

            return view("admin.store.create", compact('page_heading', 'mode', 'id', 'industry_types', 'countries', 'vendors', 'store', 'images','states','cities'));
        } else {
            abort(404);
        }
    }
    public function delete_image($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $img =  StoreImages::find($id);
        if ($img) {
            $img->delete();
            $status = "1";
            $message = "Image removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

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
        $store = Stores::find($id);
        if ($store) {
            $store->deleted = 1;
            $store->active = 0;
            $store->updated_at = gmdate('Y-m-d H:i:s');
            $store->updated_uid = session("user_id");
            $store->save();
            $status = "1";
            $message = "Store removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (Stores::where('id', $request->id)->update(['active' => $request->status])) {
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
    public function verify(Request $request)
    {
        $status = "0";
        $message = "";
        if (Stores::where('id', $request->id)->update(['verified' => $request->status])) {
            $status = "1";
            $msg = "Successfully verified";
            if (!$request->status) {
                $msg = "Successfully updated";
            }
            $message = $msg;
        } else {
            $message = "Something went wrong";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    public function load_vendor(REQUEST $request){
      $status = "0";
      $message = "";
      $o_data  = [];

      $vendor_id = $request->vendor_id;
      $store_list = Stores::where(['active'=>1,'deleted'=>0,'vendor_id'=>$vendor_id])->get();

      if($store_list->count() > 0){
        $status = "1";
        $message = "data fetched succesfully";
        $o_data  = $store_list->toArray();
      }else{
        $message = "no data to show";
      }
      echo json_encode(['status'=>$status,'message'=>$message,'oData'=>$o_data]);
    }

    
    public function get_by_vendor(Request $request)
    {
        $stores = Stores::select('id','store_name')->where(['deleted' => 0,'active'=>1,'vendor_id'=>$request->id])->get();
        echo json_encode(['stores' => $stores]);
    }
}
