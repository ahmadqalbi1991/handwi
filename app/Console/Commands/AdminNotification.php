<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Database;
use App\Models\CommentLikes;
use App\Models\User;
use App\Models\Notifications;
class AdminNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:admin_notification {--uri=} {--uri2=} {--uri3=}';

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

      $usertype = $this->option("uri");
      $notificationsid = $this->option("uri2");
      $image = $this->option("uri3");

      $where['deleted'] = 0; 
                if($usertype)
                {
                $where['role'] = $usertype;     
                }
      
      $notifications = Notifications::find($notificationsid);
      $users = User::where($where)->whereNotNull('firebase_user_key')->get();         
                
      foreach ($users as $key =>  $user) {
        $user  = (object)$user;
        $title = $notifications->title;
        $description = $notifications->description;
        $ntype = 'public-notification';
        if(isset($user->user_device_token)){
        
         prepare_notification($this->database,$user, $title, $description, $ntype,$notificationsid ,'service',$image);
        }
    }

     
     }
}
