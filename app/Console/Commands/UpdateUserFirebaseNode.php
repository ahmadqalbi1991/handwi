<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Database;
class UpdateUserFirebaseNode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:firebase_node {user_id}';

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
        $user_id = $this->argument('user_id');
        $user = \App\Models\User::where(['id'=>$user_id])->get();
        if($user->count() > 0){
          $user = $user->first();
          $this->database->getReference('Users/' . $user->firebase_user_key . '/')->update([
              'fcm_token' => $user->fcm_token,
              'user_name' => $user->name ?? $user->first_name.' '.$user->last_name,
              'email'     => $user->email,
              'user_id'   => $user->id,
              'user_image'=> $user->user_image,
              'dial_code' => $user->dial_code,
              'phone'     => $user->phone
          ]);
        }
        return 0;
    }
}
