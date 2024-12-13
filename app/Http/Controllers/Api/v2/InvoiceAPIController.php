<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;


class InvoiceAPIController extends Controller
{
    public function __construct()
    {
        $this->api_key                                  = "test300@gmail.com";
        $this->api_secret                               = "test300@123";
        $this->app_key                                  = "MzM1ZjdkMmUzMzgxNjM1NWJiNWQwYzE3YjY3YjMyZDU5N2E3ODRhZmE5NjU0N2RiMWVjZGE0ZjE4OGM1MmM1MQ==";
        $this->client_secret                            = "cfd619c909";
        $this->base_url                                 = "https://aysha.erpgulf.com/api/method/invoice_sync.invoice_sync.invoice.";
        $this->access_token                             = "";
        $this->refresh_token                            = "";
        $this->customer_id                              = "";

        $this->generate_token_secure                    = $this->base_url.'generate_token_secure';    
        $this->create_refresh_token                     = $this->base_url.'create_refresh_token'; 
        $this->get_create_customer_url                  = $this->base_url.'customer';    
        $this->create_invoice_url                       = $this->base_url.'create_invoice';    
    }
    public function place_invoice($order)
    { 
        $order = \App\Models\OrderModel::with(['users','products' => function ($qr) {
                $qr->with('vendor.vendordata')->select('order_products.*', 'product_attribute_id as product_variant_id', 'default_attribute_id', 'product_name', 'order_products.vendor_id')->join('product', 'product.id', 'order_products.product_id');
            }])->where('order_id', $order->order_id ?? $order->id)->first();
        
        $res_token      = $this->generate_token_secure();
        $customer_id    = $this->create_customer($order->users);
        $supplier_id    = $this->create_customer($order->vendor);
        $invoice_id     = $this->create_invoice($order,$customer_id,$supplier_id);
        // dd($res_token,$customer_id,$supplier_id,$invoice_id);
        if($invoice_id){
          return true;  
        }
        return false;  
    }

    public function generate_token_secure()
    { 
        $this->auth_token_form_data                     = new \StdClass();
        $this->auth_token_form_data->api_key            = $this->api_key;
        $this->auth_token_form_data->api_secret         = $this->api_secret;
        $this->auth_token_form_data->app_key            = $this->app_key;
        $this->auth_token_form_data->client_secret      = $this->client_secret;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->generate_token_secure);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->auth_token_form_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; Host=<calculated when request is sent>'));
        $result = curl_exec($ch);
        $err = curl_error($ch);
        $json_result= json_decode($result,true);
        curl_close($ch);
        
        if($json_result != null){
            $this->access_token          = $json_result['data']['access_token'] ?? '';
            $this->refresh_token         = $json_result['data']['refresh_token'] ?? '';
            return $json_result;
        }
        return null;
    }

    public function create_customer($user)
    { 
        $this->create_user                     = new \StdClass();
        $this->create_user->customer           = $user->first_name.' '.$user->last_name;
        $this->create_user->customer_type      = 'Company';
        $this->create_user->phone              = $user->dial_code.$user->phone;
        $this->create_user->email              = $user->email;
        $this->create_user->is_supplier        = $user->role == 3 ? true : false;
        
        $url = $this->get_create_customer_url;
        $soap_do1 = curl_init();
        curl_setopt($soap_do1, CURLOPT_URL, $url );
        curl_setopt($soap_do1, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do1, CURLOPT_TIMEOUT, 10);
        curl_setopt($soap_do1, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do1, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do1, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do1, CURLOPT_POST, false );
        curl_setopt($soap_do1, CURLOPT_POST, 0);
        curl_setopt($soap_do1, CURLOPT_HTTPGET, 1);
        curl_setopt($soap_do1, CURLOPT_POSTFIELDS, json_encode($this->create_user));
        curl_setopt($soap_do1, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Accept: application/json','Authorization: Bearer '. $this->access_token));
        $result_in = curl_exec($soap_do1);
        $err_in = curl_error($soap_do1);
        $file_contents = htmlspecialchars(curl_exec($soap_do1));
        curl_close($soap_do1);
        $json_result = json_decode($result_in,true);
        // dd($json_result);
        if($json_result != null){
            if($this->create_user->is_supplier){
                if(isset($json_result['data']['supplier_details'][0]['id'])){
                    return $json_result['data']['supplier_details'][0]['id'] ?? '';
                }
                return $json_result['data']['Details'][0]['id'] ?? '';
            }
            if(isset($json_result['message']['customer_details'][0]['id'])){
                return $json_result['message']['customer_details'][0]['id'];
            }
            return $json_result['message']['Details'][0]['id'] ?? '';
        }
        return null;
    }
    public function create_invoice($order,$customer_id,$supplier_id)
    { 
        $this->create_invoice                     = new \StdClass();
        $this->create_invoice->customer_id        = $customer_id;
        $this->create_invoice->supplier_id        = $supplier_id;
        $this->create_invoice->payment_method     = $order->payment_mode == 5 ? 'cash' : 'card';

        foreach ($order->products as $pkey => $pval) {
            $items  = new \StdClass();
            $items->item_name           = $pval->product_name;
            $items->quantity            = $pval->quantity;
            $items->rate                = $pval->price;
            $this->create_invoice->items[] = $items;
        }
        $url = $this->create_invoice_url;
        $soap_do1 = curl_init();
        curl_setopt($soap_do1, CURLOPT_URL, $url );
        curl_setopt($soap_do1, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do1, CURLOPT_TIMEOUT, 10);
        curl_setopt($soap_do1, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do1, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do1, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do1, CURLOPT_POST, false );
        curl_setopt($soap_do1, CURLOPT_POST, 0);
        curl_setopt($soap_do1, CURLOPT_HTTPGET, 1);
        curl_setopt($soap_do1, CURLOPT_POSTFIELDS, json_encode($this->create_invoice));
        curl_setopt($soap_do1, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Accept: application/json','Authorization: Bearer '. $this->access_token));
        $result_in = curl_exec($soap_do1);
        $err_in = curl_error($soap_do1);
        $file_contents = htmlspecialchars(curl_exec($soap_do1));
        curl_close($soap_do1);
        $json_result = json_decode($result_in,true);

        // dd($json_result);
        if($json_result != null){
            return $json_result['message']['id'] ?? '';
        }
        return null;
    }
}