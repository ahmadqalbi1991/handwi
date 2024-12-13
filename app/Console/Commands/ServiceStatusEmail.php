<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderServiceModel;
use App\Models\OrderServiceItemsModel;
use App\Models\User;
use DB;

class ServiceStatusEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:send_service_status_email {--uri=} {--uri2=} {--uri3=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Order Email';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        $order_id =  urldecode($this->option("uri"));
        $order_status =  urldecode($this->option("uri2"));
        $item_id =  urldecode($this->option("uri3"));
        $order_status_text = service_order_status($order_status);
        $subject = 'Order '.$order_status_text;
        
        $order = OrderServiceModel::select('orders_services.*','users.dial_code','users.phone','users.phone','first_name','last_name','users.email')->orderBy('order_id','desc')->with(['services' => function ($qr) use($item_id) {
                $qr->select('orders_services_items.id', 'order_id', 'service_id','image','service.name','service_price','orders_services_items.order_status','booking_date','orders_services_items.qty','orders_services_items.hourly_rate','vat','discount')->where('orders_services_items.id',$item_id)->join('service', 'service.id', 'orders_services_items.service_id');
            },'customer'])->leftjoin('users','users.id','=','orders_services.user_id')->where(['orders_services.order_id'=>$order_id])->first();

    


        $bookindate = OrderServiceItemsModel::where('order_id',$order_id)->get()->first();

            if ($order) {
               
                $order->status_text = service_order_status($order->status);
                $order->order_no    = config('global.sale_order_prefix')."-SER".date(date('Ymd', strtotime($order->created_at))).$order->order_id;
                $order->payment_mode_id = (string) $order->payment_mode;
                $order->payment_mode = payment_mode($order->payment_mode);

                $order->order_date = DateTimeFormat($order->booking_date);
                //$order->booking_date = date('d-M-y h:i A', strtotime($order->booking_date));
                $order->booking_date = fetch_booking_date($order->order_id);
                $order->created_at = DateTimeFormat($order->created_at);

                $order->order_date = date(config('global.datetime_format'), strtotime($bookindate->booking_date??$order->created_at));
                $order->address = \App\Models\UserAdress::get_address_details($order->address_id);
                $service = $order->services;
                foreach ($service as $key => $value) {
                 $service[$key]->image = get_uploaded_image_url($value->image,'service_image_upload_dir');  
                 
                 $service[$key]->status_text = service_order_status($value->order_status);
                 $serviceid = $value->service_id;
                }
                $order->services = convert_all_elements_to_string($service);
                $o_data = $order;
            }
       
         $vendor_id = [];
         $vendor = null;
         
                           if ( $serviceid ){
                               $vendor_service = DB::table('vendor_services')->where('service_id', $serviceid)->get();

                               foreach ($vendor_service as $key => $value) {
                                   $vendor_id[] = $value->vendor_id;
                               }
                           }
                       
                       if ( $vendor_id ) {
                           $vendor = User::select('first_name as name','email')->whereIn('id',$vendor_id)->get();
                       }
         $vendor = User::select('first_name as name','email')->where('id',$o_data->accepted_vendor)->get();
        if($order_status != 10)
        {
        if($order_status != 1)
        {
            foreach ($vendor as $key => $value) {
            $to = $value->email;
            $name = $value->name;
            $status = $order_status_text;
            $mailbody = view('mail.service_order_status', compact('o_data', 'name','status'));
        
            send_email($to, $subject, $mailbody);
            }
        }
        
        if(isset($order->customer)){
            $subject = 'Order '.$order_status_text;
            $to = $order->customer->email;
            $name = $order->customer->name;
            $status = $order_status_text;
            $mailbody = view('mail.service_order_status', compact('o_data', 'name','status'));
        
            send_email($to, $subject, $mailbody);
        }
        //send mail to admin
        $vendor = User::where('id',$o_data->accepted_vendor)->get()->first();
            $to = env('MAIL_FROM_ADDRESS_CC'); 
            $name = "admin";
            $status = $order_status_text;
            $mailbody = view('mail.service_order_status', compact('o_data', 'name','status','vendor'));
        
            send_email($to, $subject, $mailbody);
        }
        else
        {
            $to = env('MAIL_FROM_ADDRESS_CC'); 
            $name = "admin";
            $status = "Rejected";
            $subject = 'Order '.$status;
            $mailbody = view('mail.service_order_status', compact('o_data', 'name','status'));
        
            send_email($to, $subject, $mailbody);
        }
    }
}
