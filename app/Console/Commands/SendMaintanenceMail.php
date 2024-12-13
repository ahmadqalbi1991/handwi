<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maintainance;
use Kreait\Firebase\Contract\Database;

class SendMaintanenceMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintanence_mail:self {id}';

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
      $like = Maintainance::with('user')->where(['id'=>$id])->first();
      
      if(!empty($like)){
        
        $subject = "Maintainance Request Placed Successfully";
        $to =  $like->user->email;
        $user = $like->user;
        $name = $like->user->name;
        $mailbody = view('mail.maintainance_request', compact('user'))->render();
        
        send_email($to, $subject, $mailbody);

      }
        return 0;
    }
}
