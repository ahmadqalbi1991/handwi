<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\OrderProductsModel;
use App\Models\OrderModel;
use DB;
use Kreait\Firebase\Contract\Database;

class SendOrderNotification extends Command
{
    /**
     * The name and signature of the console command. 
     * 
     * @var string
     */
    protected $signature = 'order:send_order_notification {--uri=}';

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
        $providersList = [];
        $image = '';
        $file_name = '';
        $order_id   = $this->option("uri");

        $order = OrderModel::with(['products' => function ($qr) {
            $qr->select('order_products.*', 'product_attribute_id as product_variant_id', 'default_attribute_id','product_name')->join('product', 'product.id', 'order_products.product_id');
        }])->where('order_id', $order_id)->first();
        
          
        

        if(!empty($file_name))
        $file_name = $image;

        $usersListQry = OrderProductsModel::where('order_id',$order_id)->join('users','users.id','=','order_products.vendor_id')->whereNotNull('firebase_user_key')->distinct('users.id')->get();

        if(!empty($usersListQry))
        {
                    foreach($usersListQry as $user)
                    {
                        if(!empty($user->firebase_user_key))
                        {
                            $providersList[] = [
                                'user_id'=>$user->id,
                                'user_device_token'=>$user->user_device_token,
                                'firebase_user_key'=>$user->firebase_user_key,
                            ];
                        }
                    }
        }
                
               
        if(!empty($providersList))
        {
            $order_no = config('global.sale_order_prefix').date(date('Ymd', strtotime($order->created_at))).$order->order_id;
                    $bd_data = [];
                    $push_dat= [];
                    $title          = $order_no;
                    $description    = "Your order placed successfully.For more information, Please check the Order Status.";
                    $ntype          = 'order_received';
                    $notification_id = time();
                    
                    foreach($providersList as $seller)
                    {
                        
                        $in_data = [
                            "title" => $title,
                            "description" => $description,
                            "notificationType" => $ntype,
                            "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                            "orderId" => (string) $order_id,
                            "url" => "",
                            "imageURL" => (string)$file_name,
                            "read" => "0",
                            "seen" => "0"
                        ];
                        if($seller['user_device_token'] != "" ){
                            $push_dat[] = $seller['user_device_token'];
                        }
                        if($seller['firebase_user_key'] != "" ){
                            $bd_data["Notifications/".$seller['firebase_user_key']."/".$notification_id] = $in_data;
                        }
                        
                    }

                    if(!empty($push_dat))
                    {
                        
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
                            "imageURL" => (string)$file_name,
                        ]);
                        
                        //print_r($push_dat); exit;
                    }
                    if(!empty($bd_data))
                    {
                        $this->database->getReference()->update($bd_data);
                    }
                    return 0;
    }
}
}
