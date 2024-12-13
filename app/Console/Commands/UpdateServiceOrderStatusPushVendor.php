<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderServiceItemsModel;
use App\Models\OrderServiceModel;
use App\Models\ServiceAssignedVendors;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Log;

class UpdateServiceOrderStatusPushVendor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:update_service_status_vendor {--uri=} {--uri2=} {--uri3=}';

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
        
        
        $order = OrderServiceModel::select('orders_services.*','users.firebase_user_key','users.user_device_token','orders_services_items.accepted_vendor as selected_vendor')
         ->where(['orders_services_items.id'=>$order_id])
        
        ->leftjoin('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')
        ->leftjoin('users','users.id','=','orders_services_items.accepted_vendor')
        ->get();
        
        //printr($order->toArray());
      $ntype   = 'service_order_status_vendor';
        if($order->count() > 0){
            $order = $order->first();
            if($order->selected_vendor != 0){
                    $description="Your service order status succesfully changed to ".$status_text;
                    if($this->option("uri2") == 10){
                        $description = "Admin cancelled the order for some reason.";
                    }
                    

                    $nottification_id = time();
                    $nodeData = [
                    'title'    => $order->order_no,
                    'description'=>$description,
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
                    'type'     => 'service_order_status_vendor',
                    'orderId'  => $order->order_id,
                    'title'    => $description,
                    'createdDate'     => gmdate("d-m-Y H:i:s", $nottification_id),
                    'imageUrl' => '',
                    'time'    => $nottification_id
                    ];

                   

                    $ntype          = 'service_order_status_vendor';
                
                    if($order->user_device_token != ''){
                    $res = send_single_notification($order->user_device_token,
                                    [ 
                                        "title" => $order->order_no,
                                        "body" => $description,
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
                        print_r($res);
                    }
            }else{
                $list = ServiceAssignedVendors::with('user')->where(['order_id'=>$order->order_id])->get();
                foreach($list as $key){
                    $description="Your service order status succesfully changed to ".$status_text;
                    if($this->option("uri2") == 10){
                        $description = "Admin cancelled the order for some reason.";
                    }
                    

                    $nottification_id = time();
                    $nodeData = [
                    'title'    => $order->order_no,
                    'description'=>$description,
                    "notificationType" => $ntype,
                    'createdAt'  => gmdate("d-m-Y H:i:s", $nottification_id),
                    'orderId'  => (string) $order->order_id,
                    'url' => '',
                    'imageUrl' => '',
                    'read'=> '0',
                    'seen'=> '0'
                    ];
                    
                    if($key->user->firebase_user_key != ''){
                        $this->database->getReference('Notifications/'.$key->user->firebase_user_key."/".$nottification_id.'/')->update($nodeData);
                    }
                    $nodeData = [
                    'type'     => 'service_order_status_vendor',
                    'orderId'  => $order->order_id,
                    'title'    => $description,
                    'createdDate'     => gmdate("d-m-Y H:i:s", $nottification_id),
                    'imageUrl' => '',
                    'time'    => $nottification_id
                    ];

                    

                    $ntype          = 'service_order_status_vendor';
                
                    if($key->user->user_device_token != ''){
                    $res = send_single_notification($key->user->user_device_token,
                                    [ 
                                        "title" => $order->order_no,
                                        "body" => $description,
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
                        print_r($res);
                    }
                }
            }
            

        }
        
        return 0;
    }
}
