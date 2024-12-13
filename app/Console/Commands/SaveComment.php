<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Database;
use App\Models\PostComment;

class SaveComment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:save_comment {comment_id}';

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



    private function process_comment_data($data){
      $return = [
        'comment_id'    => (string)$data->id,
        'comment'       => $data->comment,
        'parent_id'     => (string)$data->parent_id,
        'created_at'    => $data->created_at,
        'updated_at'    => $data->updated_at
      ];
      if(isset($data->post) && !empty($data->post)){
        $return['post_firebase_node_id'] = $data->post->post_firebase_node_id;
      }
      if(isset($data->taged_users) && !empty($data->taged_users)){
        $return['comment_taged_users'] = [];
        foreach ($data->taged_users as $userKey ) {
          $return['comment_taged_users'][]= [
            'id'        => (string)$userKey->user->id,
            'name'      => $userKey->user->name,
            'user_image'=> $userKey->user->user_image,
            'firebase_user_key'=>$userKey->user->firebase_user_key
          ];
        }
      }

      return $return;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $comment_id = $this->argument("comment_id");
      $data = PostComment::with('post','post.user','taged_users.user')->where(['id'=>$comment_id])->get()->first();
      $o_data = $this->process_comment_data($data);
      //firebasei

      if($data->comment_node_id == ''){
        $fb_user_refrence = $this->database->getReference('PostComments/'.$o_data['post_firebase_node_id'].'/')
            ->push($o_data);
        $comment = PostComment::find($comment_id);
        $comment->comment_node_id = $fb_user_refrence->getKey();
        $comment->save();
      }else{
        $fb_user_refrence = $this->database->getReference('PostComments/'.$o_data['post_firebase_node_id'].'/')
            ->update([$data->comment_node_id => $o_data]);
      }
        return 0;
    }
}
