<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\User;

class SendVerificationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:send_verification_email {--uri=} {--uri2=} {--uri3=} {--uri4=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Verification Email';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $subject = urldecode($this->option("uri"));
        $to =  urldecode($this->option("uri2"));
        $otp = $this->option("uri3");
        // $user = User::find($user);
        $name = urldecode($this->option("uri4"));
        $mailbody = view('email_templates.verify_mail', compact('otp', 'name'))->render();
        // $mailbody = view('mail.registration_successful', compact('user'))->render();
        //echo $b64image = base64_encode(file_get_contents('https://amtopmservices.com/app_service/public/admin-assets/assets/img/logo.svg'));
        send_email($to, $subject, $mailbody);
    }
}
