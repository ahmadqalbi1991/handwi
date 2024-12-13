<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderModel;
use App\Models\Categories;
use App\Models\ProductModel;
use App\Models\OrderServiceModel;
use App\Models\User;
use App\Models\Stores;
use App\Models\OrderProductsModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Exports\ExportReports;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Contracting;
use App\Models\Maintainance;
use App\Models\OrderServiceItemsModel;
use App\Models\CouponVendorServiceOrders;
use App\Models\ServiceOrderRejected;
use App\Models\OrderRequestViews;
use App\Models\ServiceOrderMuted;
use DB;
use Auth;

class ReportController extends Controller
{
    public function service_request(REQUEST $request) {
       
        $vendor_id = Auth::user()->id;
        $muted = 1;
        $audio = 0;
        $notrelated_orders = [];
        
        $page_heading = "Service Request"; 
        $order_id = $_GET['order_id'] ?? '';
        $name = $_GET['name'] ?? '';
        $order_number = $_GET['order_number'] ?? '';
        $status = $_GET['status'] ?? '';
        $from_date = !empty($_GET['from'])?date('Y-m-d',strtotime($_GET['from'])): '';
        $to_date = !empty($_GET['to']) ?date('Y-m-d',strtotime($_GET['to'])): '';

        $vendor_coupon_orders  = CouponVendorServiceOrders::pluck('order_id')->toArray();
        if(!empty($vendor_coupon_orders))
        {
        $vendor_coupon_orders  = array_unique($vendor_coupon_orders);   
        $notrelated_orders = []; 
        foreach ($vendor_coupon_orders as $key => $value) {
           //$check_vendor = CouponVendorServiceOrders::where(['vendor_id'=>$vendor_id,'order_id'=>$value])->get()->count();
           $check_vendor = CouponVendorServiceOrders::where(['vendor_id'=>$vendor_id,'order_id'=>$value])->get()->count();
           if($check_vendor == 0)
           {
             $notrelated_orders[] = $value;
           }

        }
        
        }
        
       
        

        $vendor_services = DB::table('vendor_services')->where('vendor_id',$vendor_id)->get()->toArray();
        $vendor_services = $serar = array_column($vendor_services, 'service_id');
        $vendor_services = implode(",",$vendor_services);
        if(empty($vendor_services))
        {
            $vendor_services = 0;
        }
         $rejected = ServiceOrderRejected::select('service_order_item_id')->where('vendor_id',$vendor_id)->pluck('service_order_item_id')->toArray();

        $list =  OrderServiceModel::select('*','orders_services.created_at as created_at','orders_services_items.id as item_id',
        'orders_services_items.hourly_rate','orders_services_items.qty',
        'orders_services_items.total as total','orders_services.vat',
        'orders_services.discount','orders_services.admin_commission','orders_services.vendor_commission')->leftjoin('users','users.id','=','orders_services.user_id')
        ->join('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')
        ->whereNotIn('orders_services_items.id',$rejected)
    //     ->leftjoin('orders_services_rejected', function($join) use ($vendor_id)
    //     {
    //   // $join->on('orders_services_rejected.service_order_id', '!=', 'orders_services_items.order_id');
    //     $join->on('orders_services_rejected.vendor_id', '!=', DB::raw("'".$vendor_id."'"));
    //     })
        
        ->whereIn('orders_services_items.service_id',explode(",", $vendor_services))
        ->whereNotIn('orders_services.order_id',$notrelated_orders)
        ->where(function($query) use($vendor_id)
            {
            $query->where('orders_services_items.accepted_vendor','=','0')
           ->orWhere('orders_services_items.accepted_vendor',$vendor_id);
            });
        
       
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
        if($from_date){
            $list=$list->whereDate('orders_services.created_at','>=',$from_date.' 00:00:00');
        }
        if($to_date){
            $list=$list->where('orders_services.created_at','<=',$to_date.' 23:59:59');
        }
        if($order_number){
            
            
            $newString = substr($order_number, 14);
            if($newString){
                $list = $list->where('orders_services_items.order_id',$newString);
            }
        }
 
        $created = Auth::user()->created_at;
        
        if(is_numeric($status))
        {
            $list=$list->where('orders_services_items.order_status',$status); 
        }
        
        $list=$list->orderBy('orders_services_items.order_id','DESC')
        ->where('orders_services_items.created_at','>=',$created)
        ->distinct('orders_services_items.order_id');

        if($request->excel != 'Export'){

           $list=$list->paginate(10);
           foreach ($list as $key => $value) {
            $list[$key]->rejected = ServiceOrderRejected::where(['vendor_id'=>$vendor_id,'service_order_id'=>$value->order_id])->get()->count();
            $list[$key]->read_vendor = OrderRequestViews::where(['vendor'=>$vendor_id,'service_order'=>$value->order_id])->get()->count();
            //$list[$key]->admin_commission = OrderServiceItemsModel::where('id',$value->item_id)->sum('admin_commission');
            //$list[$key]->vendor_commission = OrderServiceItemsModel::where('id',$value->item_id)->sum('vendor_commission');
            if($list[$key]->rejected > 0)
            {
            $list[$key]->read_vendor = 1;
            }
            $list[$key]->muted = ServiceOrderMuted::where(['vendor_id'=>$vendor_id,'service_order_id'=>$value->order_id])->get()->count();
            if($list[$key]->muted == 0)
            {
                $muted = 0;
            }
            if($value->created_at >= date('Y-m-d 00:00:00'))
            {
                if($muted == 0)
                {
                    $audio = 1;
                }
            }
            $list[$key]->booking_date = $value->booking_date;
          }
           return view('portal.report.service_request_list', compact('page_heading','list','from_date', 'to_date'));

        }
        
        else {
            
            $list = $list->get();
            $rows = array();
            $i = 1;
            foreach ($list as $key => $val) {
                
                $rows[$key]['i'] = $i;
                
                $rows[$key]['order_no'] = $val->order_no;
                $customer_name = $val->customer_name.' </br>'.$val->customer_mobile;
                $rows[$key]['customer']             = $val->users->first_name .' '.$val->users->last_name ?? '';
                $rows[$key]['mobile']               = $val->users->dial_code .' '.$val->users->phone ?? '';
                // $rows[$key]['vendor']               = $val->vendordata->company_name ?? '';
                $rows[$key]['admin_commission']     = ($val->admin_commission)??'';
                $rows[$key]['vendor_commission']       = ($val->vendor_commission)??'';
                 $withdraw_status = Config('global.withdraw_status');

                $rows[$key]['withdraw_status']       = $withdraw_status[(int)$val->withdraw_status] ?? '';
                $rows[$key]['subtotal']                = number_format($val->grand_total - $val->vat + $val->discount - $val->service_charge, 2, '.', '');
                $rows[$key]['discount']             = $val->discount??'';
                $rows[$key]['vat']                  = ($val->vat)??'';
                $rows[$key]['service_charge']                  = ($val->service_charge)??'';
                $rows[$key]['total']                = $val->grand_total;
                $rows[$key]['payment_mode']         = payment_mode($val->payment_mode)??'';

                // $rows[$key]['created_date']      = get_date_in_timezone($val->created_at, 'd-M-y h:i A')??'';
                $rows[$key]['booking_date']         = $val->booking_date;
                if(!empty($val->booking_date))
                {
                  //  $rows[$key]['booking_date']         = get_date_in_timezone($val->booking_date, 'd-M-y h:i A');
                }
                
                $items                              = $val->services;
                $rows[$key]['order_items_count']    = $items->count()??'0';
                $k = 0;
                $p_items = '';
                $admin_commission = 0;
                $vendor_commission = 0;
                foreach ($items as $i_key => $p_val) {
                    $p_items .= 'Service Name: '.$p_val->service->name;
                    // if(isset($p_val->attribute_name) && $p_val->attribute_name){
                    //     $p_items .= ' Attr: '.$p_val->attribute_name .': '.$p_val->attribute_values;
                    // }
                    if($p_val->vendor && $p_val->vendor->vendordata->first()){
                        $p_items .= ', Vendor: '.$p_val->vendor->vendordata->first()->company_name ?? '';
                    }
                    
                    $p_items .= ', Admin Share: '.$p_val->admin_commission;
                    $p_items .= ', Vendor Share: '.$p_val->vendor_commission;
                    $p_items .= ', Service rate: '.$p_val->qty.' x '.number_format($p_val->price, 2, '.', '') . ' - '.$p_val->text;
                    $p_items .= ', Scheduled date: '.date('d-M-y h:i A', strtotime($p_val->booking_date));
                    
                    
                    $p_items .= ', VAT: JOD '.$p_val->vat;
                    $p_items .= ', Total: JOD '.$p_val->total;
                    $rows[$key]['p_items_'.$k] = $p_items;
                    $p_items = '';
                    $admin_commission = $admin_commission + $p_val->admin_commission;
                    $vendor_commission = $vendor_commission + $p_val->vendor_commission;
                    $k = $k+1;
                }
                //$rows[$key]['admin_commission']     = ($admin_commission)??'';
                //$rows[$key]['vendor_commission']       = ($vendor_commission)??'';
                ///////////////////
                $i++;
            }
            $headings = [
                "#",
                // "Customer Name",
                // "Customer Mobile",
                // "Admin Share",
                // "Vendor Share",
                // "Subtotal",
                // "Discount",
                // "VAT",
                // "Grand Total",
                // "Payment Mode",
                // "Booking Date",


                "Order No",
                "Customer Nmae",
                "Customer Mobile",
                "Admin Share",
                "Vendor Share",
                "Share Payment Status",
                "Sub Total",
                "Discount",
                "VAT",
                "Service Charge",
                "Total",
                "Payment Mode",
                // "Created Date",
                "Booking Date",
                "Items Count",
                "Items",


            ];
            // dd([$rows], $headings);
            $coll = new ExportReports([$rows], $headings);
            $ex = Excel::download($coll, 'Service_Request_Report_' . date('d_m_Y_h_i_s') . '.xlsx');
            if (ob_get_length()) ob_end_clean();
            return $ex;
        }
        
        
    }
    public function service_request_old(REQUEST $request) {
        
        // $vendor_id = Auth::user()->id;

        
        $user = \Auth::user();
        $vendor_id = '';
        $is_vendor = 0;
        if($user->role == 3){
            $is_vendor = 1;
            $vendor_id = $user->id;
            $activity = $user->activity_id;
        }

        $page_heading = "Service Request Report"; 

        $from_date = !empty($_GET['from_date'])?date('Y-m-d',strtotime($_GET['from_date'])): '';
        $to_date = !empty($_GET['to_date']) ?date('Y-m-d',strtotime($_GET['to_date'])): '';
        
        // $list = OrderServiceModel::select(["order_id","invoice_id","user_id","address_id","vat","discount","grand_total","payment_mode","status","booking_date", "orders_services.created_at",
        //                                     "accepted_vendor","accepted_date","is_mute","refund_method","refund_requested","refund_accepted", "refund_requested_date", "refund_accepted_date",
        //                                     "orders_services.updated_at","order_no","id","name","email"])
        //         ->leftjoin('users','users.id','=','orders_services.user_id');
        $list = OrderServiceModel::with('users','services.service','services.vendor.vendordata');

   
        if($from_date){
            $list=$list->where('created_at','>=',$from_date.' 00:00:00');
        }
        
        if($to_date){
            $list=$list->where('created_at','<=',$to_date.' 23:59:59');
        }
        // if($activity)
        // {
        //      $list =$list->where("activity_id",$activity);
        // }
        if($vendor_id)
        {
             // $list =$list->where("vendor_id",$vendor_id)
            $list =  $list->whereHas('services',function($query) use($vendor_id){
                $query->where('accepted_vendor',$vendor_id);
            });
        }

        
        $list=$list->orderBy('order_id','DESC')->paginate(10);

        if($request->excel != 'Export'){


            if($user->role == 3){
                return view('portal.report.services_report', compact('page_heading','list','from_date', 'to_date','is_vendor'));
            }else{
                return view('admin.reports.service_request_list', compact('page_heading','list','from_date', 'to_date'));
            }

        }
        else {
            // $list = $list->get();
            $rows = array();
            $i = 1;
            foreach ($list as $key => $val) {
                
                $rows[$key]['i'] = $i;
                // $rows[$key]['customer'] =  $val->users->first_name .' '.$val->users->last_name ?? '';
                // $rows[$key]['discount'] =  number_format($val->discount, 2, '.', '');
                // $rows[$key]['grand_total'] = number_format($val->grand_total, 2, '.', '');
                // $rows[$key]['payment_mode'] = payment_mode($val->payment_mode);
                // // $rows[$key]['created_date'] = date('d-m-Y h:i A',strtotime($val->created_at));
                // $rows[$key]['booking_date'] = date('d-m-Y h:i A',strtotime($val->created_at));


                ///////////////////
                $rows[$key]['order_no'] = $val->order_no;
                $customer_name = $val->customer_name.' </br>'.$val->customer_mobile;
                $rows[$key]['customer']             = $val->users->first_name .' '.$val->users->last_name ?? '';
                $rows[$key]['mobile']               = $val->users->dial_code .' '.$val->users->phone ?? '';
                // $rows[$key]['vendor']               = $val->vendordata->company_name ?? '';
                $rows[$key]['admin_commission']     = ($val->admin_commission)??'';
                $rows[$key]['vendor_commission']       = ($val->vendor_commission)??'';
                 $withdraw_status = Config('global.withdraw_status');

                $rows[$key]['withdraw_status']       = $withdraw_status[(int)$val->withdraw_status] ?? '';
                $rows[$key]['subtotal']                = $val->total??'';
                $rows[$key]['discount']             = $val->discount??'';
                $rows[$key]['vat']                  = ($val->vat)??'';
                $rows[$key]['total']                = $val->grand_total??'';
                $rows[$key]['payment_mode']         = payment_mode($val->payment_mode)??'';

                // $rows[$key]['created_date']      = get_date_in_timezone($val->created_at, 'd-M-y h:i A')??'';
                $rows[$key]['booking_date']         = get_date_in_timezone($val->booking_date, 'd-M-y h:i A');
                $items                              = $val->services;
                $rows[$key]['order_items_count']    = $items->count()??'0';
                $k = 0;
                $p_items = '';
                $admin_commission = 0;
                $vendor_commission = 0;
                foreach ($items as $i_key => $p_val) {
                    $p_items .= 'Service Name: '.$p_val->service->name;
                    // if(isset($p_val->attribute_name) && $p_val->attribute_name){
                    //     $p_items .= ' Attr: '.$p_val->attribute_name .': '.$p_val->attribute_values;
                    // }
                    if($p_val->vendor && $p_val->vendor->vendordata->first()){
                        $p_items .= ', Vendor: '.$p_val->vendor->vendordata->first()->company_name ?? '';
                    }
                    
                    $p_items .= ', Admin Share: '.$p_val->admin_commission;
                    $p_items .= ', Vendor Share: '.$p_val->vendor_commission;
                    $p_items .= ', Service rate: '.$p_val->qty.' x '.number_format($p_val->price, 2, '.', '') . ' - '.$p_val->text;
                    $p_items .= ', Scheduled date: '.date('d-M-y h:i A', strtotime($p_val->booking_date));
                    
                    
                    $p_items .= ', VAT: JOD '.$p_val->vat;
                    $p_items .= ', Total: JOD '.$p_val->total;
                    $rows[$key]['p_items_'.$k] = $p_items;
                    $p_items = '';
                    $admin_commission = $admin_commission + $p_val->admin_commission;
                    $vendor_commission = $vendor_commission + $p_val->vendor_commission;
                    $k = $k+1;
                }
                $rows[$key]['admin_commission']     = ($admin_commission)??'';
                $rows[$key]['vendor_commission']       = ($vendor_commission)??'';
                ///////////////////
                $i++;
            }
            $headings = [
                "#",
                // "Customer Name",
                // "Customer Mobile",
                // "Admin Share",
                // "Vendor Share",
                // "Subtotal",
                // "Discount",
                // "VAT",
                // "Grand Total",
                // "Payment Mode",
                // "Booking Date",


                "Order No",
                "Customer Nmae",
                "Customer Mobile",
                "Admin Share",
                "Vendor Share",
                "Share Payment Status",
                "Sub Total",
                "Discount",
                "VAT",
                "Total",
                "Payment Mode",
                // "Created Date",
                "Booking Date",
                "Items Count",
                "Items",


            ];
            // dd([$rows], $headings);
            $coll = new ExportReports([$rows], $headings);
            $ex = Excel::download($coll, 'Service_Request_Report_' . date('d_m_Y_h_i_s') . '.xlsx');
            if (ob_get_length()) ob_end_clean();
            return $ex;
        }
        
        
    }
    public function orders(Request $request)
    {
        $order_id = $_GET['order_id'] ?? '';
        $name = $_GET['name'] ?? '';
        $activity = $_GET['activity'] ?? '';
        $order_number = $_GET['order_number'] ?? '';
        $user = \Auth::user();
        $vendor_id = '';
        $is_vendor = 0;
        if($user->role == 3){
            $is_vendor = 1;
            $vendor_id = $user->id;
            $activity = $user->activity_id;
        }

        if(!$activity){
            return redirect()->to('admin/dashboard');
        }
        $page_heading = "Orders"; 
        if($activity==7){
            $page_heading = "Grocery Order Report"; 
        }
        if($activity==5){
            $page_heading = "Food Order Report"; 
        }
        if($activity==3){
            $page_heading = "Shop Online Order Report"; 
        }
        if($activity==4){
            $page_heading = "Medical & Beauty Report"; 
        }

        $from = !empty($_GET['from_date'])?date('Y-m-d',strtotime($_GET['from_date'])): '';
        $to = !empty($_GET['to_date']) ?date('Y-m-d',strtotime($_GET['to_date'])): '';

        $list =  OrderModel::select('orders.*',
            DB::raw("CONCAT(users.first_name,' ',users.last_name) as customer_name"),DB::raw("CONCAT(users.dial_code,'',users.phone) as customer_mobile"),
        )
        ->leftjoin('users','users.id','orders.user_id')
        ->with(['order_product','vendordata','customer'=>function($q) use($name){
           $q->where('display_name','like','%'.$name.'%');
        }])
        ->with(['order_product'=>function($q) use($name){
           $q
           ->select('order_products.*','order_products.quantity as order_qty','order_products.price as order_price','order_products.total as order_total','order_products.discount as order_discount','product.product_name','product_selected_attribute_list.image',DB::raw("CONCAT(users.name) as name"))
           ->join("product","product.id","=","order_products.product_id") 
            ->leftjoin("product_category","product_category.product_id","=","product.id")
            ->leftjoin('users','users.id','=','product.product_vender_id')
            ->leftjoin('product_selected_attribute_list','product_selected_attribute_list.product_attribute_id','order_products.product_attribute_id');
        }]);
        // ->whereNotNull('withdraw_status');
        if($name)
        {
             $list =$list->whereRaw("concat(first_name, ' ', last_name) like '%" .$name. "%' ");
        }
        if($activity)
        {
             $list =$list->where("orders.activity_id",$activity);
        }
        if($vendor_id)
        {
             $list =$list->where("orders.vendor_id",$vendor_id);
        }
        if($order_number){
            $list = $list->where('order_no',$order_number);
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
        

        if($request->excel != 'Export'){
            $list=$list->orderBy('orders.order_id','DESC')->paginate(10);
            foreach ($list as $key => $value) {
                $list[$key]->admin_commission = OrderProductsModel::where('order_id',$value->order_id)->sum('admin_commission');
                $list[$key]->vendor_commission = OrderProductsModel::where('order_id',$value->order_id)->sum('vendor_commission');
            }
            if($user->role == 3){
                return view('portal.report.order_report',compact('page_heading','list','order_id','name','from','to','is_vendor'));
            }else{
                return view('admin.reports.orders',compact('page_heading','list','order_id','name','from','to','is_vendor'));
            }
        }else{
            $list = $list->orderBy('orders.order_id','DESC')->get();

            foreach ($list as $key => $value) {
                $list[$key]->admin_commission = OrderProductsModel::where('order_id',$value->order_id)->sum('admin_commission');
                $list[$key]->vendor_commission = OrderProductsModel::where('order_id',$value->order_id)->sum('vendor_commission');
            }
            $rows = array();
            $i = 1;
            foreach ($list as $key => $val) {
                $rows[$key]['i'] = $i;
                $rows[$key]['order_no'] = $val->order_no;
                // $rows[$key]['order_id'] = ($val->order_id)??'-';
                $customer_name = $val->customer_name.' </br>'.$val->customer_mobile;
                $rows[$key]['customer'] = $val->customer_name;
                $rows[$key]['mobile'] = $val->customer_mobile;
                $rows[$key]['vendor'] = $val->vendordata->company_name ?? '';
                $rows[$key]['admin_commission']     = ($val->admin_commission_per)??'';
                $rows[$key]['vendor_earning']       = ($val->vendor_commission_per)??'';
                 $withdraw_status = Config('global.withdraw_status');

                $rows[$key]['withdraw_status']       = $withdraw_status[(int)$val->withdraw_status] ?? '';
                $rows[$key]['subtotal']                = $val->total??'';
                $rows[$key]['discount']             = $val->discount??'';
                $rows[$key]['vat']                  = ($val->vat)??'';
                $rows[$key]['total']                = $val->grand_total??'';
                $rows[$key]['payment_mode']         = payment_mode($val->payment_mode)??'';
                $rows[$key]['delivery_mode']         = order_type($val->order_type)??'';
                $rows[$key]['order_status']         = order_status($val->status)??'';
                // $rows[$key]['created_date']      = get_date_in_timezone($val->created_at, 'd-M-y h:i A')??'';
                $rows[$key]['booking_date']         = get_date_in_timezone($val->booking_date, 'd-M-y h:i A');
                $order_items                        = process_product_data($val->order_product);
                $rows[$key]['order_items_count']    = $order_items->count()??'0';
                $k = 0;
                $p_items = '';
                foreach ($order_items as $i_key => $p_val) {
                    $p_items .= 'Product: '.$p_val->product_name;
                    if(isset($p_val->attribute_name) && $p_val->attribute_name){
                        $p_items .= ' Attr: '.$p_val->attribute_name .': '.$p_val->attribute_values;
                    }
                    $p_items .= ', QTY: '.$p_val->order_qty;
                    $p_items .= ', Total: JOD '.$p_val->order_total;
                    $rows[$key]['p_items_'.$k] = $p_items;
                    $p_items = '';
                    $k = $k+1;
                }
                $i++;
            }
            $headings = [
                "#",
                "Order No",
                "Customer Nmae",
                "Customer Mobile",
                "Vendor",
                "Admin Share",
                "Vendor Share",
                "Share Payment Status",
                "Sub Total",
                "Discount",
                "VAT",
                "Total",
                "Payment Mode",
                "Delivery Mode",
                "Order Status",
                // "Created Date",
                "Booking Date",
                "Order Items Count",
                "Order Items",
            ];
            $coll = new ExportReports([$rows], $headings);
            // dd([$rows], $headings);
            $ex = Excel::download($coll, str_replace(' ', '_', $page_heading).'_' . date('d_m_Y_h_i_s') . '.xlsx');
            if (ob_get_length()) ob_end_clean();
            return $ex;
          }
           
        
    }
    
    public function contract_maintenance_report(REQUEST $request){
        
        $page_heading = "Contract Mainenance Report";
        $from_date    = $request->from_date;
        $to_date      = $request->to_date;

      
        $page_heading = "Contract Maintenance Report";
        $contract_maintainance_job  = [];

        $contracts = Contracting::select('id', 'description','building_type','contract_type','user_id','file','created_at')->orderBy('created_at', 'desc')
        ->with('building_list','user');
        
        if($from_date != ''){
            $contracts = $contracts->where('created_at','>=',gmdate('Y-m-d H:i:s',strtotime($from_date)));
        }
        if($to_date != ''){
            $contracts = $contracts->where('created_at','<=',gmdate('Y-m-d H:i:s',strtotime($to_date)));
        }
        
        $contracts = $contracts->where(['deleted'=> 0 ])->get();
      
        foreach($contracts as $contract)
        { 
            if($contract->contract_type === 1){
    
                $contract->contract_text = 'Fresh';
            }else{
                $contract->contract_text = 'Extension';
            }
            $contract->name = 'contract';
            array_push($contract_maintainance_job,$contract);
        }
    
        $maintainances = Maintainance::select('id','description','building_type','user_id','file','created_at')->orderBy('created_at', 'desc')  
        ->with('building_list');
        
        if($from_date != ''){
            $maintainances = $maintainances->where('created_at','>=',gmdate('Y-m-d H:i:s',strtotime($from_date)));
        }
        if($to_date != ''){
            $maintainances = $maintainances->where('created_at','<=',gmdate('Y-m-d H:i:s',strtotime($to_date)));
        }
        
        $maintainances = $maintainances->where(['deleted'=> 0 ])->get();
        
        foreach($maintainances as $maintainance)
        { 
            $maintainance->name = 'maintenance';
            array_push($contract_maintainance_job,$maintainance);
        }
    
        if($contract_maintainance_job){
            foreach ($contract_maintainance_job as $key => $row)
            {
                $count[$key] = $row['created_at'];
            }
            array_multisort($count, SORT_DESC, $contract_maintainance_job);
        }
        
        
        
        if($request->excel != 'Export'){
            return view('admin.reports.contract_maintenance_service_list', compact('page_heading','contract_maintainance_job','from_date', 'to_date'));
        }
        else{
            // $list = $list->get();
            $rows = array();
            $i = 1;
            foreach ($contract_maintainance_job as $key => $val) {
                $rows[$key]['i'] = $i;
                $rows[$key]['description'] = $val->description;
                $rows[$key]['building_type'] = ($val->building_list->name)??'-';
                $rows[$key]['contract_type'] =$val->contract_text;
                $rows[$key]['client_name'] = $val->user->name??'-';
                $rows[$key]['created_date'] = date('d-m-Y h:i A',strtotime($val->created_at));
                $i++;
            }
            $headings = [
                "#",
                "Description",
                "Building Type",
                "Contract type",
                "Client Name",
                "Created Date",
            ];
            $coll = new ExportReports([$rows], $headings);
            $ex = Excel::download($coll, 'contract_maintenance_' . date('d_m_Y_h_i_s') . '.xlsx');
            if (ob_get_length()) ob_end_clean();
            return $ex;
        }

    }
        //
    public function users(REQUEST $request){
      $page_heading = "Customers Report";
      $from_date    = $request->from_date;
      $to_date      = $request->to_date;
      $list = User::with('vendordata','vendordata.industry_type','country','state','city')->where(['role'=>2,'users.deleted'=>'0','users.phone_verified'=>'1']);
      if($from_date != ''){
        $list = $list->where('created_at','>=',gmdate('Y-m-d H:i:s',strtotime($from_date)));
      }
      if($to_date != ''){
        $list = $list->where('created_at','<=',gmdate('Y-m-d H:i:s',strtotime($to_date)));
      }
      $list = $list->orderBy('id','desc');

      if($request->excel != 'Export'){
        $list = $list->paginate(10);
        return view('admin.reports.customer_list', compact('page_heading', 'list','from_date','to_date'));
      }else{
        $list = $list->get();
        $rows = array();
        $i = 1;
        foreach ($list as $key => $val) {
            $rows[$key]['i'] = $i;
            $rows[$key]['name'] = ($val->name != '')?$val->name:$val->first_name.' '.$val->last_name;
            $rows[$key]['email'] = ($val->email)??'-';
            $rows[$key]['phone'] = ($val->dial_code!='')?$val->dial_code.' '.$val->phone:'-';
            $rows[$key]['created_date'] = date('d-m-Y h:i A',strtotime($val->created_at));
            $i++;
        }
        $headings = [
            "#",
            "Name",
            "Email",
            "Mobile",
            "Created Date",
        ];
        $coll = new ExportReports([$rows], $headings);
        $ex = Excel::download($coll, 'customers_' . date('d_m_Y_h_i_s') . '.xlsx');
        if (ob_get_length()) ob_end_clean();
        return $ex;
      }

    }
    

    public function vendors(REQUEST $request){
      $page_heading = "Vendors";
      $from_date    = $request->from_date;
      $to_date      = $request->to_date;
      $list = User::with('vendordata','vendordata.industry_type','country','state','city','bank_details','bank_details.country','bank_details.bank')->where(['role'=>3]);
      if($from_date != ''){
        $list = $list->where('created_at','>=',gmdate('Y-m-d H:i:s',strtotime($from_date)));
      }
      if($to_date != ''){
        $list = $list->where('created_at','<=',gmdate('Y-m-d H:i:s',strtotime($to_date)));
      }

      if($request->excel != 'Export'){
        $list = $list->paginate(10);
        //printr($list->toArray());
        return view('admin.reports.vendor_list', compact('page_heading', 'list','from_date','to_date'));
      }else{
        $list = $list->get();
        $rows = array();
        $i = 1;
        foreach ($list as $key => $val) {
            $rows[$key]['i'] = $i;
            $rows[$key]['name'] = ($val->name != '')?$val->name:$val->first_name.' '.$val->last_name;
            $rows[$key]['company_name'] = ($val->vendordata->company_name)??'-';
            $rows[$key]['email'] = ($val->email)??'-';
            $rows[$key]['phone'] = ($val->dial_code!='')?$val->dial_code.' '.$val->phone:'-';
            $rows[$key]['address line 1'] = ($val->vendordata->address1)??'';
            $rows[$key]['address line 2'] = ($val->vendordata->address1)??'';
            $rows[$key]['street'] = ($val->vendordata->street)??'';
            $rows[$key]['country'] = $val->country->name??'';
            $rows[$key]['state'] = $val->state->name??'';
            $rows[$key]['city'] = $val->city->name??'';
            $rows[$key]['zip'] = $val->vendordata->zip??'';
            $rows[$key]['created_date'] = date('d-m-Y h:i A',strtotime($val->created_at));
            $rows[$key]['company_brand'] = ($val->vendordata->company_brand)??'';
            $rows[$key]['business_registration_date'] = date('d-m-Y',strtotime($val->vendordata->reg_date??''));
            $rows[$key]['trade_license'] = ($val->vendordata->trade_license)??'';
            $rows[$key]['trade_licence_expiry'] = date('d-m-Y',strtotime($val->vendordata->trade_license_expiry??''));
            $rows[$key]['vat'] = ($val->vendordata->vat_reg_number)??'';
            $rows[$key]['vat_expiry'] = date('d-m-Y',strtotime($val->vendordata->vat_reg_expiry??''));
            $rows[$key]['branches'] = $val->vendordata->branches??'';
            $rows[$key]['bank_country'] = $val->bank_details->country->name??'UAE';
            $rows[$key]['bank_name'] = $val->bank_details->bank->name??'';
            $rows[$key]['company_account'] = $val->bank_details->company_account??'';
            $rows[$key]['account_no'] = $val->bank_details->account_no??'';
            $rows[$key]['branch_code'] = $val->bank_details->branch_code??'';
            $rows[$key]['branch_name'] = $val->bank_details->branch_name??'';
            $i++;
        }
        $headings = [
            "#",
            "Name",
            "Company Name",
            "Email",
            "Mobile",
            "Address line 1",
            "Address Line 2",
            "Street",
            "County",
            "State",
            "City",
            "Zip",
            "Created Date",
            "Company Brand Name",
            "Business Registration Date",
            "Trade Licence Number",
            "Trade License Expiry Date",
            "Vat Registration Number",
            "Vat Registration Expiry",
            "No of branches",
            "Bank Country",
            "Bank Name",
            "Company Account",
            "Account Number",
            "Branch Code",
            "Branch Name"
        ];
        $coll = new ExportReports([$rows], $headings);
        $ex = Excel::download($coll, 'vendors_' . date('d_m_Y_h_i_s') . '.xlsx');
        if (ob_get_length()) ob_end_clean();
        return $ex;
      }
    }
    public function stores()
    {
        //
        $page_heading = "Stores";
        $stores = Stores::where(['deleted' => 0])->orderBy('updated_at', 'DESC')->get();
        return view('admin.reports.store', compact('page_heading', 'stores'));
    }
    

    public function outofstock()
    {
        $page_heading = "Products";
        $filter = ['product.deleted'=>0];
        $params = [];
        $category_ids = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $from = isset($_GET['from']) ? $_GET['from'] : '';
        $to = isset($_GET['to']) ? $_GET['to'] : '';
        $store = isset($_GET['store']) ? $_GET['store'] : '';
        $vendor = isset($_GET['vendor']) ? $_GET['vendor'] : '';
        $params['from'] = $from;
        $params['to'] = $to;
        $params['store'] = $store;
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $params['category'] = $category;
        if($category){
            $category_ids[0] = $category;
        }
        
        $search_key = $params['search_key'];

        $sortby = "product.id";
        $sort_order = "desc";
        if(isset($_GET['sort_type']) && $_GET['sort_type'] !="") {
            if($_GET['sort_type'] ==1) {
                $sortby = "product.product_name";
                $sort_order = "asc";
            } else if($_GET['sort_type'] ==2) {
                $sortby = "product.product_name";
                $sort_order = "desc";
            } else if($_GET['sort_type'] ==3) {
                $sortby = "product.id";
                $sort_order = "asc";
            } else if($_GET['sort_type'] ==4) {
                $sortby = "product.id";
                $sort_order = "desc";
            } else if($_GET['sort_type'] ==5) {
                $sortby = "product.updated_at";
                $sort_order = "asc";
            } else if($_GET['sort_type'] ==6) {
                $sortby = "product.updated_at";
                $sort_order = "desc";
            } 
        } 
        $list = ProductModel::get_products_list_out_of_stock($filter, $params,$sortby,$sort_order)->paginate(10);

        $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->get()->toArray();
        $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->get()->toArray();

        $parent_categories = array_column($parent_categories, 'name', 'id');
        asort($parent_categories);
        $category_list = $parent_categories;

        $sub_categories = [];
        foreach ($parent_categories_list as $row) {
            $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
        }
        $sub_category_list = $sub_categories;

      
        return view("admin.reports.outofstock_products", compact("page_heading", "list","search_key",'category_list','sub_category_list','from','to','category_ids','category'));

    }
    public function commission(Request $request)
    {
        $page_heading = "Commission report Orders"; 
        $order_id = $_GET['order_id'] ?? '';
        $name = $_GET['name'] ?? '';
        $from = !empty($_GET['from_date'])?date('Y-m-d',strtotime($_GET['from_date'])): '';
        $to = !empty($_GET['to_date']) ?date('Y-m-d',strtotime($_GET['to_date'])): '';

        $list =  OrderModel::select('orders.*')
        // ->join('order_products','order_products.order_id','=','orders.order_id')
        // ->leftjoin('users','users.id','order_products.vendor_id')
        ->with(['vendordata','customer'=>function($q) use($name){
           $q->where('display_name','like','%'.$name.'%');
        }])->whereNotNull('withdraw_status');
        if($name)
        {
             $list =$list->whereRaw("concat(first_name, ' ', last_name) like '%" .$name. "%' ");
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
        
        if($request->excel != 'Export'){
        $list=$list->orderBy('orders.order_id','DESC')->paginate(10);

       
        return view('admin.reports.commission',compact('page_heading','list','order_id','name','from','to'));
      }else{
        $list = $list->orderBy('orders.order_id','DESC')->get();
 
        
        $rows = array();
        $i = 1;
        foreach ($list as $key => $val) {
            $rows[$key]['i'] = $i;
            // $rows[$key]['order_id'] = $val->order_id;
            $rows[$key]['order_no'] = config('global.sale_order_prefix').date(date('Ymd', strtotime($val->created_at))).$val->order_id;
            $rows[$key]['vendor'] = $val->vendordata ? $val->vendordata->company_name : '';
            $rows[$key]['admin_commission'] = ($val->admin_commission)??'';
            $rows[$key]['vendor_earning'] = ($val->vendor_commission)??'';
            $rows[$key]['total'] = $val->grand_total??'';
            $rows[$key]['payment_mode'] = payment_mode($val->payment_mode)??'';
            $rows[$key]['created_date'] = get_date_in_timezone($val->created_at, 'd-M-y h:i A')??'';
            $i++;
        }
        $headings = [
            "#",
            // "Order ID",
            "Order No",
            "Vendor",
            "Admin Share",
            "Vendor Share",
            "Total",
            "Payment Mode",
            "Order Date",
        ];
        $coll = new ExportReports([$rows], $headings);
        $ex = Excel::download($coll, 'commission_report_orders' . date('d_m_Y_h_i_s') . '.xlsx');
        if (ob_get_length()) ob_end_clean();
        return $ex;
      }
       
        
    }
    public function commission_services(Request $request)
    {
        $page_heading = "Commission report Services"; 
        $order_id = $_GET['order_id'] ?? '';
        $name = $_GET['name'] ?? '';
        $from = !empty($_GET['from_date'])?date('Y-m-d',strtotime($_GET['from_date'])): '';
        $to = !empty($_GET['to_date']) ?date('Y-m-d',strtotime($_GET['to_date'])): '';

        $list =  OrderServiceModel::select('users.*','orders_services_items.*','orders_services.created_at','orders_services.payment_mode','orders_services.order_no','orders_services_items.id as item_id','customer.name as customer','vendor_details.company_name')
        ->join('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')
        ->leftjoin('users','users.id','=','orders_services_items.accepted_vendor')
        ->leftjoin('vendor_details','vendor_details.user_id','=','orders_services_items.accepted_vendor')
        ->leftjoin('users as customer','customer.id','=','orders_services.user_id')
        ->where('order_status',4);
       
        
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
        if($from){
            $list=$list->whereDate('orders_services.created_at','>=',$from.' 00:00:00');
        }
        if($to){
            $list=$list->where('orders_services.created_at','<=',$to.' 23:59:59');
        }
        
        

        if($request->excel != 'Export'){
        $list=$list->orderBy('orders_services.order_id','DESC')->paginate(10);

       
        return view('admin.reports.commission_service',compact('page_heading','list','order_id','name','from','to'));
      }else{
        $list=$list->orderBy('orders_services.order_id','DESC')->get();
 
         
        $rows = array();
        $i = 1;
        foreach ($list as $key => $val) {
            $rows[$key]['i'] = $i;
            //$rows[$key]['order_id'] = $val->order_id;
            $rows[$key]['order_no'] = $val->order_no;
            $rows[$key]['customer'] = $val->customer;
            $rows[$key]['vendor'] = $val->company_name;
            $rows[$key]['total_amount'] = ($val->hourly_rate * $val->qty)??'';
            $rows[$key]['payment_mode'] = payment_mode($val->payment_mode)??'';
            $rows[$key]['admin_commission'] = ($val->admin_commission)??'';
            $rows[$key]['vendor_commission'] = ($val->vendor_commission)??'';
            $rows[$key]['order_date'] = get_date_in_timezone($val->created_at, 'd-M-y h:i A')??'';
            $i++;
        }
        $headings = [
            "#",
            "Order No.",
            "Customer",
            "Vendor",
            "Total Amount",
            "Payment Mode",
            "Admin Share",
            "Vendor Share",
            "Order Date",
        ];
        $coll = new ExportReports([$rows], $headings);
        $ex = Excel::download($coll, 'commission_report_orders_' . date('d_m_Y_h_i_s') . '.xlsx');
        if (ob_get_length()) ob_end_clean();
        return $ex;
      }
       
        
    }
    public function refund_request(Request $request)
    {
        $page_heading = "Refund Request"; 
        $order_id = $_GET['order_id'] ?? '';
        $name = $_GET['name'] ?? '';
        $from = !empty($_GET['from'])?date('Y-m-d',strtotime($_GET['from'])): '';
        $to = !empty($_GET['to']) ?date('Y-m-d',strtotime($_GET['to'])): '';

        $list =  OrderModel::select('orders.*',DB::raw("CONCAT(users.first_name,' ',users.last_name) as customer_name"),'users.dial_code','users.phone','users.email')->leftjoin('users','users.id','orders.user_id')->with(['customer'=>function($q) use($name){
           $q->where('display_name','like','%'.$name.'%');
        }]);
        if($name)
        {
             $list =$list->whereRaw("concat(first_name, ' ', last_name) like '%" .$name. "%' ");
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
        $list=$list->orderBy('orders.order_id','DESC')->where('refund_requested',1)->paginate(10);
       
        return view('admin.reports.refund_request',compact('page_heading','list','order_id','name','from','to'));
    }
    public function refund_request_services(Request $request)
    {
        $page_heading = "Refund Request Services"; 
        $order_id = $_GET['order_id'] ?? '';
        $name = $_GET['name'] ?? '';
        $from = !empty($_GET['from'])?date('Y-m-d',strtotime($_GET['from'])): '';
        $to = !empty($_GET['to']) ?date('Y-m-d',strtotime($_GET['to'])): '';

        $list =  OrderServiceModel::select('orders_services.*',DB::raw("CONCAT(users.first_name,' ',users.last_name) as customer_name"),'users.dial_code','users.phone','users.email')->leftjoin('users','users.id','orders_services.user_id')->with(['customer'=>function($q) use($name){
           $q->where('display_name','like','%'.$name.'%');
        }]);
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
        if($from){
            $list=$list->whereDate('orders_services.created_at','>=',$from.' 00:00:00');
        }
        if($to){
            $list=$list->where('orders_services.created_at','<=',$to.' 23:59:59');
        }
        $list=$list->orderBy('orders_services.order_id','DESC')->where('refund_requested',1);

        if (!empty($_GET)) {
        $list = $list->paginate(500);
        }
        else
        {
          $list=$list->paginate(10);  
        }
       
        return view('admin.reports.refund_request_services',compact('page_heading','list','order_id','name','from','to'));
    }
    function change_status_accepted(Request $request)
    {
        $status  = "0";
        $message = "";
        
        $up = ['refund_accepted'=>1,
               'refund_accepted_date'=>gmdate('Y-m-d H:i:s')];

        $check = OrderModel::where('order_id',$request->order_id)->update($up);
        $status  = 1;
        $message = "Accepted successfully";
        
       
        echo json_encode(['status'=>$status,'message'=>$message]);
    }
    function change_status_accepted_service(Request $request)
    {
        $status  = "0";
        $message = "";
        
        $up = ['refund_accepted'=>1,
               'refund_accepted_date'=>gmdate('Y-m-d H:i:s')];

        $check = OrderServiceModel::where('order_id',$request->order_id)->update($up);
        $status  = 1;
        $message = "Accepted successfully";
        
       
        echo json_encode(['status'=>$status,'message'=>$message]);
    }
}
