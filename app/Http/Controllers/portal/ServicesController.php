<?php

namespace App\Http\Controllers\Portal;

use App\Models\ServiceCategories;
use App\Models\Service;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Validator;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Services";
        $params['user_id']  = session("user_id");
//        $service_categories = ServiceCategories::all();
        $categories = Service::getAllServices($params);
        foreach($categories as $key=>$cat)
        {
            $categories[$key]->categories_selected = DB::table('service_category_selected')
                ->join('service_category','service_category.id','=','service_category_selected.category_id')
                ->where('service_id', '=', $cat->id)->get();
        }
        return view('portal.service.list', compact('page_heading', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Services";
        $mode = "create";
        $id = "";
        $name = "";
        $parent_id = "";
        $image = "";
        $active = "1";
        $banner_image = "";
        $category = [];
        $description = "";
        $category       = '';
        $serviceprice       = "";
        $service_type = "";
        $contract_type = "";
        $included = '';
        $building_type = "";
        $categories = ServiceCategories::where(['deleted' => 0, 'parent_id' => 0])->get();
        return view("portal.service.create", compact('page_heading', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image','description','category','serviceprice','service_type','contract_type','included','building_type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//        dd($request->all());
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
            $check_exist = Service::where(['deleted' => 0, 'name' => $request->name])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
//                    'service_vendor_user_id'=>session("user_id"),
                'included' =>$request->included_text,
                    'building_type' =>$request->building_type,
                    'service_user_id'=>session("user_id"),
                    'name'            => $request->name,
                    'updated_at'      => gmdate('Y-m-d H:i:s'),
//                    'category'        => $request->category ?? 0,
                    'service_price'   => $request->price ?? 0,
                    'description'     => $request->description,
                    'active'          => $request->active,
                ];

                if($request->file("image")){
                    $response = image_save($request,config('global.service_image_upload_dir'),'image');
                    if($response['status']){
                        $ins['image'] = $response['link'];
                    }
                }

                if ($request->id != "") {
                    $category = Service::find($request->id);
                    $category->update($ins);
                    $status = "1";
                    $message = "Services updated succesfully";
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    Service::create($ins);
                    $status = "1";
                    $message = "Services added successfully";
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
        $service = Service::find($id);

        if ($service) {
            $page_heading   = "Services";
            $mode           = "edit";
            $serviceprice   = $service->service_price;
            $id             = $service->id;
            $name           = $service->name;
            $parent_id      = $service->parent_id;
            $image          = $service->image;
            $active         = $service->active;
            $description    = $service->description;
            $category       = $service->category;
            $service_type = $service->service_type;
            $contract_type = $service->contract_type;
            $building_type       = $service->building_type;
            $included       = $service->included;

            $banner_image   = "";
            $categories = ServiceCategories::where(['deleted' => 0, 'parent_id' => 0])->where('id', '!=', $id)->get();
            return view("portal.service.create", compact('page_heading', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image','description','category','serviceprice','service_type','contract_type','building_type','included'));
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
        $category = Service::find($id);
        if ($category) {
            $category->deleted = 1;
            $category->active = 0;
            $category->updated_at = gmdate('Y-m-d H:i:s');
            $category->save();
            $status = "1";
            $message = "Services removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (Service::where('id', $request->id)->update(['active' => $request->status])) {
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
    public function sort(Request $request)
    {
        if ($request->ajax()) {
            $status = 0;
            $message = '';

            $items = $request->items;
            $items = explode(",", $items);
            $sorted = ServiceCategories::sort_item($items);
            if ($sorted) {
                $status = 1;
            }

            echo json_encode(['status' => $status, 'message' => $message]);

        } else {
            $page_heading = "Sort Categories";

            $list = Categories::where(['deleted' => 0, 'parent_id' => 0])->get();

            return view("portal.sort", compact('page_heading', 'list'));
        }
    }
}
