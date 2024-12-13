<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CustomerExport;
use App\Http\Controllers\Controller;
use App\Models\CampaignModel;
use App\Models\UserTable;
use App\Models\VendorModel;
use App\Models\VendorDetailsModel;
use App\Models\BankdataModel;
use App\Models\CountryModel;
use App\Models\IndustryTypes;
use App\Models\States;
use App\Models\Cities;
use App\Models\BankModel;
use App\Models\BankCodetypes;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use Hash;
use App\Models\User;
use App\Models\WalletHistory;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Customers";
        $customers = UserTable::where(['mazouz_customer' => 1])->get();
        
        return view('admin.customer.list', compact('page_heading', 'customers'));
    }

    public function exportExcel() {
        $export_list = [];
        $customers = UserTable::where(['mazouz_customer' => 1])->get();

        foreach ($customers as $customer) {
            $export_list[] = [
                $customer->user_first_name . ' ' . $customer->user_last_name,
                $customer->user_email_id,
                '+' . $customer->dial_code . $customer->phone_number,
                $customer->user_status ? 'Active' : 'Disable'
            ];
        }

        return Excel::download(new CustomerExport($export_list), 'customer.xlsx');
    }

    public function changeStatus(Request $request)
    {
        try {
            $campaign = UserTable::find($request->id);
            if ($campaign) {
                $campaign->user_status = $request->status;
                $campaign->save();

                return return_response('1', 200, 'Status changed successfully');
            } else {
                return return_response('0', 404, 'User not found');
            }
        } catch (\Exception $exception) {
            return return_response('0', 500, 'Something went wrong');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Customers";
        $mode = "create";
        $id = "";
        $prefix = "";
        $name = "";
        $dial_code = "";
        $image = "";
        $active = "1";
        $states = [];
        $cities = [];
        $countries = CountryModel::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        $industry = IndustryTypes::where(['deleted' => 0])->get();
        $banks = BankModel::get();
        $banks_codes = BankCodetypes::get();
        return view("admin.customer.create", compact('page_heading', 'industry', 'id', 'name', 'dial_code', 'active', 'prefix', 'countries', 'states', 'cities', 'banks', 'banks_codes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
        ]);
        if (!empty($request->password)) {
            $validator = Validator::make($request->all(), [
                'confirm_password' => 'required',
            ]);
        }
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $input = $request->all();
            $check_exist = VendorModel::where('email', $request->email)->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $check_exist_phone = VendorModel::where('phone', $request->phone)->where('id', '!=', $request->id)->get()->toArray();
                if (empty($check_exist_phone)) {
                    $ins = [
                        'name' => $request->first_name . ' ' . $request->last_name,
                        // 'email'      => $request->email,
                        'dial_code' => $request->dial_code,
                        'phone' => $request->phone,
                        'role' => '2',//customer
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'phone_verified' => 1,
                        'wallet_amount' => empty($request->wallet_amount) ? '0' : $request->wallet_amount,
                    ];
                    if ($request->id == "") {
                        $ins['email'] = $request->email;
                    }


                    if ($request->password) {
                        $ins['password'] = bcrypt($request->password);
                    }

                    if ($request->file("image")) {
                        $response = image_upload($request, 'company', 'image');
                        if ($response['status']) {
                            $ins['user_image'] = $response['link'];
                        }
                    }


                    if ($request->id != "") {
                        $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                        $user = VendorModel::find($request->id);
                        $user->update($ins);

                        $vendordata = VendorDetailsModel::where('user_id', $request->id)->first();
                        if (empty($vendordata->id)) {
                            $vendordatils = new VendorDetailsModel();
                            $vendordatils->user_id = $request->id;
                        } else {
                            $vendordatils = VendorDetailsModel::find($vendordata->id);
                        }


                        $status = "1";
                        $message = "Customer updated succesfully";
                    } else {
                        $ins['created_at'] = gmdate('Y-m-d H:i:s');
                        $userid = VendorModel::create($ins)->id;


                        $vendordatils = new VendorDetailsModel();
                        $vendordatils->user_id = $userid;
                        $vendordatils->updated_at = gmdate('Y-m-d H:i:s');

                        $bankdata = new BankdataModel();
                        $bankdata->user_id = $userid;

                        $status = "1";
                        $message = "Customer added successfully";
                    }


                    $vendordatils->industry_type = 1;
                    $vendordatils->address1 = $request->address1;
                    $vendordatils->address2 = $request->address2;
                    $vendordatils->street = $request->street;
                    $vendordatils->zip = $request->zip;


                    //logo
                    if ($request->file("user_image")) {
                        $response = image_upload($request, 'company', 'user_image');
                        if ($response['status']) {
                            $user->user_image = $response['link'] ?? '';
                        }
                    }
                    //logo end
                    $vendordatils->save();
                    $user->save();


                } else {
                    $status = "0";
                    $message = "Phone number should be unique";
                    $errors['phone'] = "Already exist";
                }

            } else {
                $status = "0";
                $message = "Email should be unique";
                $errors['email'] = $request->email . " already added";
            }

        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $page_heading = "View Customer";
        $datamain = VendorModel::find($id);
        $datamain->vendordatils = VendorDetailsModel::where('user_id', $id)->first();
        $datamain->bankdetails = BankdataModel::where('user_id', $id)->first();

        $countries = CountryModel::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        $industry = IndustryTypes::where(['deleted' => 0])->get();
        $banks = BankModel::get();
        $banks_codes = BankCodetypes::get();
        $user_image = asset($datamain->user_image);
        $states = States::where(['deleted' => 0, 'active' => 1, 'country_id' => $datamain->country_id])->orderBy('name', 'asc')->get();

        $cities = Cities::where(['deleted' => 0, 'active' => 1, 'state_id' => $datamain->state_id])->orderBy('name', 'asc')->get();
        if ($datamain) {
            return view("admin.customer.view_customer", compact('page_heading', 'datamain', 'id', 'countries', 'states', 'cities', 'user_image', 'industry', 'banks', 'banks_codes'));
        } else {
            abort(404);
        }


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $page_heading = "Edit Customer";
        $datamain = VendorModel::find($id);
        $datamain->vendordatils = VendorDetailsModel::where('user_id', $id)->first();
        $datamain->bankdetails = BankdataModel::where('user_id', $id)->first();

        $countries = CountryModel::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        $industry = IndustryTypes::where(['deleted' => 0])->get();
        $banks = BankModel::get();
        $banks_codes = BankCodetypes::get();
        $user_image = asset($datamain->user_image);
        $states = States::where(['deleted' => 0, 'active' => 1, 'country_id' => $datamain->country_id])->orderBy('name', 'asc')->get();

        $cities = Cities::where(['deleted' => 0, 'active' => 1, 'state_id' => $datamain->state_id])->orderBy('name', 'asc')->get();
        if ($datamain) {
            return view("admin.customer.create", compact('page_heading', 'datamain', 'id', 'countries', 'states', 'cities', 'user_image', 'industry', 'banks', 'banks_codes'));
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (VendorModel::where('id', $request->id)->update(['active' => $request->status])) {
            $status = "1";
            $msg = "Successfully activated";
            if (!$request->status) {
                $msg = "Successfully deactivated";
            }
            $message = $msg;
        } else {
            $message = "Something went wrong";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    public function verify(Request $request)
    {
        $status = "0";
        $message = "";
        if (VendorModel::where('id', $request->id)->update(['verified' => $request->status])) {
            $status = "1";
            $msg = "Successfully verified";
            if (!$request->status) {
                $msg = "Successfully updated";
            }
            $message = $msg;
        } else {
            $message = "Something went wrong";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    public function destroy($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $datatb = VendorModel::find($id);
        if ($datatb) {
            $datatb->deleted = 1;
            $datatb->active = 0;
            $datatb->save();
            $status = "1";
            $message = "Customer removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }

    public function add_wallet_balance(Request $request)
    {
        $customer_id = $request->customer_id ?? 0;
        $wallet_balance = $request->wallet_balance ?? '';

        if ($customer_id == 0) {
            echo json_encode(['status' => "0", 'message' => "Invalid Customer"]);
            exit;
        } elseif ($wallet_balance == '' || $wallet_balance <= 0) {
            echo json_encode(['status' => "0", 'message' => "Invalid Amount"]);
            exit;
        }

        $user_wallet_amount = VendorModel::where('id', $request->customer_id)->first()->wallet_amount;

        $status = "0";
        $message = "";
        if (VendorModel::where('id', $request->customer_id)->update(['wallet_amount' => ($wallet_balance + $user_wallet_amount)])) {
            $status = "1";
            $message = "Successfully recharged";

            DB::table('wallet_histories')->insert(['user_id' => $request->customer_id, 'wallet_amount' => $wallet_balance,
                'pay_type' => 10, 'description' => 'admin added amount in user wallet', 'created_at' => gmdate('Y-m-d'), 'updated_at' => gmdate('Y-m-d')]);
        } else {
            $message = "Something went wrong";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    public function wallet_history($id = 0)
    {
        $page_heading = "Wallet History";
        $user = VendorModel::find($id);

        $wallet_history = WalletHistory::where(['user_id' => $id])->orderBy('id', 'desc')->paginate(20);

        return view('admin.customer.wallet_history', compact('page_heading', 'user', 'wallet_history', 'id'));
    }

    public function ref_history($id = 0)
    {
        $page_heading = "Referal Code History";
        $user = VendorModel::find($id);

        $ref_history = \App\Models\RefHistory::with('sender', 'accepted_user')->where(['sender_id' => $id])->orderBy('id', 'desc')->paginate(20);

        return view('admin.customer.ref_history', compact('page_heading', 'user', 'ref_history', 'id'));
    }

    public function wallet_top_up(REQUEST $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';
        $validator = Validator::make($request->all(), [
            'pay_type' => 'required',
            'amount' => 'required'

            // 'last_name' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $request->id;
            $pay_type = $request->pay_type;
            $amount = $request->amount;

            $w_data = [
                'user_id' => $user_id,
                'wallet_amount' => $amount,
                'pay_method' => 0,
                'pay_type' => ($pay_type == 'credit') ? 'credited' : 'debited',
                'description' => ($pay_type == 'credit') ? 'Amount credited from admin' : 'Amount debited by admin',
            ];
            if (wallet_history($w_data)) {
                $users = VendorModel::find($user_id);
                if ($pay_type == 'credit') {
                    $users->wallet_amount = $users->wallet_amount + $amount;
                } else {
                    $users->wallet_amount = $users->wallet_amount - $amount;
                }
                $users->save();
                $status = "1";
                $message = "Wallet top up successfully completed";
            }
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }

}
