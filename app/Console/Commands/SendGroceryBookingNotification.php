<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\OrderModel;
use DB;
use Kreait\Firebase\Contract\Database;

class SendGroceryBookingNotification extends Command
{
    /**
     * The name and signature of the console command. 
     * 
     * @var string
     */
    protected $signature = 'send:send_grocery_booking_notification {--uri=} {--uri2=}';

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
        $VnedorsList = [];
        $device_token = [];
        $image = '';
        $file_name = '';
        $order_id   = $this->option("uri");
        $item_id   = $this->option("uri2") ?? '';

        $title          = "New order have been received.";
        $description    = "Received a grocery order";
        $ntype          = 'grocery_order_received';

        if($item_id == 'cancellation'){
            $item_id = '';
            $title          = "Order cancelled.";
            $description    = "Order has been cancelled.";
            $ntype          = 'grocery_order_cancelled';
        }

        $activity_id = '';
        $order =  OrderModel::with('vendor')->where('order_id',$order_id)->first();
        if($order && $order->vendor){
            $user = $order->vendor;
            $activity_id = $user->activity_id;
            if($user->firebase_user_key){
                $VnedorsList[] = [
                    'user_id'=>$user->id,
                    'user_device_token'=>$user->user_device_token,
                    'firebase_user_key'=>$user->firebase_user_key,
                    'activity_id'=>$user->activity_id,
                ];
            }
        }
        // if($activity_id){
        //     $all_vendors = User::where('activity_id',$activity_id)->get();
        //     if($all_vendors->count()){
        //         foreach($all_vendors as $user){
        //             if($user->firebase_user_key && !in_array($user->user_device_token, $device_token)){
        //                 $device_token[] = $user->user_device_token;
        //                 $VnedorsList[] = [
        //                     'user_id'=>$user->id,
        //                     'user_device_token'=>$user->user_device_token,
        //                     'firebase_user_key'=>$user->firebase_user_key,
        //                     'activity_id'=>$user->activity_id,
        //                 ];
        //             }
        //         }
        //     }
        // }
        if(count($VnedorsList)){
            $bd_data = [];
            $push_dat= [];
            
            $notification_id = time();
            foreach($VnedorsList as $vendor) {
                $in_data = [
                    "title" => $title,
                    "description" => $description,
                    "notificationType" => $ntype,
                    "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                    "orderId" => (string) $order_id,
                    "id" => (string) $item_id,
                    "url" => (string) $vendor['activity_id'],
                    "imageURL" => (string)$file_name,
                    "read" => "0",
                    "seen" => "0"
                ];
                if($vendor['user_device_token'] != "" ){
                    $push_dat[] = $vendor['user_device_token'];
                }
                if($vendor['firebase_user_key'] != "" ){
                    $bd_data["Notifications/".$vendor['firebase_user_key']."/".$notification_id] = $in_data;
                }
            }

            if(!empty($push_dat)) {
                $res = send_multicast_notification($push_dat,
                [
                    "title" => $title,
                    "body" => $description,
                    "icon" => 'myicon',
                    "sound" => 'default',
                    "click_action" => "EcomNotification",
                ],
                [
                    "type" => $ntype,
                    "notificationID" => $notification_id,
                    "orderId" => (string) $order_id,
                    "id" => (string) $item_id,
                    "url" => (string) $vendor['activity_id'],
                    "imageURL" => (string)$file_name,
                ]);
                //print_r($push_dat); exit;
            }
            if(!empty($bd_data)){
                $this->database->getReference()->update($bd_data);
            }
            return 0;
        }
    }
}
