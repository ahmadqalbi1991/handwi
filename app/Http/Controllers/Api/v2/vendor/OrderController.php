<?php

namespace App\Http\Controllers\Api\v2\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderChangeStatusRequest;
use App\Http\Requests\Api\OrderDetailRequest;
use App\Models\OrderModel;
use App\Models\OrderProductsModel;
use App\Models\OrderServiceModel;
use App\Models\OrderStatusHistory;
use App\Models\Sku;
use App\Models\SkuImage;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Ordersnew;
use App\Models\OrdersHistory;
use App\Models\UserAdress;
use Illuminate\Support\Facades\DB;
use Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrderController extends Controller
{

    public function list(Request $request)
    {
        $user_id = $this->validateAccesToken($request->access_token);
        $vendor_id = $user_id;

        
        $page_no= $request->page_no??1;
        $limit = $request->limit;
        $offset = ($page_no - 1) * $limit;

        $orders = OrderModel::select('order_products.*', 'users.name', 'orders.*','users.dial_code','users.phone', DB::raw("CONCAT(users.first_name,' ',users.last_name) as customer_name"),'orders.order_id as id')->join('order_products', 'order_products.order_id', '=', 'orders.order_id')
            ->leftjoin('user_address', 'user_address.user_id', '=', 'orders.user_id')
            ->leftjoin('users', 'users.id', 'orders.user_id');
        
        if ($request->order_status >= 0) {
           $orders = $orders->where('orders.status', $request->order_status);
        }
        $orders = $orders->where('order_products.vendor_id','=',$vendor_id);
        
        $total = $orders->get()->count();
        $orders = $orders->orderBy('orders.order_id','desc')->distinct('orders.order_id');
        if($request->limit && $request->page_no)
        {
            $orders = $orders->limit($limit)->skip($offset);
        }
        $orders =$orders->get();


        foreach ($orders as $key=>$order){
         
            $orders[$key]->status_text = order_status($order->status);
            $orders[$key]->order_no = config('global.sale_order_prefix').date(date('Ymd', strtotime($order->created_at))).$order->order_id;
            $orders[$key]->payment_mode_id = (string) $order->payment_mode;
            $orders[$key]->payment_mode = payment_mode($order->payment_mode);
            $orders[$key]->booking_date = get_date_in_timezone($order->booking_date, 'Y-m-d H:i:s');
            $orderProductDetail  = OrderProductsModel::leftjoin('product as p','p.id','=','order_products.product_id')
                ->select(
                'order_products.product_id as product_id',
                'order_products.product_attribute_id',
                'order_products.quantity as order_product_qty',
                'order_products.price',
                'p.product_name as product_name',
                'pas.product_full_descr',
                'pas.image')
            ->leftjoin('product_selected_attribute_list as pas','pas.product_attribute_id','=','order_products.product_attribute_id')
            ->where('order_products.order_id' ,'=',$order->order_id)->get();
            foreach ($orderProductDetail as $key => $value) {
                
                if($value['image']) {
                    $images = $value['image'];
                    if ($images) {
                        $images = explode(',', $images);
                        $i = 0;
                        $prd_img = [];

                        foreach ($images as $img) {
                            if ($img) {
                                $prd_img[$i] = url(config('global.upload_path') . '/' . config('global.product_image_upload_dir') . $img);
                                $i++;
                            }
                        }
                        $orderProductDetail[$key]['image'] = $prd_img;
                    } else {
                        $orderProductDetail[$key]['image'] = [];
                    }
                }
                else
                {
                    $orderProductDetail[$key]['image'] = [];
                }

                
            }
            
            

            $order->productDetail = $orderProductDetail;
            if(!$order->order_type){
                $order->billing_address = UserAdress::find($order->address_id);
            }
            

        }
        $pagination['current_page'] =$page_no;
        $pagination['total']  = $total;
        $pagination['limit']  = $limit;
        return $this->successPaginatedResponse2(convert_all_elements_to_string($orders), 'Orders list',200,$pagination);

    }
    function successPaginatedResponse2($data, $message = "Success Response", $code = 200,$pagination=[]): JsonResponse
    {
        return new JsonResponse(
            [
                'status' => "1",
                'message' => $message,
                'errors' => [],
                'oData' => (object) $data,
                'pagination' => [
                    'page' => (string)$pagination['current_page'],
                    'total' => (string)$pagination['total'],
                    'perPage' => (string)$pagination['limit'],
                ],
                'errors' => (object)[]
            ], 200);
    }
    public function order_details(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $user_id = $this->validateAccesToken($request->access_token);
        $vendor_id = $user_id;

        

        
        $orders = OrderModel::select('order_products.*', 'users.name', 'orders.*','users.dial_code','users.phone', DB::raw("CONCAT(users.first_name,' ',users.last_name) as customer_name"),'orders.order_id as id')->join('order_products', 'order_products.order_id', '=', 'orders.order_id')
            ->leftjoin('user_address', 'user_address.user_id', '=', 'orders.user_id','orders.status as status')
            ->leftjoin('users', 'users.id', 'orders.user_id');
        
        if ($request->order_status > 0) {
           $orders = $orders->where('order_products.order_status', $request->order_status);
        }
        $orders = $orders->where('order_products.order_id','=',$request->order_id);
        
        $total = $orders->get()->count();
        $orders = $orders->orderBy('orders.order_id','desc')->distinct('orders.order_id')->first();
        
        if(!empty($orders))
        {
        $orders->status_text = order_status($orders->status);
        $orders->order_no = config('global.sale_order_prefix').date(date('Ymd', strtotime($orders->created_at))).$orders->order_id;
        $orders->payment_mode_id = (string) $orders->payment_mode;
        $orders->payment_mode = payment_mode($orders->payment_mode);
        $orders->booking_date = get_date_in_timezone($orders->booking_date, 'Y-m-d H:i:s');
        
       


            $orderProductDetail  = OrderProductsModel::leftjoin('product as p','p.id','=','order_products.product_id')
                ->select(
                'order_products.product_id as product_id',
                'order_products.product_attribute_id',
                'order_products.quantity as order_product_qty',
                'order_products.price',
                'p.product_name as product_name',
                'pas.product_full_descr',
                'pas.image')
            ->leftjoin('product_selected_attribute_list as pas','pas.product_attribute_id','=','order_products.product_attribute_id')
            ->where('order_products.order_id' ,'=',$orders->order_id)->get()->toArray();
            foreach ($orderProductDetail as $key => $value) {
                $orderProductDetail[$key]['image'] = [];
                if($value['image']) {
                    $images = $value['image'];
                    if ($images) {
                        $images = explode(',', $images);
                        $i = 0;
                        $prd_img = [];

                        foreach ($images as $img) {
                            if($img) {
                                $prd_img[$i] = url(config('global.upload_path') . '/' . config('global.product_image_upload_dir') . $img);
                                $i++;
                            }
                        }
                        $orderProductDetail[$key]['image'] = $prd_img;
                    } else {
                        $orderProductDetail[$key]['image'] = [];
                    }
                }
                
                $product_attributes_full = \App\Models\ProductModel::getSelectedProductAttributeValsFull($value['product_attribute_id']);
                if($product_attributes_full){
                        $orderProductDetail[$key]['selected_attribute_list'] = $product_attributes_full->toArray();
                }else{
                    $orderProductDetail[$key]['selected_attribute_list'] = [];
                }
            }
            
            
            $o_data = $orders;
            $o_data['productDetail'] = $orderProductDetail;
            if(!$orders->order_type){
                $o_data['billing_address'] = UserAdress::find($orders->address_id);
            }
            
            // $o_data['billing_address'] = UserAdress::find($orders->address_id);
            $o_data = convert_all_elements_to_string($o_data);
            }
            else
            {
                $status = "0";
                $message  ="Invalid order id";
                $o_data  = (object)[];
            }

        
        
        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);

    }


    public function show(OrderDetailRequest $request)
    {
        $order_id = $request->id;
        $address = [];
        
        $userid = $this->validateAccesToken($request->access_token);
        $user = User::find($userid);

        $store_id = $user->id;


        $order = OrderModel::with('transaction', 'trader', 'orderProducts', 'orderProducts.product')
            ->orderBy('order_id')
            ->where(['order_id' => $order_id, 'store_id' => $store_id]);

        if ($order->count() > 0) {
            $order = $order->first();
            $data = [];


            $data['order'] = [
                'order_id' => $order->order_id,
                'order_number' => $order->order_id,
                'total_price' => $order->total,
                'created_at' => $order->created_at,
                'item_total' => $order->orderProducts->count(),
                'delivery_charge' => $order->shipping_charge,
                'tax' => $order->vat,
                'discount' => $order->discount,
                'order_status' =>$order->status,
                'order_message' => $order->order_message,
                'payment_mod' => ($order->payment_mode == 1  )? 'COD' : 'Card',
                'grand_total' => $order->grand_total,
                'order_status' =>$order->status,
                'order_status_text' => order_status($order->status),
                'is_muted'=>$order->is_muted
            ];

            $data['trader'] = [
                'trader_id' => $order->trader->id,
                'trader_name' => $order->trader->name,
                'trader_image' => $order->trader->user_image,
                'trader_phone_code' => $order->trader->dial_code,
                'trader_phone_number'  => $order->trader->phone,
                'trader_country' => $order->trader->country->name??'',
                'trader_city' => $order->trader->city->name??'',
                'trader_area' => $order->trader->area
            ];

            $productDetails  = DB::table('order_products_new as op')
                ->join('product as p','p.id','=','op.product_id')
                ->join('product_selected_attribute_list as psl','psl.product_attribute_id','=','op.product_attribute_id')
                ->leftjoin('users as u','u.id','=','p.product_vender_id')
                ->select(
                    'op.product_id as product_id',
                    'op.quantity as order_product_qty',
                    'op.product_attribute_id as product_attribute_id',
                    'op.price as order_product_price',
                    'op.total as price_total',
                    'p.product_name as product_name',
                    'u.name as distributor_name',
                    'psl.image'
                )
                ->where('op.order_id' ,'=',$order->order_id)->get();

            $productDetails=$productDetails->toArray();
            foreach ($productDetails as $productDetail){

                // $productSelectedAttribute =  DB::table('product_selected_attribute_list as psal')
                //     ->select('psal.image as image')
                //     ->where('psal.product_attribute_id',$productDetail->product_attribute_id)
                //     ->first();

                // update imag

                $images = explode(",",$productDetail->image);
                $images = array_filter($images);
                if(empty($images)){
                    $image = url('/').'/admin-assets/assets/img/logo.png';
                }else{
                    $image = generate_public_image_url().'/uploads/products/'.$images[0];
                }
                $productDetail->image = $image;
                //echo $image;
                // if(file_exists($image)){
                //     $productDetail->image = $image;
                // }else{
                //     $productDetail->image = url('/').'/admin-assets/assets/img/logo.png';
                // }

                $orderNew = OrderModel::find($order->order_id);
                $address = $orderNew->address;

                $address = $address->makeHidden('id','user_id','is_default','status','country_id','city_id','state_id',
                'created_at','updated_at','full_name','dial_code','phone','address_type')->toArray();

                $billingAmount = [];

                $billingAmount['item_total'] = $orderNew->total;
                $billingAmount['delivery_charges'] = $orderNew->shipping_charge;
                $billingAmount['taxes_charges'] = $orderNew->vat;
                $billingAmount['mode_of_payment'] = $orderNew->payment_mode;
                $billingAmount['mode_of_payment_text'] =  ($orderNew->payment_mode == 1  )? 'COD' : 'Card';
                $billingAmount['grand_total'] =  $orderNew->grand_total;
                $billingAmount['discount'] =  $orderNew->discount;


            }

            $data['products_list'] = $productDetails;
            $data['billing_info']  = $address;
            

            return $this->successResponse($data);
        } else {
            return $this->notFoundResponse();
        }

    }

    public function change_status(OrderChangeStatusRequest $request)
    {
       
         $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        // update on order table
        $userid = $this->validateAccesToken($request->access_token);
        $user = User::find($userid);
        $name = $users->name ?? $user->first_name . ' ' . $user->last_name;

        $store_id = $user->id;
        
        // update payment withdraw status to comfirm in cod payment menthod
            $order = OrderModel::where('order_id',$request->order_id)->first();
           
            if($order->payment_mode == 5){
                $order->withdraw_status = 3;
                $order->save();
            }
            
          
         
        
        $order = OrderModel::where('order_id',$request->order_id)->first();
        
        if(!$order){
            return response()->json([
                'status' => "0",
                'message' => 'Invalid order_id',
                'oData' => (object)[],
                'errors' => (object) [],
            ], 401);
        }
        
        OrderProductsModel::where('order_id',$request->order_id)->update(['order_status'=>$request->order_status]);
        $orderproducts = OrderProductsModel::where('order_id',$request->order_id)->orderBy('order_status','asc')->first();
        OrderModel::where('order_id',$orderproducts->order_id)->update(['status'=>$orderproducts->order_status]);

        if($request->order_status && $request->order_status == 10){
            $items = OrderProductsModel::with('variant')->where('order_id', $request->order_id)->get();
            if($items->count()){
                foreach ($items as $key => $row) {
                    $variant = $row->variant;
                    $variant->stock_quantity = $variant->stock_quantity + $row->quantity;
                    $variant->save();
                }
            }

            $check =  OrderStatusHistory::where('order_id',$request->order_id)->where('status_id',$request->order_status)->get()->count();
        if($check == 0)
        {
            $datastatusins = new OrderStatusHistory;
            $datastatusins->order_id = $request->order_id;
            $datastatusins->status_id = $request->order_status;
            $datastatusins->created_at = gmdate('Y-m-d H:i:s');
            $datastatusins->updated_at = gmdate('Y-m-d H:i:s');
            $datastatusins->save();
        }
        }
            
        // $orderupdate = OrderModel::where('order_id',$request->order_id)->update(['status'=>$request->order_status]);

       
       $order_no = config('global.sale_order_prefix').date(date('Ymd', strtotime($order->created_at))).$order->order_id;
       
        $message = $order_no;//"Order status is changed successfully";
        exec("php ".base_path()."/artisan order:update_status ".$order->order_id." ".$request->order_status." > /dev/null 2>&1 & ");
        if($request->order_status == 4)
        {
        //\Artisan::call("order:update_status_vendor ".$order->order_id." ".$order->status);    
        exec("php ".base_path()."/artisan order:update_status_vendor ".$order->order_id." ".$request->order_status." > /dev/null 2>&1 & ");    
        }
       
         
        $check =  OrderStatusHistory::where('order_id',$request->order_id)->where('status_id',$request->order_status)->get()->count();
        if($check == 0)
        {
            $datastatusins = new OrderStatusHistory;
            $datastatusins->order_id = $request->order_id;
            $datastatusins->status_id = $request->order_status;
            $datastatusins->created_at = gmdate('Y-m-d H:i:s');
            $datastatusins->updated_at = gmdate('Y-m-d H:i:s');
            $datastatusins->save();
        }
       

        if($request->order_status == 4)//deliverd
        {
        //   $order = Ordersnew::where(['order_id' => $order->order_id])->first();
        //   $user  = User::find($user->id);
        //   $user->balance = $user->balance + OrderProductsModelNew::where('order_id',$order->order_id)->sum('admin_commission');
        //   $user->save();
        }
        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
        // }
        
    }

    public function mute_order(REQUEST $request){
    $status   = "0";
     $message  = "";
     $o_data   = [];
     $errors   = [];
         
     $validator = Validator::make($request->all(), [
         'type' => 'required',
         'access_token' => 'required'
     ]);

     if ($validator->fails()) {
         $status = "0";
         $message = "Validation error occured"; 
         $errors = $validator->messages();
     }else{
          $userid = $this->validateAccesToken($request->access_token);
          $user = User::find($userid);
         $type = $request->type;
         if($type == 'all'){
             $data = OrderModel::join('order_products','order_products.order_id','=','orders.order_id')->where('order_products.vendor_id',$userid)
             ->update(['is_muted'=>1]);
             $status = "1";
             $message = "all orders muted";
         }else{
             $data = OrderModel::join('order_products','order_products.order_id','=','orders.order_id')->where(['order_products.vendor_id'=>$userid,'orders.order_id'=>$request->order_id])
             ->update(['is_muted'=>1]);
             $status = "1";
             $message = "order muted";
         }
     }
      return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => (object) $o_data], 200);
    }
    private function validateAccesToken($access_token)
    {

        $user = User::where(['user_access_token' => $access_token])->get();

        if ($user->count() == 0) {
            http_response_code(401);
            echo json_encode([
                'status' => "0",
                'message' => login_message(),
                'oData' => [],
                'errors' => (object) [],
            ]);
            exit;

        } else {
            $user = $user->first();
            if ($user->verified == 1) {
                return $user->id;
            } else {
                http_response_code(401);
                echo json_encode([
                    'status' => "0",
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ]);
                exit;
                return response()->json([
                    'status' => "0",
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ], 401);
                exit;
            }
        }
    }
public function count_order(REQUEST $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required'
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $orders = OrderModel::select('order_products.*', 'users.name', 'orders.*','users.dial_code','users.phone', DB::raw("CONCAT(users.first_name,' ',users.last_name) as customer_name"),'orders.order_id as id')->join('order_products', 'order_products.order_id', '=', 'orders.order_id')
            ->leftjoin('user_address', 'user_address.user_id', '=', 'orders.user_id')
            ->leftjoin('users', 'users.id', 'orders.user_id');
           $orders = $orders->where('orders.status', 0);
           $orders = $orders->where('order_products.vendor_id','=',$user_id);
        
           $total = $orders->get()->count();
           $orders = $orders->orderBy('orders.order_id','desc')->distinct('orders.order_id')->get();

            $vendor_services = DB::table('vendor_services')->where('vendor_id',$user_id)->get()->toArray();
            $vendor_services = $serar = array_column($vendor_services, 'service_id');
            $vendor_services = implode(",",$vendor_services);
           $order_list = [];
           
           if(!empty($serar))
            {
           $order_list = OrderServiceModel::select('orders_services.order_id','orders_services_items.id as id','orders_services_items.order_status','orders_services_items.total as price',
            'users.name as customer_name',
            'orders_services_items.booking_date',
            'service.name as service_name',
            'orders_services.is_mute',
            'orders_services.created_at')->orderBy('orders_services.order_id','desc')
           ->join('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')
           ->join('service', 'service.id', 'orders_services_items.service_id');
           
           $order_list = $order_list->where('orders_services_items.order_status',0);  
           $order_list = $order_list->join('users', 'users.id', 'orders_services.user_id')
           ->whereIn('orders_services_items.service_id',explode(",", $vendor_services))
           ->where(function($query) use($user_id)
            {
            $query->where('orders_services_items.accepted_vendor','=','0');
            })
           ->get();
            }
                 
            if($orders->first()) {
                $o_data['pending_orders'] = $total;
                
            }
            else
            {
                $o_data['pending_orders'] = 0;
            }
            if($order_list->first()) {
                $o_data['pending_service_orders'] = $order_list->count();
                
            }
            else
            {
                $o_data['pending_service_orders'] = 0;
            }
            
            $o_data = convert_all_elements_to_string($o_data);
            
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
   }


}
