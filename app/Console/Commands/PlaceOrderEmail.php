<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderServiceModel;
use App\Models\OrderServiceItemsModel;
use App\Models\CouponVendorServiceOrders;
use App\Models\User;
use DB;

class PlaceOrderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:send_order_placed_email {--uri=}';

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
        $subject = 'Order Received';
        $order_id =  urldecode($this->option("uri"));
      
        $order = OrderServiceModel::select('orders_services.*','users.dial_code','users.phone','users.phone','first_name','last_name','users.email')->orderBy('order_id','desc')->with(['services' => function ($qr) {
                $qr->select('orders_services_items.id', 'order_id', 'service_id','image','service.name','service_price','orders_services_items.order_status','booking_date','orders_services_items.qty','orders_services_items.hourly_rate','orders_services_items.vat','orders_services_items.discount')->join('service', 'service.id', 'orders_services_items.service_id');
            },'customer'])->leftjoin('users','users.id','=','orders_services.user_id')->where(['orders_services.order_id'=>$order_id])->first();

    


        $bookindate = OrderServiceItemsModel::where('order_id',$order_id)->get()->first();

            if ($order) {
               
                $order->status_text = order_status($order->status);
                $order->order_no    = config('global.sale_order_prefix')."-SER".date(date('Ymd', strtotime($order->created_at))).$order->order_id;
                $order->payment_mode_id = (string) $order->payment_mode;
                $order->payment_mode = payment_mode($order->payment_mode);

                $order->order_date = DateTimeFormat($order->booking_date);
                //$order->booking_date = date('d-M-y h:i A', strtotime($order->booking_date));
                $order->booking_date = fetch_booking_date($order->order_id);
                //$order->created_at = DateTimeFormat($order->created_at);

                $order->order_date = date(config('global.datetime_format'), strtotime($bookindate->booking_date??$order->created_at));
                $order->address = \App\Models\UserAdress::get_address_details($order->address_id);
                $service = $order->services;
                foreach ($service as $key => $value) {
                 $service[$key]->image = get_uploaded_image_url($value->image,'service_image_upload_dir');  
                 
                 $service[$key]->status_text = order_status($value->order_status);
                 $serviceid = $value->service_id;
                }
                $order->services = convert_all_elements_to_string($service);
                $o_data = $order;
            }

            $vendors_coupon = CouponVendorServiceOrders::where('order_id',$order_id)->pluck('vendor_id');

          
         $vendor_id = [];
         $vendor = null;
         
                           if ( $serviceid ){
                               $vendor_service = DB::table('vendor_services')->where('service_id', $serviceid)->get();

                               foreach ($vendor_service as $key => $value) {
                                   $vendor_id[] = $value->vendor_id;
                               }
                           }
                       
                       if ( $vendor_id ) {
                           $vendor = User::select('first_name as name','email');
                           if(count($vendors_coupon) > 0)
                            {
                               $vendor = $vendor->whereIn('users.id',$vendors_coupon);   
                            }
                            else
                            {
                               $vendor = $vendor->whereIn('id',$vendor_id);
                            }

                           $vendor = $vendor->get();
                       }

        if(isset($order->customer)){
            $name = $order->customer->name;
            make_service_pdf($o_data,$name);

        }
        foreach ($vendor as $key => $value) {
            $to = $value->email;
            $name = $value->name;
            $mailbody = view('mail.service_order_placed', compact('o_data', 'name'));
        
            send_email($to, $subject, $mailbody);
        }
        if(isset($order->customer)){
            $subject = 'Order Placed';
            $to = $order->customer->email;
            $name = $order->customer->name;
            $mailbody = view('mail.service_order_placed_customer', compact('o_data', 'name'));
        
            send_email($to, $subject, $mailbody);

            make_service_pdf($o_data,$name);

        }
        //send mail to admin
            $to = env('MAIL_FROM_ADDRESS_CC'); 
            $name = "admin";
            $mailbody = view('mail.service_order_placed', compact('o_data', 'name'));
        
            send_email($to, $subject, $mailbody);
        
    }
}
