<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;

class SendForgotEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:send_forgot_email {--uri=} {--uri2=} {--uri3=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Forgot Email';

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
        $subject = env('APP_NAME').' Forgot Password';
        $to =  urldecode($this->option("uri"));
        $otp = $this->option("uri2");
        $name = urldecode($this->option("uri3"));
        $mailbody = view('mail.forgot_mail', compact('otp', 'name'));
        send_email($to, $subject, $mailbody);
    }
}
