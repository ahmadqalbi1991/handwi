<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\OrderServiceItemsModel;
use App\Models\OrderServiceModel;
use App\Models\ServiceAssignedVendors;
use App\Models\SettingsModel;
use App\Models\CouponVendorServiceOrders;
use DB;
use Kreait\Firebase\Contract\Database;

class SendServiceNotification extends Command
{
    /**
     * The name and signature of the console command. 
     * 
     * @var string
     */
    protected $signature = 'order:send_service_notification {--uri=} {--uri2=}';

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
        $item_id   = $this->option("uri2");

        $service_order = OrderServiceModel::find($order_id);
        $latitude = $service_order->user_latitude;
        $longitude = $service_order->user_longitude;

        $service_radius = 50;

        $settings = SettingsModel::first();
        if (isset($settings->service_radius)) {
            $service_radius = $settings->service_radius;
        }

        $vendor_services =  OrderServiceItemsModel::select('service_id')->where('order_id',$order_id)->get()->toArray();
        $vendor_services = $serar = array_column($vendor_services, 'service_id');
        $vendor_services = implode(",",$vendor_services);

        

        
        $coupon_vendor = CouponVendorServiceOrders::select('vendor_id')->where(['order_id'=>$order_id])->pluck('vendor_id')->toArray();
       

        if(!empty($file_name))
        $file_name = $image;
        //DB::enableQueryLog();
        $usersListQry = DB::table('vendor_services')->whereIn('vendor_services.service_id',explode(",", $vendor_services))
        ->join('users','users.id','=','vendor_services.vendor_id')
        ->join('vendor_locations','vendor_locations.user_id','=','users.id')
        ->whereNotNull('vendor_locations.longitude')->distinct('users.id');

        $distance =
            "6371 * acos (
            cos ( radians( CAST (vendor_locations.latitude AS double precision) ) )
            * cos( radians( CAST ({$latitude} AS double precision) ) )
            * cos( radians( CAST ({$longitude} AS double precision) ) - radians( CAST (vendor_locations.longitude AS double precision) ) )
            + sin ( radians( CAST (vendor_locations.latitude AS double precision) ) )
            * sin( radians ( CAST ({$latitude} AS double precision) ) )
        )";
        $usersListQry->selectRaw("$distance as distance")->addSelect('users.*')->orderBy('users.id','desc')->orderBy('distance','asc');
        $usersListQry->whereRaw("$distance<=$service_radius");
        if(!empty($coupon_vendor))
        {
            $usersListQry = $usersListQry->whereIn('vendor_services.vendor_id',$coupon_vendor);
        }
        
        
        // if($order_id=570){
        //     echo $usersListQry->toSql(); exit;
        //     dd(DB::getQueryLog());
        // }
        $usersListQry = $usersListQry->get();
        //printr($usersListQry->toArray()); 

        if(!empty($usersListQry))
        {
            ServiceAssignedVendors::where(['order_id'=>$order_id])->delete();
                    foreach($usersListQry as $user)
                    {
                        $vendor_item = new ServiceAssignedVendors();
                        $vendor_item->vendor_user_id = $user->id;
                        $vendor_item->order_id= $order_id;
                        $vendor_item->service_status = 0;
                        $vendor_item->created_at = gmdate('Y-m-d H:i:s');
                        $vendor_item->updated_at = gmdate('Y-m-d H:i:s');
                        $vendor_item->save();

                        
                        if(!empty($user->firebase_user_key))
                        {
                            $providersList[] = [
                                'user_id'=>$user->id,
                                'user_device_token'=>$user->user_device_token,
                                'firebase_user_key'=>$user->firebase_user_key,
                                'service_id'=>$user->service_id??0,
                            ];
                        }
                    }
        }
                
               
        if(!empty($providersList))
        {
                    $bd_data = [];
                    $push_dat= [];
                    $title          = $service_order->order_no;
                    $description    = "Received a service order";
                    $ntype          = 'service_order_received';
                    $notification_id = time();
                    
                    foreach($providersList as $seller)
                    {
                        
                        $in_data = [
                            "title" => $title,
                            "description" => $description,
                            "notificationType" => $ntype,
                            "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                            "orderId" => (string) $order_id,
                            "id" => (string) $item_id,
                            "url" => (string) $seller['service_id'],
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
                            "id" => (string) $item_id,
                            "url" => (string) $seller['service_id'],
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
