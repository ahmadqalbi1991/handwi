<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderServiceItemsModel;
use App\Models\OrderServiceModel;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Log;

class UpdateServiceOrderStatusPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:update_service_status {--uri=} {--uri2=} {--uri3=}';

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
        $order_id = $this->option("uri3");
        //$item_id = $this->argument("item_id");
        $status_text = order_status($this->option("uri2"));
        if($this->option("uri2") == 4)
        {
         $status_text = "Completed";
        }
        if($this->option("uri2") == 3)
        {
         $status_text = "Ongoing";
        }
        
        
        $order = OrderServiceModel::select('orders_services.*','users.firebase_user_key','users.user_device_token')
         ->where(['orders_services_items.id'=>$order_id])
        ->leftjoin('users','users.id','=','orders_services.user_id')
        ->leftjoin('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')
        ->get();
        
        //printr($order);
      $ntype   = 'service_order_status';
        if($order->count() > 0){
            $order = $order->first();

            $nottification_id = time();
            $nodeData = [
              'title'    => $order->order_no,
              'description'=>"Your service order status succesfully changed to ".$status_text,
              "notificationType" => $ntype,
              'createdAt'  => gmdate("d-m-Y H:i:s", $nottification_id),
              'orderId'  => (string) $order->order_id,
              'url' => '',
              'imageUrl' => '',
              'read'=> '0',
              'seen'=> '0',
              'description'=>"Your service order status succesfully changed to ".$status_text
            ];
            
            if($order->firebase_user_key != ''){
                $this->database->getReference('Notifications/'.$order->firebase_user_key."/".$nottification_id.'/')->update($nodeData);
            }
            $nodeData = [
              'type'     => 'service_order_status',
              'orderId'  => $order->order_id,
              'title'    => "order status is updated",
              'createdDate'     => gmdate("d-m-Y H:i:s", $nottification_id),
              'imageUrl' => '',
              'time'    => $nottification_id
            ];

            if($order->store_firebase_user_key != ''){
                //$this->database->getReference('Notifications/'.$order->store_firebase_user_key."/".$nottification_id.'/')->update($nodeData);
            }

            $ntype          = 'service_order_status';
           
         if($order->user_device_token != ''){
           $res = send_single_notification($order->user_device_token,
                        [ 
                            "title" => $order->order_no,
                            "body" => "You service order has been updated to ".$status_text." successfully",
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
         	//print_r($res);
         }
         //mail
        $order =  OrderServiceModel::select("orders_services.*", "orders_services.status as order_status", "users.name as customer_name","users.email as customer_email", "user_address.*")
            ->leftjoin('users', 'users.id', 'orders_services.user_id')
            ->where(['order_id' => $order->order_id])
            ->leftjoin('user_address', 'user_address.id', '=', 'orders_services.address_id')
            ->first();
        //printr($order);
        if (!empty($order)) {
            $order->service_details = OrderServiceItemsModel::select('orders_services_items.*', 'service.name', 'service.image', 'description')
                ->leftjoin('service', 'service.id', '=', 'orders_services_items.service_id')
                ->where('order_id', $order->order_id)->get();
        }
        $message = "Your order status changed successfully";
        
        $mailbody = view('email_templates.order_status_change', compact('order','message'));
        //echo $order->customer_email;
        //send_email($order->customer_email, "Order Status Updated", $mailbody);

        }
        
        return 0;
    }
}
