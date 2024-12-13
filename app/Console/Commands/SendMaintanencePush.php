<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maintainance;
use Kreait\Firebase\Contract\Database;

class SendMaintanencePush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintanence_push:self {id}';

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
      $id = $this->argument('id');
      $like = Maintainance::with('user')->where(['id'=>$id])->get();
      if($like->count() > 0){
        $like = $like->first();
        $nottification_id = time();
        $nottifcation_items = [];
        $fcm_tokens_taged = $device_tokens = [];
        $nodeData = [
          'notificationType'     => 'maintenance_quote_created',
          'orderId'  => (string)$like->id,
          'description'=>'Request Placed Successfully',
          'title'    => " Request Placed Successfully ",
          'createdAt'     => (string)get_date_in_timezone($like->created_at,'d-m-Y H:i:s'),
          'imageUrl' => '',
          'time'    => $nottification_id,
          'read'=> '0',
          'seen'=> '0',
        ];
        $title = "Maintainance Request";
        $description = " Request Placed Successfully ";
        if($like->user->firebase_user_key != ''){
          $this->database->getReference('Notifications/'.$like->user->firebase_user_key.'/'.$nottification_id.'/')->update($nodeData);
        }
        $ntype          = 'maintenance_quote_created';
        if($like->user->user_device_token != ''){
          $res = send_single_notification($like->user->user_device_token,
                       [
                           "title" => $title,
                           "body" => $description,
                           "icon" => 'myicon',
                           "sound" => 'default',
                           "click_action" => "EcomNotification",
                       ],
                       [
                           "type" => $ntype,
                           "notificationID" => $nottification_id,
                           'orderId'  => (string)$like->id,
                           "imageURL" => "",
                       ]);
        }
        


      }
        return 0;
    }
}
