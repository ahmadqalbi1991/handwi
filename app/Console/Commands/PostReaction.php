<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Database;
use App\Models\PostLikes;

class PostReaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:post_reaction {reaction_id} {option}';

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
      $reaction_id = $this->argument('reaction_id');
      $option = $this->argument('option');

      $data = PostLikes::with('post','liked_user')->where(['id'=>$reaction_id])->get();
      if($data->count() > 0){
         $data= $data->first();
         if($option =='dislike'){
           $fb_user_refrence = $this->database->getReference('SocialPostReactions/'.$data->post->post_firebase_node_id.'/'.$data->liked_user->firebase_user_key.'/')->remove();
           $fb_user_refrence = $this->database->getReference('SocialPostReactionCounters/'.$data->post->post_firebase_node_id.'/like/'.$data->liked_user->firebase_user_key.'/')->remove();
         }else{
            $fb_user_refrence = $this->database->getReference('SocialPostReactions/'.$data->post->post_firebase_node_id.'/'.$data->liked_user->firebase_user_key.'/')->update(['reaction'=>'like']);
            $fb_user_refrence = $this->database->getReference('SocialPostReactionCounters/'.$data->post->post_firebase_node_id.'/like/'.$data->liked_user->firebase_user_key.'/')->update(['reacted_at'=>strtotime($data->created_at)]);
          }
        }else{

        }
        return 0;
    }
}
