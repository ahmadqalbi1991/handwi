<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServiceCategories;
use App\Models\ServiceInclude;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contracting;
use App\Models\Maintainance;
use App\Models\User;
use App\Models\BuildingTypes;
use Kreait\Firebase\Contract\Database;
use Validator;

class ContractMaintenanceController extends Controller
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Contract Maintenance Jobs";
       $contract_maintainance_job  = [];

            $contracts = Contracting::select('id', 'description','building_type','contract_type','user_id','file','created_at')->orderBy('created_at', 'desc')
            ->with('building_list','user')->where(['deleted'=> 0 ])->get();
            foreach($contracts as $contract)
            { 
                if($contract->contract_type === 1){

                    $contract->contract_text = 'Fresh';
                }else{
                    $contract->contract_text = 'Extension';
                }
                $contract->name = 'contract';
                array_push($contract_maintainance_job,$contract);
            }

            $maintainances = Maintainance::select('id','description','building_type','user_id','file','created_at')->orderBy('created_at', 'desc')  
            ->with('building_list')->where('deleted', 0)->get();
            
            foreach($maintainances as $maintainance)
            { 
                $maintainance->name = 'maintenance';
                array_push($contract_maintainance_job,$maintainance);
            }

            if($contract_maintainance_job){
                foreach ($contract_maintainance_job as $key => $row)
                {
                    $count[$key] = $row['created_at'];
                }
                array_multisort($count, SORT_DESC, $contract_maintainance_job);
            }

        return view('admin.contract_maintenance.list', compact('page_heading', 'contract_maintainance_job'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Service Category";
        $mode = "create";
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
        return view("admin.contract_maintenance.create", compact('page_heading', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image', 'description', 'servic_include'));
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
            'price' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $input = $request->all();
            {
                $ins = [
                    'price' => $request->price,
                    'status' => 3,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
                ];
                
                if ($request->file("qoutation_file")) {
                    $response = image_save($request, config('global.contracts_image_upload_dir'), 'qoutation_file');
                    if ($response['status']) {
                        $ins['qoutation_file'] = $response['link'];
                    }
                }

                $user_id = 0;
                $type = 1;

                if($request->name  == 'maintenance')
                {
                        $maintenance = Maintainance::find($request->id);
                        $maintenance->update($ins);

                        $maintenance = Maintainance::where('id',$request->id)->first();
                        $user_id = $maintenance->user_id;

                        $status = "1";
                        $message = "maintenance updated Successfully";
                        $description = "Maintenance Quote Generated successfully.";
                        $type = 0;
                }
                if($request->name  == 'contract')
                {
                        $contract = Contracting::find($request->id);
                        $contract->update($ins);

                        $maintenance = Contracting::where('id',$request->id)->first();
                        $user_id = $maintenance->user_id;

                        $status = "1";
                        $message = "Contracting updated Successfully.";
                        $description = "Contracting Quote Generated successfully";
                }

                $users = User::find($user_id);
               $ntype = 'quote_created';
                $title= 'Your Request Created';
                
                if($request->name  == 'contract')
                {
                  $title = 'Quote Created';
                }
                if($request->name  == 'maintenance')
                {
                    $ntype = 'maintenance_quote_created';
                  $title = 'Maintenance Quote Created';
                }
                
                $notification_id = time();
                

                if (!empty($users->firebase_user_key)) {
                    $notification_data["Notifications/" . $users->firebase_user_key . "/" . $notification_id] = [
                        "title" => $title,
                        "description" => $description,
                        "notificationType" => $ntype,
                        "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                        "orderId" => (string) $request->id,
                        "Type" => (string) $type,
                        "url" => "",
                        "imageURL" => '',
                        "read" => "0",
                        "seen" => "0",
                    ];
                    $this->database->getReference()->update($notification_data);
                }

                if (!empty($users->user_device_token)) {
                    $ret =send_single_notification(
                        $users->user_device_token,
                        [
                            "title" => $title,
                            "body" => $description,
                            "icon" => 'myicon',
                            "sound" => 'default',
                            "click_action" => "EcomNotification"
                        ],
                        [
                            "type" => $ntype,
                            "notificationID" => $notification_id,
                            "orderId" => (string) $request->id,
                            "Type" => (string) $type,
                            "imageURL" => "",
                        ]
                    );
                    
                }
              
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
            $serviceInclude->icon = $include['include_icon'];
            $serviceInclude->save();
        }
    }


    public function edit($id,$name)
    {
        if($name === 'contract'){
            $contract_maintainance_job = Contracting::find($id);
        }
        if($name === 'maintenance'){
            $contract_maintainance_job = Maintainance::find($id);
        }
        //
        
       
        
        if ($contract_maintainance_job) {
            $page_heading = "Contracting Maintenance Jobs";
            $files = [];
            if(!empty($contract_maintainance_job->processed_file_urls))
            {
                $filse = $contract_maintainance_job->processed_file_urls;
                foreach($filse as $keyfile=> $val)
                {
                    $files[] = $val;
                }
            }
            $file = $files;
            $mode  = "edit";
            $id    = $contract_maintainance_job->id;
            $name  = $name;
            //$file = $contract_maintainance_job->file;
            $description = $contract_maintainance_job->description;
            $price = $contract_maintainance_job->price;
            $qoutation_file = $contract_maintainance_job->qoutation_file;
            $user = User::find($contract_maintainance_job->user_id);
            $building_list = BuildingTypes::find($contract_maintainance_job->building_type);
            $created_at  = $contract_maintainance_job->created_at;
            $status  = $contract_maintainance_job->status;
            $contract_type = $contract_maintainance_job->contract_type ?? 0;
            
            return view("admin.contract_maintenance.create", 
            compact('page_heading', 'contract_maintainance_job', 'mode', 'id','price','status', 'qoutation_file','building_list','name', 'file', 'description', 'user' , 'created_at','contract_type' ));
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
            $page_heading = "Sort Categories";

            $list = Categories::where(['deleted' => 0, 'parent_id' => 0])->get();

            return view("admin.sort", compact('page_heading', 'list'));
        }
    }
}