<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Auth;
use Kreait\Firebase\Database;
use App\Models\OrderServiceModel;
use App\Models\OrderServiceItemsModel;
use App\Models\VendorDetailsModel;
use App\Exports\ExportReports;
use Maatwebsite\Excel\Facades\Excel;
class ServiceRequestController extends Controller
{

    public function index(Request $request)
    { 

        $vendor_id = Auth::user()->id;
        $audio = 0;
       
        
        $page_heading = "Service Request"; 
        $order_id = $_GET['order_id'] ?? '';
        $name = $_GET['name'] ?? '';
        $cusid = $_GET['cus_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $from = !empty($_GET['from'])?date('Y-m-d',strtotime($_GET['from'])): '';
        $to = !empty($_GET['to']) ?date('Y-m-d',strtotime($_GET['to'])): '';

        $customer =  $_GET['customer'] ?? '';
        $vendor_id	= $_GET['vendor_id']??'';

        $list =  OrderServiceModel::with('ServicesItems','activity')->select('*','orders_services.activity_id as activity_id')->leftjoin('users','users.id','=','orders_services.user_id');
       
        //->leftjoin('user_address','user_address.user_id','=','orders.user_id')
        //->leftjoin('users','users.id','orders.user_id')->with(['customer'=>function($q) use($name){
        //    $q->where('display_name','like','%'.$name.'%');
        // }]);
        if($name)
        { 
             $list =$list->whereRaw("concat(first_name, ' ', last_name) like '%" .$name. "%' ");
        }
        if($order_id){
            $list=$list->where(function ($query) use ($order_id) {
            $query->where('orders_services.order_id','like','%'.$order_id.'%' );
            $query->orWhere('orders_services.order_no', "like", "%" . $order_id . "%");
        });
        }
        if($cusid)
        {
           $list=$list->where('orders_services.user_id',$cusid); 
        }
        if($customer){
            $list = $list->where('orders_services.user_id',$customer);
        }
        if($vendor_id){
            $list = $list->whereIn('order_id',OrderServiceItemsModel::where(['accepted_vendor'=>$vendor_id])->select('order_id'));
            // $list = $list->whereHas('ServicesItems',function($q) use($vendor_id){
            //     $q->where('accepted_vendor',$vendor_id);
            // });
        }
        if(is_numeric($status))
        {
            $list=$list->where('orders_services.status',$status); 
        }
        if($from){
            $list=$list->whereDate('orders_services.created_at','>=',$from.' 00:00:00');
        }
        if($to){
            $list=$list->where('orders_services.created_at','<=',$to.' 23:59:59');
        }

        //$vendorcreated_at = 
        
        $list=$list->orderBy('orders_services.order_id','DESC');
        if (isset($_GET['from'])) {
        $list = $list->paginate(500);
        }
        else
        {
          $list=$list->paginate(10);  
        }

        
        
        foreach ($list as $key => $value) {
            $list[$key]->admin_commission = OrderServiceItemsModel::where('order_id',$value->order_id)->sum('admin_commission');
            $list[$key]->activity_type = $value->activity->name??'Service';
        }
        
        // if($list->total()){     
        //         foreach($list->items() as $key=>$row){
        //             //$list->items()[$key]->product_name=OrderProductsModel::product_name($row->product_id,$row->product_type);
        //             //$list->items()[$key]->vendor_total = //DB::table('order_products')->where('vendor_id',$vendor_id)->where('order_id',$row->order_id)->sum('total');
                    
        //         }
        //       }
       
        return view('admin.service_request.list',compact('page_heading','status','list','order_id','name','from','to','audio'));
    }
    public function commission(Request $request)
    {
        $page_heading = "Commission Report"; 
        $order_id = $_GET['order_id'] ?? '';
        $name = $_GET['name'] ?? '';
        $from = !empty($_GET['from'])?date('Y-m-d',strtotime($_GET['from'])): '';
        $to = !empty($_GET['to']) ?date('Y-m-d',strtotime($_GET['to'])): '';
        $list =  OrderModel::select('orders.*',DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as vendor_name"),'order_products.admin_commission as ad_comm','order_products.vendor_commission as vd_comm','order_products.total as subtot')
           ->leftjoin('order_products','order_products.order_id','orders.order_id')
           ->leftjoin('res_users','res_users.id','order_products.vendor_id');
        $list->orderBy('vendor.order_id','desc');
        
        if($order_id){
            $list=$list->where('orders.order_id',$order_id);
        }
        if($from){
            $list=$list->whereDate('orders.created_at','>=',$from.' 00:00:00');
        }
        if($to){
            $list=$list->where('orders.created_at','<=',$to.' 23:59:59');
        }
        $list=$list->where('order_products.order_status',config("global.order_status_delivered"));
        if($request->submit != "export")
        {
        $list=$list->paginate(10);    
        }
        else
        {
        $list=$list->paginate(1000);    
        }
        
        
        if($request->submit == "export")
        {
            //export
            

            $rows = array();
            $i = 1;
            foreach ($list as $key => $val) {
                
            if($val->payment_mode==1)
            {
            $payment = "COD";
            }
            else
            {
            $payment = "CARD";
            }
            
            $rows[$key]['i'] = $i;
            $rows[$key]['order_id'] = $val->order_id;
            $rows[$key]['invoice_id'] = ($val->invoice_id)??'-';
            $rows[$key]['vendor'] = ($val->vendor_name)??'-';
            $rows[$key]['admin_commission'] = ($val->ad_comm)??'0';
            $rows[$key]['vendor_earning'] = $val->vd_comm;
            $rows[$key]['total'] = $val->subtot;
            $rows[$key]['payment_mode'] = $payment;
            $rows[$key]['order_date'] = get_date_in_timezone($val->created_at, 'd-M-y H:i A');
            $i++;
            }
            $headings = [
            "#",
            "Order ID",
            "Invoice ID",
            "Vendor",
            "Admin Commission",
            "Vendor Earning",
            "Total",
            "Payment Mode",
            "Order Date",
             ];
            $coll = new ExportReports([$rows], $headings);
            $ex = Excel::download($coll, 'products_' . date('d_m_Y_h_i_s') . '.xlsx');
            if (ob_get_length()) ob_end_clean();
            return $ex;
            //export end
        }
        else
        {
        return view('admin.orders.commission',compact('page_heading','list','order_id','name','from','to'));
        }
    }
    
    
   public function details(Request $request,$id)
    {
        $page_heading = "Service Request Details"; 
        $list =  OrderServiceModel::with('ServicesItems')->select("orders_services.*","users.name as customer_name","user_address.*","users.email","users.dial_code","users.phone")->leftjoin('users','users.id','orders_services.user_id')->where(['order_id'=>$id])
        ->leftjoin('user_address','user_address.id','=','orders_services.address_id')
        ->first();
            
        if(!empty($list))
        {
         $list->service_details=OrderServiceItemsModel::select('orders_services_items.*','service.name','service.image','description','vendor.first_name','vendor.last_name')
         ->leftjoin('service','service.id','=','orders_services_items.service_id')
         ->leftjoin('users as vendor','vendor.id','=','orders_services_items.accepted_vendor')
         ->where('order_id',$list->order_id)->get();   
         
         $list->company_name = VendorDetailsModel::where('user_id',$list->service_details[0]->accepted_vendor)->first()->company_name??'';
        }
        
       
        OrderServiceModel::where('order_id',$id)->update(['read_admin'=>'1']);
        
      
        return view('admin.service_request.details',compact('page_heading','list'));
    }

    public function edit_order(Request $request,$id)
    {
        $page_heading = "Orders Details Edit"; 
        //$list =  OrderProductsModel::select('orders.*',DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as customer_name"))->->leftjoin('res_users','res_users.id','orders.user_id')->with('vendor')->where(['order_id'=>$id])->paginate(10);
        //if($list->total()){     
        //foreach($list->items() as $key=>$row){
       
            //$list->items()[$key]->tickets=OrderModel::tickets($row->id);
            //$list->items()[$key]->product_name=OrderProductsModel::product_name($row->product_id,$row->product_type);
        //}
        // }
            $filter['order_id']  = $id;
            
            $page = (int)$request->page??1;
            $limit= 10;
            $offset = ($page - 1) * $limit;
            $list = OrderProductsModel::get_order_details($filter)->skip($offset)->take($limit)->get();
            $list = process_order($list);

            
         
        return view('admin.orders.details_edit',compact('page_heading','list'));
    }

    function change_status(Request $request)
    { 
        $status  = "0";
        $message = "";
        $data = OrderServiceItemsModel::where('id',$request->detailsid)->get()->first();
        if($data){
            if(OrderServiceItemsModel::where('id',$request->detailsid)->update(['order_status'=>$request->statusid])){
                $status = "1";
                $message = "Successfully updated";

                $min_status_get = OrderServiceItemsModel::where(['order_id'=>$data->order_id])->min('order_status');
                OrderServiceModel::where('order_id',$data->order_id)->update(['status'=>$min_status_get]);
                
                exec("php ".base_path()."/artisan order:update_service_status_vendor --uri=" . $request->detailsid . " --uri2=" . $request->statusid . " --uri3=" . $request->detailsid. "> /dev/null 2>&1 & ");
            }else{
                $message = "Something went wrong";
            }
        }else{
            $message = "invalid request";
        }
        echo json_encode(['status'=>$status,'message'=>$message]);
    }
    

}