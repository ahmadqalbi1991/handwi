<?php

namespace App\Http\Controllers;

use App\Models\CleaningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CleaningServiceController extends Controller
{
    public function index()
    {
        $page_heading = "Cleaning Service";
        $cleaning_services = CleaningService::all();
        return view('admin.cleaning_service.list',compact('page_heading','cleaning_services'));
    }

    public function create()
    {
        $page_heading = "Cleaning Services";
        $mode = "create";
        $id = "";
        $title = "";
//        $name = "";
//        $industry_type = "";
//        $image = "";
//        $active = "1";
//        $banner_image = "";
        $category = [];
//        $industry   = IndustryTypes::where(['deleted' => 0])->get();
        return view("admin.cleaning_service.create", compact('page_heading', 'mode', 'id','title'));
    }

    public function store(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $input = $request->all();
            $check_exist = CleaningService::where([ 'title' => $request->title])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'title' => $request->title,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
//                    'industry_type' => $request->industry_type ?? 0,
//                    'active' => $request->active,
                ];

//                if($request->file("image")){
//                    $response = image_upload($request,'brand','image');
//                    if($response['status']){
//                        $ins['image'] = $response['link'];
//                    }
//                }
//                if($request->file("banner_image")){
//                    $response = image_upload($request,'brand','banner_image');
//                    if($response['status']){
//                        $ins['banner_image'] = $response['link'];
//                    }
//                }
                if ($request->id != "") {
                    $cleaning_service = CleaningService::find($request->id);
                    $cleaning_service->update($ins);
                    $status = "1";
                    $message = "Cleaning Service updated successfully";
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    CleaningService::create($ins);
                    $status = "1";
                    $message = "Cleaning Service Created  successfully";
                }
            } else {
                $status = "0";
                $message = "Name should be unique";
                $errors['title'] = $request->title . " already added";
            }

        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }
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
        $datamain = CleaningService::find($id);
        if ($datamain) {
            $page_heading = "Cleaning Service ";
            $mode = "edit";
            $id = $datamain->id;
            $title = $datamain->title;
//            $industry_type = $datamain->industry_type;
//            $image = $datamain->image;
//            $active = $datamain->active;
//            $banner_image = $datamain->banner_image;
//            $industry   = IndustryTypes::where(['deleted' => 0])->get();
            return view("admin.cleaning_service.create", compact('page_heading', 'datamain', 'mode', 'id', 'title'));
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
        $cleaning_service = CleaningService::find($id);
        if ($cleaning_service) {
//            $cleaning_service->deleted = 1;
//            $category->active = 0;
//            $cleaning_service->updated_at = gmdate('Y-m-d H:i:s');
            //$category->updated_uid = session("user_id");
            $cleaning_service->delete();
            $status = "1";
            $message = "cleaning services successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (Categories::where('id', $request->id)->update(['active' => $request->status])) {
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
