<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderModel;
use App\Models\OrderProductsModel;
use App\Models\OrderServiceItemsModel;
use App\Models\OrderServiceModel;
use App\Models\UserAdress;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\User;

class ServiceCreation extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order =  OrderServiceModel::select("orders_services.*", "orders_services.status as order_status", "users.name as customer_name", "user_address.*")
            ->leftjoin('users', 'users.id', 'orders_services.user_id')
            ->where(['order_id' => $this->data])
            ->leftjoin('user_address', 'user_address.id', '=', 'orders_services.address_id')
            ->first();
        if (!empty($order)) {
            $order->service_details = OrderServiceItemsModel::select('orders_services_items.*', 'service.name', 'service.image', 'description')
                ->leftjoin('service', 'service.id', '=', 'orders_services_items.service_id')
                ->where('order_id', $order->order_id)->get();
        }

        return $this->subject('Order Create Success')
            ->from('admin@carepack.com')
            ->view('email_templates.order_success', compact('order'));
    }
}