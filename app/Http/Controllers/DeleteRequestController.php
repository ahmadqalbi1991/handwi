<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PageModel;
use App\FaqModel;
use App\Models\Article;
use App\SettingsModel;
use App\ContactUsModel;
use App\Models\ContactUsSetting;
use App\SubscriptionEmailModel;
use Validator;

class DeleteRequestController extends Controller
{
    public function deleteRequest(){
        $page_heading = 'Delete Request ';
        
        return view('web.delete_request.index',compact('page_heading'));
    }
    function deleteRequestStore(Request $request){
        $status = "0";
            $message = "";
            $errors = '';
            $validator = Validator::make($request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email',
                    'phone' => 'required',
                    'message' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = "0";
                $message = "Validation error occured";
                $errors = $validator->messages();
            } else {
                $name = $request->name;
                $email = $request->email;
                $phone = $request->phone;
                $msg = $request->message;
                $mailbody =  view("mail.delete_request",compact('name','email','phone','msg'));
                $to = ContactUsSetting::first();
                
                // $contact['name'] = $name;
                // $contact['email'] = $email;
                // $contact['mobile_number'] = $phone;
                // $contact['message'] = $msg;
                // $contact['date'] = gmdate('Y-m-d H:i:s');
                // ContactUsModel::insert($contact);
                send_email($to->email,'New Contact Form Received',$mailbody);
                $status = "1";
                $message = "Successfully submited";
                $errors = '';

                if(send_email($to->email,'Delete Request Received',$mailbody)){
                    $status = "1";
                    $message = "Successfully submited";
                    $errors = '';
                }else{
                    $status = "0";
                    $message = "Something went wrong";
                    $errors = '';
                }
            }
            echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
    }
    
    
    
}
