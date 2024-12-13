<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderModel;
use App\Models\OrderServiceModel;
use App\Models\OrderServiceItemsModel;
use Kreait\Firebase\Contract\Database;

class UpdateOrderStatusPush extends Command
{
    /**
     * The name and signature of the console command. 
     * 
     * @var string
     */
    protected $signature = 'order:update_status {order_id} {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
     public $database = '';
     public $status_text = '';
    public function __construct(Database $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $order_id = $this->argument("order_id");
        $status_text = order_status($this->argument("status"));
        $order2=$order = OrderModel::join('users','users.id','=','orders.user_id')->leftjoin('order_products','order_products.order_id','=','orders.order_id')
        ->leftJoin('users as store','users.id','=','order_products.vendor_id')->where(['orders.order_id'=>$order_id])
        ->select(['orders.*','users.firebase_user_key','users.email','users.user_device_token','store.firebase_user_key as store_firebase_user_key','store.user_device_token as  store_device_token'])->get();
        
        if($order->count() > 0){
            $order= $order->first();
            $order_no = config('global.sale_order_prefix').date(date('Ymd', strtotime($order->created_at))).$order->order_id;
            $title = $order_no;
            $nottification_id = time();
            $nodeData = [
              'title'    => $title,//"Your order status is updated to ".$status_text." successfully",
              'description'=>"Your order status succesfully changed to ".$status_text,
              'notificationType' => 'order_status',
              'createdAt'     => gmdate("d-m-Y H:i:s", $nottification_id),
              'orderId'  => (string) $order->order_id,
              'url' => '',
              'imageUrl' => '',
              'read'=> '0',
              'seen'=> '0',
              
            ];
            
            if($order->firebase_user_key != ''){
                $this->database->getReference('Notifications/'.$order->firebase_user_key."/".$nottification_id.'/')->update($nodeData);
            }
            $nodeData = [
              'type'     => 'order_status',
              'orderId'  => $order->order_id,
              'title'    => $title,//"order status is updated",
              'createdDate'     => gmdate("d-m-Y H:i:s", $nottification_id),
              'imageUrl' => '',
              'time'    => $nottification_id
            ];
            if($order->store_firebase_user_key != ''){
                //$this->database->getReference('Notifications/'.$order->store_firebase_user_key."/".$nottification_id.'/')->update($nodeData);
            }

            $ntype          = 'order_status';
           
         if($order->user_device_token != ''){
           $res = send_single_notification($order->user_device_token,
                        [
                            "title" => $title,//"Update Order Status",
                            "body" => "You order has been updated to ".$status_text." successfully",
                            "icon" => 'myicon',
                            "sound" => 'default',
                            "click_action" => "EcomNotification",
                        ],
                        [
                            "type" => $ntype,
                            "notificationID" => $nottification_id,
                            "imageURL" => '',
                            "orderId" => $order->order_id
                        ]);
         }
         
        

        }
        
        
        //mail
        // $order =  OrderServiceModel::select("orders_services.*", "orders_services.status as order_status", "users.name as customer_name", "user_address.*")
        //     ->leftjoin('users', 'users.id', 'orders_services.user_id')
        //     ->where(['order_id' => $order_id])
        //     ->leftjoin('user_address', 'user_address.id', '=', 'orders_services.address_id')
        //     ->first();
        // if (!empty($order)) {
        //     $order->service_details = OrderServiceItemsModel::select('orders_services_items.*', 'service.name', 'service.image', 'description')
        //         ->leftjoin('service', 'service.id', '=', 'orders_services_items.service_id')
        //         ->where('order_id', $order->order_id)->get();
        // }
        // $message = "Your order status is updated to ".$status_text." successfully";
        
        if($this->argument("status") == config('global.order_status_delivered'))
        {
            //printr($order2);
            try {
                $invoice_api  = new \App\Http\Controllers\Api\v1\InvoiceAPIController();
                $res = $invoice_api->place_invoice($order2->first());
             } catch (\Exception $e) {
                 echo $e->getMessage();
             }
        }
        
        // $mailbody = view('email_templates.order_status_change', compact('order','message'));
        // $order= $order->first();
        // send_email($order->email, "Order Status Updated", $mailbody);
        return 0;
    }
}
