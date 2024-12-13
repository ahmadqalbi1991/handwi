<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Models\User as Users;
use App\Models\UserEarning;

class EarningController extends Controller
{
	public function requests(Request $request)
	{
		$page_heading = "Earnings";
        $vendors = Users::where('role', '3')->orderBy('id', 'desc')->get();
        $driver = [];//\App\Models\User::getAllDrivers();
        return view('admin.earning.index',compact('page_heading','vendors','driver'));
	}

	public function getData(Request $request)
    {


        // if (!check_permission('module_menu_view')) {
        //     $json_data = [
        //         "draw" => 0,
        //         "recordsTotal" => 0,
        //         "recordsFiltered" => 0,
        //         "data" => [],
        //     ];

        //     echo json_encode($json_data);
        //     die();
        // }


        $columns = [
            0 => 'order_id',
            1 => 'order_number',
            2 => 'user_id',
            3 => 'grand_total',
            4 => 'outlet_id'
        ];



        $data = \App\Models\OrderModel::where('status',4)->with(array('users'=>function($query){
            $query->select('id','name','email','user_image');
        }))->with(array('vendor'=>function($query){
            $query->select('id','name','email','user_image');
        }))->whereNotNull('withdraw_status');

        if(isset($request->name)) {
            $data = $data ->where('order_no',$request->name);
        }

        if(isset($request->request_status)) {
            $data = $data ->whereRaw('(withdraw_status='.$request->request_status.' )');
        }
        if(isset($request->vendor_id)) {
            $data = $data ->where('vendor_id',$request->vendor_id);
        }
        if(isset($request->driver_id)) {
            $data = $data ->where('driver_id',$request->driver_id);
        }


        $totalData = $data->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir   = $request->input('order.0.dir');


            $record = $data
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();


        $data = [];
        if (!empty($record)) {
            foreach ($record as $key => $row) {
            	$act = "";
            	if($row->status == 0 )
                $act = '<a class="dropdown-item" onclick="showApproveModal(' . $row->id . ')" title="Delete"  href="javascript:;">Approve</a>   ';

                $action = '
                 <ul class="">
                 <li class="nav-item dropdown user-profile-dropdown">
                     <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                       <span class="flaticon-gear"></span>
                     </a>
                     <div class="dropdown-menu " aria-labelledby="userProfileDropdown">
                     <a class="dropdown-item" title="Edit" onclick="showBankModal(' . $row->user_id . ')"">Bank Details</a>
                      	'.$act.'

                    </div>
                     </li>
                 </ul>';




                $nestedData['id'] = '<div class="expend"></div>
                 <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input record_row"  data-record-id="' . $row->id . '" id="check-' . $row->id . '">
                    <label class="custom-control-label" for="check-' . $row->id . '">&nbsp;</label>
                 </div>';
                $counter = ($request->input('start')) ? ($request->input('start') + ($key + 1)) : ($key + 1);
                $nestedData['id'] = $counter;
                $nestedData['name'] = $row->users->name ?? '';
                $nestedData['chef'] = $row->vendor->name ?? '';
                $nestedData['driver'] = '';//isset($row->get_driver) ?  $row->get_driver->name :'';
                $nestedData['amount'] = amount_currency($row->grand_total);
                $withdraw_status = Config('global.withdraw_status');
                $type="vendor";
                $chefAction = $row->withdraw_status<=1 ? '</br></br><a href="javascript:void(0)" class="btn btn-primary" onClick=changeStatus("'.$type.'",'.$row->order_id.')>Update Status</a>' : '';

                $nestedData['chef_payment_status'] = amount_currency($row->vendor_commission_per)."</br>".$withdraw_status[(int)$row->withdraw_status].$chefAction;
                $nestedData['chef_payment'] = amount_currency($row->vendor_commission_per);
                $nestedData['chef_status'] = $withdraw_status[(int)$row->withdraw_status];

                $nestedData['driver_payment'] = '';//amount_currency($row->delivery_man_commission_amount);
                $nestedData['driver_status'] = '';//$withdraw_status[(int)$row->driver_withdraw_status];

                $type="driver";
                $driverAction = $row->driver_withdraw_status<=1 ? '</br></br><a href="javascript:void(0)" class="btn btn-primary" onClick=changeStatus("'.$type.'",'.$row->order_id.')>Update Status</a>': '';

                $nestedData['driver_payment_status'] = amount_currency($row->delivery_man_commission_amount)."</br>".$withdraw_status[(int)$row->driver_withdraw_status].$driverAction;

                $nestedData['order_id']=order_number($row);
                $nestedData['order_id']=order_number($row);
                $status = '';
                if($row->status == 0 )
                	$status   = "Requested";
                else if($row->status == 1 )
                	$status   = "Paid";
                else if($row->status == 2 )
                	$status   = "Rejected";
                $nestedData['active'] ='<span>
                                            <a href="javascript:;" onclick="showChangeStatusModal('.$row->id.')">
                                                <span class="badge badge-pills outline-badge-success">
                                                   '.$status.'
                                                </span>
                                            </a>
                                        </span>
                                        ';
                $nestedData['updated_at'] = date('j M Y', strtotime($row->updated_at));
                $nestedData['action'] = $action;
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

    public function getBankDetails(Request $request)
	{

		$status = 0;
		$userDetails = \App\Models\User::with('bank')->find($request->id);  //print_r($userDetails);
		if($userDetails->bank !=null) {
			$status = 1;
		}
		$list = "<table class='table table-bordered'>";
		$list .= "<tr><td>Bank Name</td><td>".$userDetails->bank->name_en."</td></tr><tr><td>Branch Name</td><td>".$userDetails->bank_branch."</td></tr><tr><td>Account Number</td><td>".$userDetails->account_no."</td></tr><tr><td>IBAN</td><td>".$userDetails->ifsc."</td></tr><tr><td>Swift Code</td><td>".$userDetails->swift."</td></tr><tr><td>Beneficiary</td><td>".$userDetails->benificiary."</td></tr>";
		$list .= "</table>";
		$json_data = ['status'=>$status,'details'=>$list];
		echo json_encode($json_data);
	}

	public function changeStatus(Request $request)
	{
		$status = 1;
        if($request->order_ids && count($request->order_ids)) {
            $ids = $request->order_ids;
            // if($request->type == 'vendor') {
               $orders =  \App\Models\OrderModel::whereIn('order_id',$ids)->get();//->update(["withdraw_status" => $request->status]);
               foreach ($orders as $key => $row) {
                   $row->withdraw_status = $request->status;
                    $row->save();
               }
            // } else if($request->type == 'driver') {
            //     // \App\Models\OrderModel::whereIn('order_id',$ids)->update(["driver_withdraw_status" => $request->status]);
            // }
        }
        else {
            $earningObj =  \App\Models\OrderModel::where('order_id',$request->order_id)->first();

            if($request->type == 'vendor') {
                $earningObj->withdraw_status = $request->status;
            } else if($request->type == 'driver') {
                // $earningObj->driver_withdraw_status = $request->status;
            }
            $earningObj->save();
        }

		$message = 'Request status has been updated successfully.';
		$json_data = ['status'=>$status,'message'=> $message];
		echo json_encode($json_data);
	}
}
