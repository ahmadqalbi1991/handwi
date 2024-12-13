<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;

class SendRegEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:send_reg_email {--uri=} {--uri2=} {--uri3=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Registration Email';

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
        $name = urldecode($this->option("uri3"));
        $mailbody = view('email_templates.registration_mail', compact('name'));
        send_email($to, $subject, $mailbody);
    }
}
