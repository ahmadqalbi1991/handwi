<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\CashPoints;
use App\Models\Orders;
use App\Models\CustomerRequests;
use App\Models\UserAddress;
use Validator;
use Auth,DB;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        die("here");
        $page_title = 'Payments';
        $details = Orders::select(['chef_withdraw_status',DB::raw('sum(restaurant_commission_amount)')])->where('order_status',4)->where('chef_id',Auth::user()->id)->groupBy('chef_withdraw_status')->get();  
        $payStatus = []; 
        $total = 0;
        foreach ($details as $key => $value) {
            $payStatus[$value->chef_withdraw_status] = $value->sum;
            $total += $value->sum;
        } 
        return view('portal.earning.index', compact('page_title','payStatus','total'));
    }

    

    public function getEarningData(Request $request)
    {
        $columns = [
            0 => 'orders.id',
            1 => 'orders.id',
            2 => 'users.name',
            3 => 'orders_sum_grand_total',
            4 => 'restaurant_commission_amount',
            5 => 'chef_withdraw_status',
            6 => 'orders.created_at'
        ];
        $data = \App\Models\Orders::select(['orders.*'])->where('chef_id',Auth::user()->id)
            ->where('order_status',4)->with('users')
            ->join('users','users.id','orders.user_id');

        if(isset($request->order_number)) {
            $data = $data ->where('order_number',$request->order_number);
        }

        if(isset($request->request_status)) {
            $data = $data ->where('chef_withdraw_status',$request->request_status);
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
                $nestedData['st'] = (int)$row->chef_withdraw_status == 0 ? '<input type="checkbox" name="status_ids[]" class="changestatus_check" value="'.$row->id.'">' : '';
                $nestedData['order_id'] = $row->order_number;
                $nestedData['customer'] = $row->users->name;
                $withdraw_status = Config('global.withdraw_status');              
                $nestedData['status'] = $withdraw_status[(int)$row->chef_withdraw_status];
                $nestedData['commission'] = $row->restaurant_commission_amount;               
               
                $nestedData['grand_total'] = $row->grand_total;
                $nestedData['created_at'] = get_date_in_timezone($row->created_at, 'd-M-y h:i A');

                   $action = (int)$row->chef_withdraw_status == 0 ?  '
                        <a class="btn btn-primary" title="Customer View" onclick="sendRequest(' . $row->id . ')">Send request</a>            
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
        $order = \App\Models\Orders::find($id);
        if($order->chef_id == Auth::user()->id && $order->chef_withdraw_status == 0  ) {
            $order->chef_withdraw_status = 1;
            $order->chef_withdraw_request_at = getcreatedAt();
            $order->save();
            $status = 1; 
            $message = "Request Send Successfully";
        } else {
            $message = "Invalid Request";
        }
        $json_data = [
            "status" => $status,
            "message" => $message
        ];

        echo json_encode($json_data);
    }
}