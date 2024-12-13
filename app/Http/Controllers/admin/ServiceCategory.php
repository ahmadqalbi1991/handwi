<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServiceCategories;
use App\Models\ServiceInclude;
use App\Models\AppHomeSection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class ServiceCategory extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Service Category";
        $categories = ServiceCategories::where(['deleted' => 0])->orderBy('id','desc')->get();
        foreach ($categories as $key => $val) {
            $prnt = ServiceCategories::where(['id' => $val->parent_id])->first();
            $categories[$key]->parent_name = isset($prnt->name) ? $prnt->name : '';
        }
        return view('admin.service_category.list', compact('page_heading', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $item= AppHomeSection::find(11);
        //                 //$item->type='middle_banner_3';
        //                 //$item->entity_id=0;
        //                 $item->title="Middle Banner 2";
        //                 //$item->sort_order= 9;
        //                 //$item->created_at=gmdate('Y-m-d H:i:s');
        //                 //$item->updated_at=gmdate('Y-m-d H:i:s');
        //                 $item->save();
                        
        $page_heading = "Service Category";
        $mode = "create";
        $include_in_home=0;
        $id = "";
        $name = "";
        $parent_id = "";
        $image = "";
        $active = "1";
        $banner_image = "";
        $category = [];
        $description = "";
        $servic_include = [];
        $categories = ServiceCategories::where(['deleted' => 0, 'parent_id' => 0])->get();
        return view("admin.service_category.create", compact('page_heading', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image', 'description', 'servic_include','include_in_home'));
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
            $check_exist = ServiceCategories::where(['deleted' => 0, 'name' => $request->name])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
                    'parent_id' => $request->parent_id ?? 0,
                    'description' => $request->description,
                    'activity_id' => $request->activity_id,
                    'active' => $request->active,
                ];

                if ($request->file("image")) {
                    $response = image_save($request, config('global.service_image_upload_dir'), 'image');
                    if ($response['status']) {
                        $ins['image'] = $response['link'];
                    }
                }
                if ($request->file("banner_image")) {
                    $response = image_save($request, config('global.service_image_upload_dir'), 'banner_image');
                    if ($response['status']) {
                        $ins['banner_image'] = $response['link'];
                    }
                }
                
                if ($request->include_title || $request->include_description) {
                    $serviceIncludes = [];
                    foreach ($request->include_title as $key => $value) {
                        
                        $serviceIncludes[$key]['title']  = $value;
                        $serviceIncludes[$key]['description']  = $request->include_description[$key];

                        // if ($request->old_images) {
                        //     $serviceIncludes[$key]['include_icon'] = $request->old_images;
                        // } else {
                            
                            if ($include_icon = $request->file("include_icon".$key)) {
                                //printr($request->file("include_icon")); 
                                //$include_icon = $request->file("include_icon");
                                //$include_icon = $include_icon[$key];

                                $dir = config('global.upload_path') . "/" . config('global.service_image_upload_dir') . "/service_includes";

                                $file_name = time() . uniqid() . "." . $include_icon->getClientOriginalExtension();

                                $link = $include_icon->storeAs($dir, $file_name, config('global.upload_bucket'));
                                 $dir = config('global.upload_path') . "/" . config('global.service_image_upload_dir');
                                $link = $include_icon->storeAs($dir, $file_name, config('global.upload_bucket'));

                                $serviceIncludes[$key]['include_icon'] = $file_name;
                            }
                            else
                            {
                               
                               $serviceIncludes[$key]['include_icon'] = $request->oldfile[$key]; 
                            }
                        //}
                    }
                }
                
                //printr($serviceIncludes); exit;
                if ($request->id != "") {
                    $category = ServiceCategories::find($request->id);
                    $category->update($ins);

                    $this->deleteServiceInclude($request->id);
                    if ($request->include_title) {
                        $this->createIncludes($serviceIncludes, $request->id);
                    }
                    $status = "1";
                    $message = "Service Category updated succesfully";
                    $service_id = $request->id;
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    $service_id =  ServiceCategories::create($ins)->id;

                    $this->deleteServiceInclude($service_id);
                    if ($request->include_title) {
                        $this->createIncludes($serviceIncludes, $service_id);
                    }

                    $status = "1";
                    $message = "Service Category added successfully";
                }
                
                if($request->include_in_home == 1){
                    $check_exist = AppHomeSection::where(['type'=>'category','entity_id'=>$service_id])->get();
                    if($check_exist->count() > 0){
                        AppHomeSection::where(['type'=>'category','entity_id'=>$service_id])->delete();
                    }
                    $item= new AppHomeSection();
                        $item->type='category';
                        $item->title=$request->name;
                        $item->entity_id=$service_id;
                        $item->created_at=gmdate('Y-m-d H:i:s');
                        $item->updated_at=gmdate('Y-m-d H:i:s');
                        $item->save();
                }else{
                    $check_exist = AppHomeSection::where(['type'=>'category','entity_id'=>$service_id])->get();
                    if($check_exist->count() > 0){
                        AppHomeSection::where(['type'=>'category','entity_id'=>$service_id])->delete();
                    }
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

    public function deleteServiceInclude($service_id)
    {
        $serviceInclude =  ServiceInclude::where('service_id', $service_id)->get();
        if (!empty($serviceInclude)) {
            foreach ($serviceInclude as $key => $value) {
                $value->delete();
            }
        }
    }

    public function createIncludes($serviceIncludes, $service_id)
    {
        foreach ($serviceIncludes as $include) {
            $serviceInclude = new ServiceInclude();
            $serviceInclude->service_id    = $service_id;
            $serviceInclude->title          = $include['title'];
            $serviceInclude->description = $include['description'];
            $serviceInclude->icon = $include['include_icon']??'';
            $serviceInclude->save();
        }
    }


    public function edit($id)
    {
        
        //
        $category = ServiceCategories::find($id);
        if ($category) {
            $page_heading = "Service Category ";
            $mode  = "edit";
            $include_in_home=0;
            $id    = $category->id;
            $name  = $category->name;
            $parent_id = $category->parent_id;
            $image = $category->image;
            $active = $category->active;
            $description = $category->description;
            $banner_image = $category->banner_image;
            $activity_id = $category->activity_id;
            $categories = ServiceCategories::where(['deleted' => 0, 'parent_id' => 0,'activity_id'=>$activity_id])->where('id', '!=', $id)->get();
            $servic_include = ServiceInclude::where('service_id', $id)->get();
            
            $check_exist = AppHomeSection::where(['type'=>'category','entity_id'=>$id])->get();
            if($check_exist->count() > 0){
                $include_in_home = 1;
            }
           
            return view("admin.service_category.create", compact('page_heading','activity_id', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image', 'description', 'servic_include','include_in_home'));
        } else {
            abort(404);
        }
    }
    public function get_parants_activity(Request $request)
    {
        $data_arry = [];
        
        $datamain = ServiceCategories::where(['deleted' => 0, 'parent_id' => 0,'activity_id'=>$request->activity_id])->get();
        foreach($datamain as $value)
        {
            $data_arry[] = array(
               'id'           => $value->id,
               'text'         => $value->name,
             );
        }
        echo json_encode($data_arry);
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
        $category = ServiceCategories::find($id);
        if ($category) {
            $category->deleted = 1;
            $category->active = 0;
            $category->updated_at = gmdate('Y-m-d H:i:s');
            $category->save();
            $status = "1";
            $message = "Service Category removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (ServiceCategories::where('id', $request->id)->update(['active' => $request->status])) {
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
            $page_heading = "Sort Service Categories";

            $list = ServiceCategories::with('activity','parent')->where(['deleted' => 0])->orderBy('sort_order','asc')->get();
            //printr($list->toArray()); exit;
            return view("admin.sort", compact('page_heading', 'list'));
        }
    }
}