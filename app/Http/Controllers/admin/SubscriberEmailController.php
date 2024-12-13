<?php

namespace App\Http\Controllers\Admin;

use App\Models\News;
use App\Models\SubscriberEmail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Exports\SubscriberEmailsExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class SubscriberEmailController extends Controller
{
   /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
   public function index(REQUEST $request)
   {
      
      $from_date    = $request->from_date;
      $to_date      = $request->to_date;
      $page_heading = "Subscriber Emails";
      $subscriberemails = SubscriberEmail::orderby('id', 'desc');
      if($from_date != ''){
        $subscriberemails = $subscriberemails->where('created_at','>=',gmdate('Y-m-d H:i:s',strtotime($from_date." 00:00:00")));
      }
      if($to_date != ''){
        $subscriberemails = $subscriberemails->where('created_at','<=',gmdate('Y-m-d H:i:s',strtotime($to_date." 23:59:59")));
      }
      $subscriberemails = $subscriberemails->get();
      
      return view('admin.subscriber_email.list', compact('page_heading', 'subscriberemails','from_date','to_date'));
   }
   
   public function exportToExcel()
   {
     
      return Excel::download(new SubscriberEmailsExport, 'subscriber_emails.xlsx');
   }
   
   
   
}
