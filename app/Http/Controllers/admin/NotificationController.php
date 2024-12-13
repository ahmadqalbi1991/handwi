<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Notifications;
use Validator;
use Kreait\Firebase\Contract\Database;

class NotificationController extends Controller
{
     public function __construct(Database $database)
    {
        $this->database = $database;
    }

 public function notifications(Request $request)
    {
        $page_heading = 'Notifications List';
        $notification_list = Notifications::orderBy('id','desc')->get();
        return view('admin.notifications.index', compact('page_heading','notification_list'));
    }


    public function create(Request $request){
        $page_title = "Add New Notifications";
        return view('admin.notifications.form', compact('page_title'));
    }
    
      public function save(Request $request){

          $validator = Validator::make($request->all(),[
            'title'         => 'required',
            'description'   => 'required'
        ]);
        
        if ($validator->fails())
        {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        }
        else
        {
            $notifications = new Notifications();
                       
            $notifications->title       = $request->title;
            $notifications->description = $request->description;
            $notifications->user_type = $request->usertype??0;
            $notifications->created_at = gmdate('Y-m-d H:i:00');
            $file_name = "";
            $image = '';
            if($file = $request->file("image"))
            {
                $file_name = time().$file->getClientOriginalName();
                $dir = config('global.upload_path'). 'notifications/';
                $res =  $file->move($dir,$file_name);
                $image = asset('uploads/notifications/'.$file_name);
            }
            
            
            $notifications->image = $file_name;
            if($notifications->save())
            {
                $providersList = [];
                //$image = $notifications->image;

                if(!empty($file_name))
                    $file_name = $image;
                

                

                exec("php ".base_path()."/artisan send:admin_notification --uri=" . $request->usertype." --uri2=" . $notifications->id . " --uri3=" . $image . " > /dev/null 2>&1 & ");
                //\Artisan::call('send:admin_notification --uri='.$request->usertype.' --uri2='.$notifications->id.' --uri3='.$image);
                
                /*if(!empty($usersListQry))
                {
                    foreach($usersListQry as $user)
                    {
                        if(!empty($user->firebase_user_key))
                        {
                            $providersList[] = [
                                'user_id'=>$user->id,
                                'user_device_token'=>$user->user_device_token,
                                'firebase_user_key'=>$user->firebase_user_key,
                            ];
                        }
                    }
                }
                
               
                if(!empty($providersList))
                {
                    $bd_data = [];
                    $push_dat= [];
                    $title          = $request->title;
                    $description    = $request->description;
                    $ntype          = 'public-notification';
                    $notification_id = time();
                    //print_r($providersList);

                    foreach($providersList as $seller)
                    {
                        
                        $in_data = [
                            "title" => $title,
                            "description" => $description,
                            "notificationType" => $ntype,
                            "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                            "url" => "",
                            "imageURL" => (string)$file_name,
                            "read" => "0",
                            "seen" => "0"
                        ];
                        if($seller['user_device_token'] != "" ){
                            $push_dat[] = $seller['user_device_token'];
                        }
                        if($seller['firebase_user_key'] != "" ){
                            $bd_data["Notifications/".$seller['firebase_user_key']."/".$notification_id] = $in_data;
                        }
                        
                    }

                    if(!empty($push_dat))
                    {
                        
                        $res = send_multicast_notification($push_dat,
                        [
                            "title" => $title,
                            "body" => $description,
                            "icon" => 'myicon',
                            "sound" => 'default',
                            "click_action" => "EcomNotification",
                        ],
                        [
                            "type" => $ntype,
                            "notificationID" => $notification_id,
                            "imageURL" => (string)$file_name,
                        ]);
                        
                        //print_r($push_dat); exit;
                    }
                    if(!empty($bd_data))
                    {
                        $this->database->getReference()->update($bd_data);
                    }
                }*/
                $errors = [];
                $status = 1;
                $message = "Notification sent successfully";
            }
        }

        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
    }
    
    public function delete(Request $request)
    {
        $status="0";
        $message="Something went wrong.";
        $record = Notifications::find($request->id);
        if ($record) {
            $record->delete();
            $status="1";
            $message="Record has been delete successfully";
        }
        echo json_encode(['status'=>$status,'message'=>$message]);
    }
    
    
    
  
    
}