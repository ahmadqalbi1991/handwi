<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\CashPoints;
use App\Models\OrderModel;
use App\Models\CustomerRequests;
use App\Models\UserAddress;
use App\Models\OrderServiceItemsModel;
use App\Models\OrderServiceModel;
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
use Validator;
use Auth,DB;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $page_heading = 'Payments';
        $details = OrderModel::select(['withdraw_status',DB::raw('sum(vendor_commission_per::double precision)'),DB::raw('sum(grand_total)'),DB::raw('count(order_id)')])
        ->where('status',4)
        ->where('vendor_id',Auth::user()->id)
        ->groupBy('withdraw_status')
        ->get();  

        $payStatus = []; 
        $total = 0;
        foreach ($details as $key => $value) {
            $payStatus[$value->withdraw_status] = $value->sum;
            $total += $value->sum;
        } 
        // ->where('payment_mode','!=',5)
        $total_vendor_commission = OrderModel::where('status',4)->where('vendor_id',Auth::user()->id)->get()->sum('vendor_commission_per');  
        $vendor_commission_approved = OrderModel::where('status',4)->where('withdraw_status',3)->where('vendor_id',Auth::user()->id)->get()->sum('vendor_commission_per');  
        $cod_commission = OrderModel::where('status',4)->where('payment_mode',5)->where('withdraw_status',3)->where('vendor_id',Auth::user()->id)->get()->sum('admin_commission_per');  
        $cod_amount = OrderModel::where('status',4)->where('payment_mode',5)->where('withdraw_status',3)->where('vendor_id',Auth::user()->id)->get()->sum('vendor_commission_per');  
        // dd($total_vendor_commission,$vendor_commission_approved);

       if($request->export != 1){
        return view('portal.earning.index', compact('page_heading','payStatus','total','total_vendor_commission','vendor_commission_approved','cod_commission','cod_amount'));
       }else{
           
           $data = OrderModel::where('vendor_id',Auth::user()->id)->where('status',4)->orderBy('order_id','desc');         

            // if(isset($request->order_number)) {
            //     $data = $data ->where('order_number',$request->order_number);
            // }
    
            if(isset($request->request_status)) {
                $data = $data ->where('withdraw_status',$request->request_status);
            }
            if(isset($request->order_number)) {
                $data = $data ->where('order_no',$request->order_number);
            }
            if(isset($request->payment_mode)) {
                $data = $data ->where('payment_mode',$request->payment_mode);
            }
            if(isset($request->from_date)) {
                $data = $data ->whereDate('created_at','>=',date('Y-m-d',strtotime($request->from_date)));
            }
            if(isset($request->to_date)) {
                $data = $data ->whereDate('created_at','<=',date('Y-m-d',strtotime($request->to_date)));
            }
           $list=$data->get();
                $rows = array();
                $i = 1;
                foreach ($list as $key => $val) {
                    $rows[$key]['i'] = $i;
                    $rows[$key]['order_no'] = $val->order_no;
                    $rows[$key]['customer'] = $val->users->name;
                    $rows[$key]['grand_total'] = $val->grand_total;
                    
                    $admin_share = 0;
                    if($val->payment_mode == 5 ){
                        $admin_share = -$val->admin_commission ;
                    }else { 
                        $admin_share = $val->admin_commission ;
                    }
                    $rows[$key]['admin_commission']     = $admin_share;
                    $rows[$key]['vendor_earning']       = ($val->vendor_commission)??'';
                    $withdraw_status = Config('global.withdraw_status');

                    $rows[$key]['withdraw_status']       = $withdraw_status[(int)$val->withdraw_status] ?? '';
                    $rows[$key]['payment_mode']         = payment_mode($val->payment_mode)??'';
                    
                    $rows[$key]['created_date']      = get_date_in_timezone($val->created_at, 'd-M-y H:i A');
                    
                    $i++;
                }
                $headings = [
                    "#",
                    "Order No",
                    "Customer Name",
                    "Grand Total",
                    "Admin Share",
                    "Vendor Share",
                    "Withdraw Status",
                    "Payment Mode",
                    "Created Date",
                ];
                $coll = new ExportReports([$rows], $headings);
                // dd([$rows], $headings);
                $ex = Excel::download($coll, str_replace(' ', '_', $page_heading).'_' . date('d_m_Y_h_i_s') . '.xlsx');
                if (ob_get_length()) ob_end_clean();
                return $ex;
       }
       
        
    }

    

    public function getEarningData(Request $request)
    {
        $columns = [
            0 => 'order_id',
            1 => 'order_id',
            2 => 'users.name',
            3 => 'orders_sum_grand_total',
            4 => 'admin_commission_per',
            5 => 'vendor_commission_per',
            6 => 'payment_mode',
            7 => 'withdraw_status',
            8 => 'orders.created_at'
        ]; 

         // updated query                                   
        $data = OrderModel::where('vendor_id',Auth::user()->id)->where('status',4)->orderBy('order_id','desc');         

        // if(isset($request->order_number)) {
        //     $data = $data ->where('order_number',$request->order_number);
        // }

        if(isset($request->request_status)) {
            $data = $data ->where('withdraw_status',$request->request_status);
        }
        if(isset($request->order_number)) {
            $data = $data ->where('order_no',$request->order_number);
        }
        if(isset($request->payment_mode)) {
            $data = $data ->where('payment_mode',$request->payment_mode);
        }
        if(isset($request->from_date)) {
            $data = $data ->whereDate('created_at','>=',date('Y-m-d',strtotime($request->from_date)));
        }
        if(isset($request->to_date)) {
            $data = $data ->whereDate('created_at','<=',date('Y-m-d',strtotime($request->to_date)));
        }
        $totalData = $data->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir   = $request->input('order.0.dir');






        if (empty($request->input('search.value'))) {
            $record = $data
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();  
        } else {
            $search = $request->input('search.value');


            $data->where('users.name', 'LIKE', "%{$search}%");

            $record = $data->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $data->count();
        }

        $data = [];
        if (!empty($record)) {
            foreach ($record as $key => $row) {

                $counter = ($request->input('start')) ? ($request->input('start') + ($key + 1)) : ($key + 1);
                $verified = $row->users->phone_verified == 1 ? '<i class="mdi mdi-check-circle-outline text-success" title="Verified"></i>' : '';
                $name =  '<div class="d-flex align-items-start">
                        <div class="mr-2 mt-2">
                           ' . user_symbol($row->users->name, $row->users->id) . '
                        </div>
                        <div>
                            <div>' . $row->users->name . '</div>
                            <div class="text-muted">' . $row->users->email . '</div>
                            <div><span>' . $row->users->dial_code . $row->users->phone_number . '</span> ' . $verified . '</div>
                        </div>
                    </div>';
                $nestedData['id'] = $counter;
                $nestedData['st'] = (int)$row->withdraw_status == 0 || (int)$row->withdraw_status == 4 ? '<input type="checkbox" name="status_ids[]" class="changestatus_check" value="'.$row->id.'">' : '';
                $nestedData['order_id'] = $row->order_no;
                $nestedData['customer'] = $row->users->name;
                $withdraw_status = Config('global.withdraw_status');              
                $nestedData['status'] = $withdraw_status[(int)$row->withdraw_status];
                $nestedData['admin_commission_per'] = ($row->payment_mode == 5 ? '-' : '') .$row->admin_commission_per;    
                $nestedData['commission'] = $row->vendor_commission_per;    
                $nestedData['payment_mode'] = payment_mode($row->payment_mode);                   
               
                $nestedData['grand_total'] = $row->grand_total;
                $nestedData['created_at'] = get_date_in_timezone($row->created_at, 'd-M-y h:i A');;

                   $action = (int)$row->withdraw_status == 0 || (int)$row->withdraw_status == 4 ?  '
                        <a class="btn btn-primary" title="Customer View" onclick="sendRequest(' . $row->order_id . ')">Send request</a>            
                   ':'';
                $nestedData['action'] = $action ;
                $data[] = $nestedData;
            }
        }

        $json_data = [
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data,
        ];

        echo json_encode($json_data);
    }

    public function getEarningDataService(Request $request)
    {
        $columns = [
            0 => 'orders.order_id',
            1 => 'orders.order_id',
            2 => 'users.name',
            3 => 'orders_sum_grand_total',
            4 => 'vendor_commission_per',
            5 => 'withdraw_status',
            6 => 'orders.created_at'
        ];
        $data = OrderServiceItemsModel::where('accepted_vendor',Auth::user()->id)->where('order_status',4)->orderBy('id','desc');

        // if(isset($request->order_number)) {
        //     $data = $data ->where('order_number',$request->order_number);
        // }

        if(isset($request->request_status)) {
            $data = $data ->where('withdraw_status',$request->request_status);
        }

        if(isset($request->order_number)) {
            $data = $data ->where('order_no',$request->order_number);
        }
        if(isset($request->payment_mode)) {
            $data = $data ->where('payment_mode',$request->payment_mode);
        }
        if(isset($request->from_date)) {
            $data = $data ->whereDate('created_at','>=',date('Y-m-d',strtotime($request->from_date)));
        }
        if(isset($request->to_date)) {
            $data = $data ->whereDate('created_at','<=',date('Y-m-d',strtotime($request->to_date)));
        }
        $totalData = $data->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir   = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $record = $data
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();  
        } else {
            $search = $request->input('search.value');


            $data->where('users.name', 'LIKE', "%{$search}%");

            $record = $data->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $data->count();
        }

        $data = [];
        if (!empty($record)) {
            foreach ($record as $key => $row) {

                $counter = ($request->input('start')) ? ($request->input('start') + ($key + 1)) : ($key + 1);
                $verified = $row->users->phone_verified == 1 ? '<i class="mdi mdi-check-circle-outline text-success" title="Verified"></i>' : '';
                $name =  '<div class="d-flex align-items-start">
                        <div class="mr-2 mt-2">
                           ' . user_symbol($row->users->name, $row->users->id) . '
                        </div>
                        <div>
                            <div>' . $row->users->name . '</div>
                            <div class="text-muted">' . $row->users->email . '</div>
                            <div><span>' . $row->users->dial_code . $row->users->phone_number . '</span> ' . $verified . '</div>
                        </div>
                    </div>';
                $nestedData['id'] = $counter;
                $nestedData['st'] = (int)$row->withdraw_status == 0 || (int)$row->withdraw_status == 4 ? '<input type="checkbox" name="status_ids[]" class="changestatus_check" value="'.$row->id.'">' : '';
                $nestedData['order_id'] = order_number($row);
                $nestedData['customer'] = $row->users->name;
                $withdraw_status = Config('global.withdraw_status');              
                $nestedData['status'] = $withdraw_status[(int)$row->withdraw_status];
                $nestedData['commission'] = $row->vendor_commission_per;               
               
                $nestedData['grand_total'] = $row->grand_total;
                $nestedData['created_at'] = date('j M Y', strtotime($row->created_at));

                   $action = (int)$row->withdraw_status == 0 || (int)$row->withdraw_status == 4 ?  '
                        <a class="btn btn-primary" title="Customer View" onclick="sendRequest(' . $row->order_id . ')">Send request</a>            
                   ':'';
                $nestedData['action'] = $action ;
                $data[] = $nestedData;
            }
        }

        $json_data = [
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data,
        ];

        echo json_encode($json_data);
    }

    public function sendRequest(Request $request)
    {
        $status = 0; $message = "";

        $id =  $request->order_id;
        $order = \App\Models\OrderModel::where('order_id',$id)->first();
        if($order && $order->vendor_id == Auth::user()->id  ) {
           if($order->withdraw_status == 0 || $order->withdraw_status == 4){
                $order->withdraw_status = 1;
                $order->withdraw_request_at = getcreatedAt();
                $order->save();
                $status = 1; 
                $message = "Request Send Successfully";
           }
        } else {
            $message = "Invalid Request";
        }
        $json_data = [
            "status" => $status,
            "message" => $message
        ];

        echo json_encode($json_data);
    }

    public function sendRequestService(Request $request)
    {
        $status = 0; $message = "";

        $item_id =  $request->item_id;
        $order = \App\Models\OrderServiceItemsModel::where(['id'=>$item_id])->first();
        if($order){
           if($order->withdraw_status == 0 || $order->withdraw_status == 4){
                $order->withdraw_status = 1;
                $order->withdraw_request_at = getcreatedAt();
                $order->save();
                $status = 1; 
                $message = "Request Send Successfully";
           }
        } else {
            $message = "Invalid Request";
        }
        $json_data = [
            "status" => $status,
            "message" => $message
        ];

        echo json_encode($json_data);
    }

    public function service_earnings(Request $request)
    {
        $page_heading = 'Payments';
        $total = 0;
        if(Auth::user()->activity_id == 6 || Auth::user()->activity_id == 1 || Auth::user()->activity_id == 4)
        {
           $request_status = $_GET['request_status'] ?? '';
           $order_number = $_GET['order_number'] ?? '';
           $from_date = $_GET['from_date'] ?? '';
           $to_date = $_GET['to_date'] ?? '';
           $payment_mode = $_GET['payment_mode'] ?? '';
           $datamain = OrderServiceItemsModel::join('orders_services','orders_services.order_id','=','orders_services_items.order_id')
           ->leftjoin('users','users.id','=','orders_services.user_id')
           ->select(['orders_services_items.*','orders_services.grand_total','orders_services.admin_commission as admin_commission','orders_services.vendor_commission as vendor_commission','orders_services.payment_mode','orders_services.order_no','users.name','orders_services.service_charge','orders_services_items.created_at as created_at'])
           ->where('orders_services_items.accepted_vendor',Auth::user()->id)
           ->where('orders_services_items.order_status',4)->orderBy('order_id','desc');
           if(is_numeric($request_status))
           {
            $datamain = $datamain->where('orders_services_items.withdraw_status',$request_status);
           }

           if($payment_mode){
            $datamain = $datamain->where('payment_mode',$payment_mode);
           }
           if($order_number){
            $newString = substr($order_number, 14);
            if($newString){
                $datamain = $datamain->where('orders_services_items.order_id',$newString);
            }
                
           }
           if($from_date){
            $datamain = $datamain->whereDate('orders_services_items.created_at','>=',date('Y-m-d',strtotime($from_date)));
           }
           if($to_date){
            $datamain = $datamain->whereDate('orders_services_items.created_at','<=',date('Y-m-d',strtotime($to_date)));
           }

           $list = $datamain;
           $datamain = $datamain->distinct('orders_services_items.order_id')->paginate(10);
           foreach ($datamain as $key => $value) {
            
            $datamain[$key]->order_no = $value->order_no;
           }
           
           //printr($datamain->toArray()); exit;

           $total_vendor_commission = \App\Models\OrderServiceModel::where('orders_services_items.accepted_vendor',Auth::user()->id)
            ->where('order_status',config('global.order_status_delivered'))
            ->leftjoin('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')->get()->sum('vendor_commission');
            $vendor_commission_approved = \App\Models\OrderServiceModel::where('orders_services_items.accepted_vendor',Auth::user()->id)->where('withdraw_status',3)->where('payment_mode','!=',5)
            ->where('order_status',config('global.order_status_delivered'))
            ->leftjoin('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')->get()->sum('vendor_commission');
            $cod_amount = \App\Models\OrderServiceModel::where('orders_services_items.accepted_vendor',Auth::user()->id)->where('withdraw_status',3)
            ->where('order_status',config('global.order_status_delivered'))->where('payment_mode',5)
            ->leftjoin('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')->get()->sum('vendor_commission');
            $payment_recived = \App\Models\OrderServiceModel::where('orders_services_items.accepted_vendor',Auth::user()->id)->where('withdraw_status',3)
            ->where('order_status',config('global.order_status_delivered'))->where('payment_mode',5)
            ->leftjoin('orders_services_items','orders_services_items.order_id','=','orders_services.order_id')->get();
            $payment_recived_cod = 0;
            foreach ($payment_recived as $key => $value) {
                $payment_recived_cod = $payment_recived_cod + (($value->hourly_rate * $value->qty) + $value->vat - $value->discount);
            }
            if($request->export != 1){
                return view('portal.earning.service', compact('page_heading','datamain','cod_amount','payment_recived_cod','total','request_status','total_vendor_commission','vendor_commission_approved'));
            }else{
                $list=$list->get();
                $rows = array();
                $i = 1;
                foreach ($list as $key => $val) {
                    $rows[$key]['i'] = $i;
                    $rows[$key]['order_no'] = $val->order_no."_".$val->id;
                    $rows[$key]['customer'] = $val->name??$val->customer_name;
                    $rows[$key]['grand_total'] = ($val->hourly_rate * $val->qty) + $val->vat - $val->discount;
                    
                    $admin_share = 0;
                    if($val->payment_mode == 5 ){
                        $admin_share = -$val->admin_commission ;
                    }else { 
                        $admin_share = $val->admin_commission ;
                    }
                    $rows[$key]['admin_commission']     = $admin_share;
                    $rows[$key]['vendor_earning']       = ($val->vendor_commission)??'';
                    $withdraw_status = Config('global.withdraw_status');

                    $rows[$key]['withdraw_status']       = $withdraw_status[(int)$val->withdraw_status] ?? '';
                    $rows[$key]['payment_mode']         = payment_mode($val->payment_mode)??'';
                    
                    $rows[$key]['created_date']      = get_date_in_timezone($val->created_at, 'd-M-y H:i A');
                    
                    $i++;
                }
                $headings = [
                    "#",
                    "Order No",
                    "Customer Name",
                    "Grand Total",
                    "Admin Share",
                    "Vendor Share",
                    "Withdraw Status",
                    "Payment Mode",
                    "Order Status",
                    "Created Date",
                ];
                $coll = new ExportReports([$rows], $headings);
                // dd([$rows], $headings);
                $ex = Excel::download($coll, str_replace(' ', '_', $page_heading).'_' . date('d_m_Y_h_i_s') . '.xlsx');
                if (ob_get_length()) ob_end_clean();
                return $ex;
            }
        }
       
        
    }
}