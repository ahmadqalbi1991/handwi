<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderModel;
use App\Models\OrderProductsModel;
use App\Models\UserAdress;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\User;

class OrderStatusChange extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $order_status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data,$order_status)
    {
        $this->data = $data;
        $this->order_status = $order_status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order = OrderModel::find($this->data)->first();
     
        $products = DB::select('select product_name, image,price,op.discount as discount,quantity, op.total as total from  orders o
        left join order_products op on op.order_id = o.order_id
        left join  product p on p.id =  op.product_id
        left join public.product_selected_attribute_list pal on pal.product_id = p.id
        where o.order_id =' . $this->data );

        foreach($products as $product){
            foreach(explode(',',$product->image) as $image)
            {
                $product->image  = $image;
            }
        }
        
        if( $order['family_type_ids']){
            $familyAddresses=array();
            foreach($order['family_type_ids'] as $familytype){
                $userAddress =   DB::select('select u.name as name,nin_no,ft.name as family_type_name, email,u.phone as phone ,ua.address as address from users u 
                left join user_address ua on u.id = ua.user_id 
                left join family_types ft on ft.id = u.family_type_id
                where u.id ='.$familytype['id']);
                array_push($familyAddresses,$userAddress);
            }
        }else{
             $familyAddresses = [];
        }

        $receiver =  UserAdress::get_address_details($order['address_id']);

        $purchaser = User::where(['id' => $order['user_id'],'deleted' => 0])->first();

        $packageStyle = DB::select('select DISTINCT op.order_id, ps.name as package_name, p.name as parent_category, ps.image as image from order_products op
        left join package_style ps on ps.id = op.package_style_id
        left join package_style p on p.id = ps.parent_id
        where op.order_id = '.$order['order_id']);

        return $this->subject('Order Create Success')
        ->from('admin@carepack.com')
        ->view('email_templates.order_status_changes',compact('order','products','familyAddresses','receiver','purchaser','packageStyle'));
    }
}