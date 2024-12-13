<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Database;
use App\Models\Post;
use App\Models\User;
use App\Models\UserFollow;
class SendPostNottification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send_nottification:post {post_id}';

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
       $post_id = $this->argument('post_id');
       $post =Post::with('post_users','user')->where(['id'=>$post_id])->get();
       if($post->count() > 0){
         $post = $post->first();
         $followers_ids = [];
         $follower_id_value = [];
         $taged_ids     = [];

         $post_added_by = $post->user;
         foreach($post->post_users as $tagUser){
           $taged_ids[] = $tagUser->user_id;
         }

         $followers = UserFollow::with('follower')->where(['follow_user_id'=>$post_added_by->id])->get();
         if($followers->count() > 0){
           foreach($followers as $followUser){
             //printr($followUser);
              $followers_ids[] = $followUser->follower->id;
              $follower_id_value[$followUser->follower->id] = $followUser->follower->toArray();
           }

         }

         foreach($taged_ids as $tagKey){
           if(in_array($tagKey,$followers_ids)){
             if(isset($follower_id_value[$tagKey])){
               unset($follower_id_value[$tagKey]);
             }
           }
         }

         //post to firebase nottification table for follower
         $nottification_id = time();
         $nottifcation_items = [];
         $fcm_tokens_taged = $fcm_tokens = [];
         $nodeData = [
           'type'     => 'post',
           'post_id'  => $post_id,
           'title'    => $post->user->name." added new post ",
           'post_firebase_key' => $post->post_firebase_node_id,
           'createdDate'     => $post->created_at,
           'imageUrl' => $post->file,
           'file_type' => $post->file_type,
           'time'    => $nottification_id
         ];
         $title = "New post";
         $description = $post->user->name." added new post ";
         foreach($follower_id_value as $key){
           if($key['firebase_user_key'] != ''){
              $nottifcation_items[$key['firebase_user_key'].'/'.$nottification_id] = $nodeData;
            }
            if($key['user_device_token'] != ''){
              $fcm_tokens[] = $key['user_device_token'];
            }
         }

         //get tag user details
         $tag_user_lists = User::whereIn('id',$taged_ids)->get();
         foreach($tag_user_lists as $key){
           if($key->firebase_user_key != ''){
             $nodeData['title'] = $post->user->name." tagged you in a post";
             $nottifcation_items[$key->firebase_user_key.'/'.$nottification_id] = $nodeData;
           }
           if($key->user_device_token != ''){
             $fcm_tokens_taged[] = $key->user_device_token;
           }
         }
         //printr($nottifcation_items);
         if(!empty($nottifcation_items)){
           $this->database->getReference('Nottification/')->update($nottifcation_items);
         }
         $ntype          = 'post-notification';
         if(!empty($fcm_tokens)){
           $res = send_multicast_notification($fcm_tokens,
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
                            "imageURL" => (string)$post->file,
                        ]);
         }
         if(!empty($fcm_tokens_taged)){
           $decription = $post->user->name." tagged you in a post";
           $res = send_multicast_notification($fcm_tokens_taged,
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
                            "imageURL" => (string)$post->file,
                        ]);
         }
       }
       return 0;
    }
}
