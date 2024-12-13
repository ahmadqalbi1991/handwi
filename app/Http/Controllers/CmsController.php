<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PageModel;
use App\FaqModel;
use App\Models\Article;
use App\SettingsModel;
use App\ContactUsModel;
use App\ContactUsSetting;
use App\SubscriptionEmailModel;
use Illuminate\Support\Facades\DB;
use Validator;

class CmsController extends Controller
{
    //
    public function about_us(){
        $data = Article::where("id",'1')->first();
        $page_heading = 'About Us';
        return view('web.cms.about_us',compact('page_heading','data'));
    }
    public function contact_us(){
        $page_heading = 'Contact Us';
        return view('web.cms.contact_us',compact('page_heading'));
    }
    function submit_contact_us(Request $request){
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
                $mailbody =  view("web.emai_templates.contact_us",compact('name','email','phone','msg'));
                $to = ContactUsSetting::first();
                
                $contact['name'] = $name;
                $contact['email'] = $email;
                $contact['mobile_number'] = $phone;
                $contact['message'] = $msg;
                $contact['date'] = gmdate('Y-m-d H:i:s');
                ContactUsModel::insert($contact);
                send_email($to->email,'New Contact Form Received',$mailbody);
                $status = "1";
                $message = "Successfully submited";
                $errors = '';

                // if(send_email($to->email,'New Contact Form Received',$mailbody)){
                //     $status = "1";
                //     $message = "Successfully submited";
                //     $errors = '';
                // }else{
                //     $status = "0";
                //     $message = "Something went wrong";
                //     $errors = '';
                // }
            }
            echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
    }
    function submit_subscription(Request $request){
        $status = "0";
            $message = "";
            $errors = '';
            $validator = Validator::make($request->all(),
                [
                    'email' => 'required|email',
                ]
            );
            if ($validator->fails()) {
                $status = "0";
                $message = "Validation error occured";
                $errors = $validator->messages();
            } else {
                $email = $request->email;
                $check = SubscriptionEmailModel::where('email', '=', $request->email)->first();
                if ($check === null) {

                    $datain['email'] = $email;
                    $datain['date'] = gmdate('Y-m-d H:i:s');
                    SubscriptionEmailModel::insert($datain);
                
                $status = "1";
                $message = "Successfully submited";
                $errors = '';
            
                }
                else
                {
                $status = "1";
                $message = "Email Already added!";
                $errors = $validator->messages();
                }
                
            }
            echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
    }
    public function faq(){
        $page_heading = 'Faq ';
        $data = FaqModel::where('active',1)->orderBy('created_on','desc')->get();
        return view('web.cms.faq',compact('page_heading','data'));
    }
    public function user_guide(){
        $data = PageModel::select('oodle_pages.id','oodle_pages.title as page_title','oodle_page_data.title','description')->where('oodle_pages.id',2)->leftjoin('oodle_page_data','page_id','=','oodle_pages.id')->get()->first();
        $page_heading = 'User Guide';
        return view('web.cms.user_guide',compact('page_heading','data'));
    }
    public function join_us(){
        $page_heading = 'Join Oodle';
        $data = PageModel::select('oodle_pages.id','oodle_pages.title as page_title','oodle_page_data.title','description')->where('oodle_pages.id',3)->leftjoin('oodle_page_data','page_id','=','oodle_pages.id')->get()->first();
        return view('web.cms.join_us',compact('page_heading','data'));
    }
    public function oodle_policy(){
        $page_heading = 'Oodle Policy';
        $data = PageModel::select('oodle_pages.id','oodle_pages.title as page_title','oodle_page_data.title','description')->where('oodle_pages.id',8)->leftjoin('oodle_page_data','page_id','=','oodle_pages.id')->get()->first();
        return view('web.cms.oodle_policy',compact('page_heading','data'));
    }
    public function general_policy(){
        $page_heading = 'General Policy';
        $data = PageModel::select('oodle_pages.id','oodle_pages.title as page_title','oodle_page_data.title','description')->where('oodle_pages.id',6)->leftjoin('oodle_page_data','page_id','=','oodle_pages.id')->get()->first();
        return view('web.cms.general_policy',compact('page_heading','data'));
    }
    public function cooke_policy(){
        $page_heading = 'Cookies Policy';
        $data = PageModel::select('oodle_pages.id','oodle_pages.title as page_title','oodle_page_data.title','description')->where('oodle_pages.id',7)->leftjoin('oodle_page_data','page_id','=','oodle_pages.id')->get()->first();
        return view('web.cms.cooke_policy',compact('page_heading','data'));
    }
    public function company_info(){
        $page_heading = 'Company Info';
        $data = PageModel::select('oodle_pages.id','oodle_pages.title as page_title','oodle_page_data.title','description')->where('oodle_pages.id',4)->leftjoin('oodle_page_data','page_id','=','oodle_pages.id')->get()->first();
        return view('web.cms.company_info',compact('page_heading','data'));
    }
    public function advertise_us(){
        $page_heading = 'Advertise with us';
        $data = PageModel::select('oodle_pages.id','oodle_pages.title as page_title','oodle_page_data.title','description')->where('oodle_pages.id',5)->leftjoin('oodle_page_data','page_id','=','oodle_pages.id')->get()->first();
        return view('web.cms.advertise_us',compact('page_heading','data'));
    }
    public function oodle_membership(){
        $page_heading = 'Oodle Membership';
        return view('web.cms.oodle_membership',compact('page_heading'));
    }
    public function terms_and_conditions(){
        $page_heading = 'Terms & Conditions';
        $data = Article::where("id",'4')->first();
        return view('web.cms.terms_and_conditions',compact('page_heading','data'));
    }
    public function privacy_policy(){
        $page_heading = 'Privacy Policy';
        $data = Article::where("id",'2')->first();
        return view('web.cms.privacy_policy',compact('page_heading','data'));
    }

    public function page($id){
        $page = Article::where('id',$id)->first();
        $heading  = $page->title_en??'';
        return view('web.cms.page',compact('page','heading'));
    }

      public function subscribe_email(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers_emails,email',
        ]);

        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->errors()], 422);
        // }

        
        $email = $request->input('email');

        
        $count = DB::table('subscribers_emails')->where('email', $email)->count();
        if ($count > 0) {
            return response()->json(['error' => 'Email address already subscribed'], 422);
        }

        
        $inserted = DB::table('subscribers_emails')->insert(['email' => $email]);

        if ($inserted) {
            return response()->json(['message' => 'Subscription successful!'], 200);
        } else {
            return response()->json(['error' => 'Error inserting subscription'], 500);
        }
    }
}
