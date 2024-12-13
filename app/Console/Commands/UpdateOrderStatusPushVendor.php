<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderModel;
use Kreait\Firebase\Contract\Database;

class UpdateOrderStatusPushVendor extends Command
{
    /**
     * The name and signature of the console command. 
     * 
     * @var string
     */
    protected $signature = 'order:update_status_vendor {order_id} {status}';

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
        $order = OrderModel::leftjoin('order_products','order_products.order_id','=','orders.order_id')->join('users','users.id','=','order_products.vendor_id')
        ->leftJoin('users as store','users.id','=','orders.user_id')->where(['orders.order_id'=>$order_id])
        ->select(['orders.*','users.firebase_user_key','users.user_device_token','store.firebase_user_key as store_firebase_user_key','store.user_device_token as  store_device_token'])->get();
        
        if($order->count() > 0){
            $order = $order->first();

            $nottification_id = time();
            $nodeData = [
              'title'    => "Order status is updated to ".$status_text." successfully",
              'description'=>"Order status succesfully changed to ".$status_text,
              'notificationType' => 'vendor_order_status',
              'createdAt'     => gmdate('Y-m-d H:i:s'),
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
              'title'    => "order status is updated",
              'createdDate' => gmdate('Y-m-d H:i:s'),
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
                            "title" => "Update Order Status",
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
            printr($res);
         }
         
        

        }
        return 0;
    }
}
