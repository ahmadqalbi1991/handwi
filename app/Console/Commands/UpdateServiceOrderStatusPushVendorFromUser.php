<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderServiceItemsModel;
use App\Models\OrderServiceModel;
use App\Models\ServiceAssignedVendors;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Log;

class UpdateServiceOrderStatusPushVendorFromUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:update_service_status_vendor_from_user {--uri=}';

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
        $order_id = $this->option("uri");
        $ntype          = 'service_order_status_vendor';
        $list = ServiceAssignedVendors::with(['user','order'])->where(['order_id'=>$order_id])->get();
        //printr($list->toArray());
                foreach($list as $key){
                    
                        $description = "User cancelled the order for some reason.";
                    
                    

                    $nottification_id = time();
                    $nodeData = [
                    'title'    => $key->order->order_no,
                    'description'=>$description,
                    "notificationType" => $ntype,
                    'createdAt'  => gmdate("d-m-Y H:i:s", $nottification_id),
                    'orderId'  => (string) $key->order->order_id,
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
                    'orderId'  => $key->order->order_id,
                    'title'    => $description,
                    'createdDate'     => gmdate("d-m-Y H:i:s", $nottification_id),
                    'imageUrl' => '',
                    'time'    => $nottification_id
                    ];

                    

                    $ntype          = 'service_order_status_vendor';
                
                    if($key->user->user_device_token != ''){
                        echo "here";
                    $res = send_single_notification($key->user->user_device_token,
                                    [ 
                                        "title" => $key->order->order_no,
                                        "body" => $description,
                                        "icon" => 'myicon',
                                        "sound" => 'default',
                                        "click_action" => "EcomNotification",
                                    ],
                                    [
                                        "type" => $ntype,
                                        "notificationID" => $nottification_id,
                                        "imageURL" => '',
                                        "orderId" => $key->order->order_id
                                    ]);
                        print_r($res);
                    }
                }
        
        return 0;
    }
}
