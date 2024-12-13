<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Database;
use App\Models\Post;
use App\Models\User;
use App\Models\UserFollow;
class SavePost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:save_post {post_id}';

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
    private function process_post_data($data=[]){
      $result = [
        'post_id'     => (string)$data->id,
        'caption'     => $data->caption,
        'file'        => $data->file,
        'file_type'   => $data->file_type,
        'location_name' => $data->location_name,
        'lattitude'   => $data->lattitude,
        'longitude'   => $data->longitude,
        'created_at'  => $data->created_at,
        'updated_at'  => $data->updated_at,
        'user_id'     => (string)$data->user_id,
        'author'      => [],
        'comments'    => [],
        'comments_count' => (string) $data->comments_count??0,
        'likes_count' => (string) $data->likes_count??0,
        'visibility'  => (string) $data->visibility,
        'extra_file_names' => $data->extra_file_names,
        'active'      => (string) $data->active
      ];
      if(isset($data->user) && !empty($data->user)){
        $result['user_firebase_key'] = $data->user->firebase_user_key;
        $result['author'] = [
          'name'    => $data->user->name,
          'user_image' => $data->user->user_image,
          'author_firebase_key'=>$data->user->firebase_user_key
        ];
      }
      if(isset($data->comments) && !empty($data->comments)){
        $result['comments'] = $data->comments;
      }
      if(isset($data->post_users) && !empty($data->post_users)){
        $result['taged_users'] = [];
        foreach ($data->post_users as $userKey ) {
          $result['taged_users'][]= [
            'id'        => (string)$userKey->user->id,
            'name'      => $userKey->user->name,
            'user_image'=> $userKey->user->user_image,
            'firebase_user_key'=>$userKey->user->firebase_user_key
          ];
        }
      }
      return $result;
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $post_id = $this->argument('post_id');
      $data  = Post::with('post_users','post_users.user','user')->find($post_id);
      $o_data = $this->process_post_data($data);
      if($data->post_firebase_node_id ==''){
        //friebase insert
        $fb_user_refrence = $this->database->getReference('SocialPosts/')
            ->push($o_data);

        $post = Post::find($post_id);
        $post->post_firebase_node_id = $post_node= $fb_user_refrence->getKey();
        $post->save();
        $userNode=[];
        foreach ($o_data['taged_users'] as $key => $value) {
          // code...
          $userNode[$post_node][] = $value['firebase_user_key'];
        }
        if(!empty($userNode)){
          $this->database->getReference('SocialPostTags/'.$post_node)
              ->remove();
          $fb_user_refrence = $this->database->getReference('SocialPostTags/')
              ->update($userNode);
        }
      }else{
        $fb_user_refrence = $this->database->getReference('SocialPosts/')
            ->update([$data->post_firebase_node_id => $o_data]);
            $userNode=[];
            $post_node = $data->post_firebase_node_id;
            foreach ($o_data['taged_users'] as $key => $value) {
              // code...
              $userNode[$post_node][] = $value['firebase_user_key'];
            }
            if(!empty($userNode)){
              $this->database->getReference('SocialPostTags/'.$post_node)
                  ->remove();
              $fb_user_refrence = $this->database->getReference('SocialPostTags/')
                  ->update($userNode);
            }
            $message = "Post updated Successfully";
      }
      if($data->visibility =='public'){
        $fb_user_refrence = $this->database->getReference('SocialPublicPosts/'.$post_node.'/')
            ->update([
              'created_at' => strtotime($o_data['created_at']),
              'posted_user_id'=>(string)$o_data['user_id']
            ]);
        $fb_user_refrence = $this->database->getReference('SocialUserPosts/'.$o_data['author']['author_firebase_key'].'/'.$post_node)
                ->update([
                  'created_at'  => strtotime($o_data['created_at']),
                  'posted_user_id'=>(string)$o_data['user_id'],
                  'posted_user_node'=>(string) $o_data['author']['author_firebase_key']
                ]);
      }else{
        $this->database->getReference('SocialPublicPosts/'.$post_node)
            ->remove();
      }

      if($data->visibility =='followers'){
        $followers = UserFollow::with('follower')->where(['follow_user_id'=>$data->user_id])->get();
        $user_nodes = [];
        foreach($followers as $follow){
          if($follow->follower->firebase_user_key){
            $user_nodes[$follow->follower->firebase_user_key.'/'.$post_node] = [
              'created_at'  => strtotime($o_data['created_at']),
              'posted_user_id'=>(string)$o_data['user_id'],
              'posted_user_node'=>(string) $o_data['author']['author_firebase_key']
            ];
          }
        }
        if($data->user){
          $user_nodes[$data->user->firebase_user_key.'/'.$post_node] = [
            'created_at'  => strtotime($o_data['created_at']),
            'posted_user_id'=>(string)$o_data['user_id'],
            'posted_user_node'=>(string) $o_data['author']['author_firebase_key']
          ];
        }
        //printr($user_nodes);
        if(!empty($user_nodes)){
          $fb_user_refrence = $this->database->getReference('SocialUserPosts/')
              ->update($user_nodes);
        }
      }
        return 0;
    }
}
