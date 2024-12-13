<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Article;
use Validator;
use Illuminate\Http\Request;
use App\ContactUsQueries;
use App\Models\ContactUsSetting;
use App\Models\SettingsModel;
use Illuminate\Support\Facades\Storage;
use App\Models\LandingPageSetting;

class PagesController extends Controller
{
    public function contact_quries()
    {
        $page_heading = "Contact Us Queries";
        $queries  = ContactUsQueries::orderBy('id', 'Desc')->get();
        return view('admin.contact.index',compact('page_heading','queries'));
    }


    public function contact_details()
    {
        $page_heading = "Contact Us Details";
        $page  = ContactUsSetting::first();
        if($page == null){
            $page  = new ContactUsSetting();
        }
        return view('admin.contact.contact_settings',compact('page_heading','page'));
    }

    public function landing_page_details()
    {
        $page_heading = "Landing Page Details";
        $page  = LandingPageSetting::all();
        if($page == null){
            $page  = new LandingPageSetting();
        }
        
        return view('admin.contact.landing_page',compact('page_heading','page'));
    }
    public function settings()
    {
        $page_heading = "Settings";
        $page  = SettingsModel::first();
        $contact  = ContactUsSetting::first();
        
        return view('admin.contact.settings',compact('page_heading','page','contact'));
    }
    public function setting_store(Request $request)
    {
        $table = SettingsModel::first();

        $table->admin_commission    =  $request->admin_commission;
        $table->shipping_charge     =  $request->shipping_charge;
        $table->tax_percentage      =  $request->tax_percentage;
        $table->store_radius     =  $request->store_radius;
        $table->service_radius     =  $request->service_radius;
        $table->service_charge     =  $request->service_charge;

        $contact = ContactUsSetting::first();
        $contact->ref_discount        =  $request->ref_discount;
        $contact->save();

        if ($table->save()){
            $message = 'Setting has been updated.';
            return redirect()->back()->with('success',  $message);
        }
        
        return redirect()->back()->with('error', 'Unable to update setting');
    }

    public function landing_page_setting_store(Request $request)
    {
             foreach ($request->all() as $key => $value) {

                if ($key == '_token' ) {
                    continue;
                }


                $contact = LandingPageSetting::where('meta_key', $key)->first();


                if ($contact) {
                    if ($request->hasFile($key)) {
                         $file = $request->file($key);
                        $imageName = time() . '_' . $file->getClientOriginalName();
                        $filePath = 'uploads/landing_page_images/' . $imageName;
                        $file->move(public_path('uploads/landing_page_images/'), $imageName);
                        $imagePublicUrl = asset($filePath);
                        
                        $contact->meta_value = $imagePublicUrl;

                    } else {

                        $contact->meta_value = $value;
                    }
                    $contact->save();
                } else {
                            // Create a new record
                    $contact = new LandingPageSetting();
                    $contact->meta_key = $key;
                    if ($request->hasFile($key)) {

                        $file = $request->file($key);
                        $imageName = time() . '_' . $file->getClientOriginalName();
                        $filePath = 'uploads/landing_page_images/' . $imageName;
                        $file->move(public_path('uploads/landing_page_images/'), $imageName);
                        $imagePublicUrl = asset($filePath);
                        $contact->meta_value = $imagePublicUrl;
                    } else {

                        $contact->meta_value = $value;
                    }
                    $contact->save();
                }
            }

            $message = 'Landing Page settings have been updated.';
            return redirect()->back()->with('success', $message);
    }

    public function contact_us_setting_store(Request $request)
    {
        $contact = ContactUsSetting::first();
        
        if($contact == null){
            $contact  = new ContactUsSetting();
            $message = 'Contact us setting has been Created.';
        }
        $contact->title_en  =  $request->title_en;
        $contact->title_ar  =  $request->title_ar;
        $contact->email  =  $request->email;
        $contact->mobile  =  $request->mobile;
        $contact->desc_en  =  $request->desc_en;
        $contact->desc_ar  =  $request->desc_ar;
        $contact->location  =  $request->location;
        $contact->latitude  =  $request->latitude;
        $contact->longitude  =  $request->longitude;
        $contact->linkedin  =  $request->linkedin;
        $contact->twitter  =  $request->twitter;
        $contact->youtube  =  $request->youtube;
        $contact->facebook  =  $request->facebook;
        $contact->instagram  =  $request->instagram;
        $contact->transport_website_link  =  $request->transport_website_link;

        if ($contact->save()){
            $message = 'Contact us setting has been updated.';
            return redirect()->back()->with('success',  $message);
        }
        
        return redirect()->back()->with('error', 'Unable to update Contact us setting');
    }

    public function index(Request $request){
        $page_heading = "CMS Pages";
        $cms_pages = Article::get();
        return view('admin.cms_pages.index', compact('cms_pages','page_heading'));
    }

    public function create(Request $request){
        $page_heading = "Add New Page";
        $cms_page = new Article();
        $cms_page->status = 1;
        return view('admin.cms_pages.form', compact('page_heading','cms_page'));
    }
    public function edit(Request $request, $id){
        $page_heading = "Update Page";
        $cms_page = Article::where("id", $id)->first();
        return view('admin.cms_pages.form', compact('page_heading','cms_page'));
    }

    public function save(Request $request)
    {
        $status  = "0";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $redirectUrl = '';
        $id      = $request->id;
        $rules   = [
            'title_en'      => 'required',
            'desc_en'       =>'required',
        ];
        $validator = Validator::make($request->all(),$rules,
        [
            'title_en.required' => 'Title required',
            'desc_en.required' => 'Description Engish required',
           
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        }else{
            $input = $request->all();
            if ($request->id != null) {
                $cms_page = Article::find($request->id);
            } else {
                $cms_page = new Article();
            }
            $cms_page->status     = $request->status == 1 ? 1 : 0;
            $cms_page->title_en     = $request->title_en;
            $cms_page->title_ar     = $request->title_ar;
            $cms_page->desc_en = $request->desc_en;
            $cms_page->desc_ar = $request->desc_ar;
            $cms_page->save();
            $status="1";
             $message='Record has been saved successfully';
       }
        echo json_encode(['status'=>$status,'message'=>$message,'errors'=>$errors]);
        
    }

    
    public function delete($id){
        $record = Article::find($id);
        $status="0";
        $message="Page removal failed";
        if($record){
            $record->delete();
           $status="1";
           $message="Page removed successfully";
        } 
        
        echo json_encode(['status' => $status, 'message' => $message]);
    }


}
