<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CustomRequestModel;
use App\Models\OrderServiceItemsModel;
use DB;
use Validator;
use App\Models\Common;
use App\Models\OrderProductsModel;
use App\Models\Rating;
use App\Classes\FaceReg;
use App\Models\ProductModel;

class RatingController extends Controller
{
   
    public function add_rating(REQUEST $request){
       
            $status  = 0;
            $message = "";
            $o_data  = [];
            $errors  = [];
            $redirectUrl = '';

            $rules['type']   = "required|integer|min:1|max:3";
            $rules['rating'] = "required|numeric|min:1|max:5";
            $rules['order_id'] = "required|integer|min:1";
            //$rules['comment'] = "required";
            $user_id = validateAccesToken($request->access_token);
            if($request->type == 1)//product
            { 
                $rules['product_id']         = "required|integer";
                $rules['product_variant_id'] = "required|integer";
                $where['product_id'] = $request->product_id;
                $where['product_varient_id'] = $request->product_variant_id;
                $ins['product_id']           = $request->product_id;
                $ins['product_varient_id']   = $request->product_variant_id;
                $purchasestatus = OrderProductsModel::join('orders','orders.order_id','=','order_products.order_id')
                ->where(['orders.user_id'=>$user_id,'orders.status'=>4,'product_id'=>$request->product_id,'product_attribute_id'=>$request->product_variant_id,'order_products.order_id'=>$request->order_id])->get()->count();
               
            }
            if($request->type == 2) //vendor
            { 
                $rules['vendor_id']         = "required|integer";
                $where['vendor_id']         = $request->vendor_id;
                $ins['vendor_id']           = $request->vendor_id;
                $purchasestatus = 1;
            }
            if($request->type == 3)//service
            { 
                $rules['service_id']         = "required|integer";
                $where['service_id']         = $request->service_id;
                $ins['service_id']           = $request->service_id;
                $purchasestatus = OrderServiceItemsModel::join('orders_services','orders_services.order_id','=','orders_services_items.order_id')
                ->where(['service_id'=>$request->service_id,'user_id'=>$user_id,'orders_services_items.order_status'=>4,'orders_services_items.order_id'=>$request->order_id])->count();
            }
            
            $validator = Validator::make($request->all(),$rules);
    
            
            if ($validator->fails()) {
                $status = 0;
                $message = "Validation error occured";
                $errors = $validator->messages();
            }else{
               
                $where['user_id'] = $user_id;
                $where['type'] = $request->type;
                $where['order_id'] = $request->order_id;
                
                $check = Common::check_already('ratings',$where);
                if($check != 1)
                {
                    $ins['type']             = $request->type;
                    $ins['user_id']          = $user_id;
                    $ins['rating']           = $request->rating;
                    $ins['title']            = $request->title;
                    $ins['comment']          = $request->comment??'';
                    $ins['order_id']         = $request->order_id;
                    $ins['created_at']       = gmdate('Y-m-d H:i:s');
                    $ins['updated_at']       = gmdate('Y-m-d H:i:s');
                    $purchasestatus = 1;
                if(empty($purchasestatus))
                {
                   $in_id = 0;
                }
                else
                {
                  $in_id = Common::insert_to_db('ratings',$ins);  

                    if($request->type == 1)//product
                    {
                        $p_avg = number_format(Rating::avg_rating(['product_id'=>$request->product_id]), 1, '.', '');
                        $p  = ProductModel::find($request->product_id);
                        if($p){
                            $p->rating_avg = $p_avg;
                            $p->save();
                        }
                    }
                }
                
                if($in_id > 0 ){
                    $status = 1;
                    $message = "Your rating saved successfully!";
                }else{
                    $status = 0;
                    $message = "Unable to rate, You are not purchased this item!";
                }
                }
                else
                {
                    $status = 0;
                    $message = "You Already rated";
                }
                
            }
            return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
}
