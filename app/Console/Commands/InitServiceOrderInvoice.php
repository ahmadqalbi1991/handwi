<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderServiceModel;
use App\Models\OrderServiceItemsModel;

class InitServiceOrderInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init_invoice:service_order {order_id}';

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
        $order_id = $this->argument('order_id');
        try {
            $invoice_api  = new \App\Http\Controllers\Api\v1\InvoiceAPIController();
            $res = $invoice_api->place_invoice_service($order_id);
            printr($res);
        } catch (\Exception $e) {
            echo $e->getMessage()." ".$e->getLine();
        }
        return 0;
    }
}
