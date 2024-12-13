<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Auth;
use Kreait\Firebase\Database;
use App\Models\OrderModel;
use App\Models\OrderStatusHistory;
use App\Models\OrderProductsModel;
use App\Exports\ExportReports;
use Maatwebsite\Excel\Facades\Excel;
class OrderController extends Controller
{

    public function index(Request $request)
    { 
        if(Auth::user()->activity_id != 7 && Auth::user()->activity_id != 5 && Auth::user()->activity_id != 3)
        {
            return redirect('portal');
        }

        $vendor_id = Auth::user()->id;
       
        
        $page_heading = "Orders"; 
        $status = $_GET['status'] ?? '';
        $order_id = $_GET['order_id'] ?? '';
        $name = isset($_GET['name']) && $_GET['name'] ?  ($_GET['name']) : '';
        $from = !empty($_GET['from'])?date('Y-m-d',strtotime($_GET['from'])): '';
        $to = !empty($_GET['to']) ?date('Y-m-d',strtotime($_GET['to'])): '';

        $list =  OrderModel::select('order_products.*','orders.*','user_address.address','user_address.phone',DB::raw("CONCAT(users.first_name,' ',users.last_name) as customer_name"))->join('order_products','order_products.order_id','=','orders.order_id')
        ->leftjoin('user_address','user_address.user_id','=','orders.user_id')
        ->leftjoin('users','users.id','orders.user_id')->with(['customer'=>function($q) use($name){
           $q->where(DB::raw("LOWER(CONCAT(users.first_name,' ',users.last_name))"),'like','%'.strtolower($name).'%');
        }]);
        if($name)
        {
             $list =$list->whereRaw("LOWER(concat(users.first_name, ' ', users.last_name)) like '%" .strtolower($name). "%' ");
        }
        if($order_id){
            $list=$list->where(function ($query) use ($order_id) {
            $query->where('orders.order_id','like','%'.$order_id.'%' );
            $query->orWhere('orders.order_no', "like", "%" . $order_id . "%");
        });
        }
        if($from){
            $list=$list->whereDate('orders.created_at','>=',$from.' 00:00:00');
        }
        if($to){
            $list=$list->where('orders.created_at','<=',$to.' 23:59:59');
        }
        if($status == '0' || $status){
            $list=$list->where('orders.status',$status);
        }

        $list=$list->where('order_products.vendor_id',$vendor_id)->distinct('orders.order_id')->orderBy('orders.order_id','DESC')->paginate(10);
        if($list->total()){     
                foreach($list->items() as $key=>$row){
                    //$list->items()[$key]->product_name=OrderProductsModel::product_name($row->product_id,$row->product_type);
                    $list->items()[$key]->vendor_total = DB::table('order_products')->where('vendor_id',$vendor_id)->where('order_id',$row->order_id)->sum('total');
                    
                }
              }
       
        return view('portal.orders.list',compact('page_heading','list','order_id','name','from','to','status'));
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
        return view('portal.orders.commission',compact('page_heading','list','order_id','name','from','to'));
        }
    }
    
    
   public function details(Request $request,$id)
    {
        $page_heading = "Orders Details"; 
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
         
            
         
        return view('portal.orders.details',compact('page_heading','list'));
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

            
         
        return view('portal.orders.details_edit',compact('page_heading','list'));
    }

    function change_status(Request $request)
    {
        $status  = "0";
        $message = "";
        if(OrderProductsModel::where('order_id',$request->detailsid)->update(['order_status'=>$request->statusid])){
            $orderproducts = OrderProductsModel::where('order_id',$request->detailsid)->orderBy('order_status','asc')->first();
            OrderModel::where('order_id',$orderproducts->order_id)->update(['status'=>$orderproducts->order_status]);
            $status = "1";
            $message = "Successfully updated";
            
            $order = OrderModel::where('order_id',$request->detailsid)->first();
            if($order->payment_mode ==5 && $order->status ==4){
                $order->withdraw_status = 3;
                $order->save();
            }
            
            exec("php ".base_path()."/artisan order:update_status ".$orderproducts->order_id." ".$request->statusid." > /dev/null 2>&1 & ");
            if($request->order_status == 4)
            {
            exec("php ".base_path()."/artisan order:update_status_vendor ".$orderproducts->order_id." ".$request->statusid." > /dev/null 2>&1 & ");    
            }
            $check =  OrderStatusHistory::where('order_id',$request->detailsid)->where('status_id',$request->statusid)->get()->count();
            if($check == 0)
            {
                $datastatusins = new OrderStatusHistory;
                $datastatusins->order_id = $request->detailsid;
                $datastatusins->status_id = $request->statusid;
                $datastatusins->created_at = gmdate('Y-m-d H:i:s');
                $datastatusins->updated_at = gmdate('Y-m-d H:i:s');
                $datastatusins->save();
            }
        }else{
            $message = "Something went wrong";
        }
        echo json_encode(['status'=>$status,'message'=>$message]);
    }
   

}