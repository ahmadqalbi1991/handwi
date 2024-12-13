<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Database;
use App\Models\User;
use App\Models\UserFollow;
class SendNottificationFollowUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send_nottification:follow {follow_id}';

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
        $follow_id = $this->argument('follow_id');

        $follow_row = UserFollow::with('followed')->where(['id'=>$follow_id])->get();
        if($follow_row->count() > 0){
          $follow_row = $follow_row->first();
          $my_user_id = $follow_row->user_id;
          $followed_user_id = $follow_row->follow_user_id;
          $nottification_id= time();

          //check he followed u:a
          $check = UserFollow::where(['user_id'=>$follow_row->follow_user_id,'follow_user_id'=>$my_user_id])->get();
          if($check->count() > 0){
            $description = $follow_row->followed->user_name." followed you back";
          }else{
            $description = $follow_row->followed->user_name." started following you";
          }

          $nodeData = [
            'type'     => 'user_follow',
            'follow_id'  => $follow_id,
            'title'    => $description,
            'createdDate'     => $follow_row->created_at,
            'imageUrl' => $follow_row->followed->user_image,
            'file_type' => 1,
            'time'    => $nottification_id
          ];
          $this->database->getReference('Nottification/'.$follow_row->followed->firebase_user_key.'/'.$nottification_id.'/')->update($nodeData);
          $ntype          = 'user-follow-notification';
          $title = "User follow";
          if($follow_row->followed->user_device_token != ''){
          $res = send_single_notification($follow_row->followed->user_device_token,
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
                           "key_id" => $my_user_id,
                           "imageURL" => (string)$follow_row->followed->user_image,
                       ]);
          }
        }
        return 0;
    }
}
