<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\OrderModel;
use App\Models\OrderProductsModel;
use App\Models\User;
use App\Models\UserAdress;
use App\Models\OrderServiceModel;
use App\Models\Maintainance;
use App\Models\OrderServiceItemsModel;
use App\Models\Contracting;
use DB;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Validator;

class ContractingController extends Controller
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    private function validateAccesToken($access_token)
    {

        $user = User::where(['user_access_token' => $access_token])->get();

        if ($user->count() == 0) {
            http_response_code(401);
            echo json_encode([
                'status' => (string) 0,
                'message' => login_message(),
                'oData' => [],
                'errors' => (object) [],
            ]);
            exit;
        } else {
            $user = $user->first();
            if ($user->active == 1) {
                return $user->id;
            } else {
                http_response_code(401);
                echo json_encode([
                    'status' => (string) 0,
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ]);
                exit;
                return response()->json([
                    'status' => (string) 0,
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ], 401);
                exit;
            }
        }
    }

    public function my_contracts(REQUEST $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $contracts = Contracting::orderBy('id', 'desc')
            ->with('building_type')->where(['user_id' => $user_id, 'deleted'=> 0 ])->get();
            
            /*echo "<pre>";
                print_r($contracts);
            exit(" || ");*/
            
            if($contracts){
                foreach($contracts as $contract){
                    if($contract->contract_type === 1){
                         $contract->contract_text = 'Fresh';
                    }else{
                         $contract->contract_text = 'Extension';
                    }
                }
            $message = "Data Fetched Successfully";

            }else{
            $message = "Data Not Found";

            }
            
            $o_data = convert_all_elements_to_string($contracts);
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }

    public function contract_maintainance_jobs(REQUEST $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            
            $contract_maintainance_job  = [];

            $contracts = Contracting::orderBy('created_at', 'desc')
            ->with('building_type')->where(['user_id' => $user_id, 'deleted'=> 0 ])->get();
            foreach($contracts as $contract)
            { 
                if($contract->contract_type === 1){
                    $contract->contract_text = 'Fresh';
                }else{
                    $contract->contract_text = 'Extension';
                }
                $contract->name = 'contract';
                if(isset($contract)){
                 array_push($contract_maintainance_job,$contract);
                }
            }

            $maintainances = Maintainance::orderBy('created_at', 'desc')
            ->with('building_type')->where('user_id', $user_id)->get();
            
            foreach($maintainances as $maintainance)
            { 
                $maintainance->name = 'maintainance';
                if(isset($maintainance)){
                   array_push($contract_maintainance_job,$maintainance);
                }
            }

            if($contract_maintainance_job){
                foreach ($contract_maintainance_job as $key => $row)
                {
                    
                    $count[$key] = $row['updated_at'];
                    
                }
                array_multisort($count, SORT_DESC, $contract_maintainance_job);
              $message = "Data Fetched Successfully";
            }else{
                $message = "Data Not Found";
            }
            
            $o_data = convert_all_elements_to_string( $contract_maintainance_job);
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }




    public function my_contract_details(REQUEST $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'contract_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $bu_type =[];
            $contract = Contracting::where(['id' =>  $request->contract_id,'user_id' => $user_id])->with('building_types')->first();
            if($contract){
                if($contract->contract_type === 1){
                     $contract->contract_text = 'Fresh';
                }else{
                     $contract->contract_text = 'Extension';
                }
                $contract->status_text  =  quote_status($contract->status);
                $contract->created_date   = get_date_in_timezone($contract->created_at, config('global.datetime_format'));
                $message = "Data Fetched Successfully";
                $bu_type = $contract->building_types;
            }else{
                $message = "Data Not Found";
            }
            
            $o_data = convert_all_elements_to_string($contract);
            if(!empty($bu_type)){
                $o_data->building_type = convert_all_elements_to_string($bu_type);
            }
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
    public function cancel_order(REQUEST $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $order = OrderModel::with(['products'])->where('user_id', $user_id)->where('order_id', $request->order_id)->first();


            if ($order) {
                $highest_order_prd_status = OrderModel::where('order_id', $request->order_id)->orderby('status', 'desc')->first();

                if (isset($highest_order_prd_status->status) && $highest_order_prd_status->status <= 2) {
                    $amount_to_credit = $order->grand_total;

                    $users = User::find($user_id);
                    // $users->wallet_amount = $users->wallet_amount + $amount_to_credit;
                    // $users->save();
                    $c_st = config('global.order_status_cancelled');
                    OrderModel::where('order_id', $request->order_id)->update(['status' => $c_st]);
                    OrderProductsModel::where('order_id', $request->order_id)->update(['order_status' => $c_st]);
                    $status = (string) 1;
                    $message = "Your order has been cancelled successfully.";


                    $title = 'Order Cancelled';
                    $description = $message;
                    $notification_id = time();
                    $ntype = 'order_cancelled';
                    if (!empty($users->firebase_user_key)) {
                        $notification_data["Notifications/" . $users->firebase_user_key . "/" . $notification_id] = [
                            "title" => $title,
                            "description" => $description,
                            "notificationType" => $ntype,
                            "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                            "orderId" => (string) $request->order_id,
                            "url" => "",
                            "imageURL" => '',
                            "read" => "0",
                            "seen" => "0",
                        ];
                        $this->database->getReference()->update($notification_data);
                    }

                    if (!empty($users->user_device_token)) {
                        send_single_notification(
                            $users->user_device_token,
                            [
                                "title" => $title,
                                "body" => $description,
                                "icon" => 'myicon',
                                "sound" => 'default',
                                "click_action" => "EcomNotification"
                            ],
                            [
                                "type" => $ntype,
                                "notificationID" => $notification_id,
                                "orderId" => (string) $request->order_id,
                                "imageURL" => "",
                            ]
                        );
                    }
                } else {
                    $status = (string) 0;
                    $message = "You can't cancel this order";
                }
            }
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }

    public function refund_request(REQUEST $request)
    {
        $status = 1;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'order_id' => 'required|numeric|min:1',
            'payment_mode' => 'required|numeric|min:1|max:2',
            'type' => 'required|numeric|min:1|max:2',
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            if ($request->type == 1) { //product order
                $order = OrderModel::with(['products'])->where('user_id', $user_id)->where('order_id', $request->order_id)->first();

                if ($order) {
                    $highest_order_prd_status = OrderProductsModel::where('order_id', $request->order_id)->orderby('order_status', 'desc')->first();

                    if (isset($highest_order_prd_status->order_status) && $highest_order_prd_status->order_status == 10) {
                        $amount_to_credit = $order->grand_total;

                        $users = User::find($user_id);
                        // $users->wallet_amount = $users->wallet_amount + $amount_to_credit;
                        // $users->save();
                        $c_st = config('global.order_status_cancelled');
                        $indata = [
                            'refund_method' => $request->payment_mode,
                            'refund_requested' => 1,
                            'refund_requested_date' => gmdate('Y-m-d H:i:s'),
                        ];
                        OrderModel::where('order_id', $request->order_id)->update($indata);
                        //OrderProductsModel::where('order_id', $request->order_id)->update(['order_status'=>$c_st]);
                        $status = 1;
                        $message = "Your order refund request send successfully.";


                        $title = 'Refund request send successfully';
                        $description = $message;
                        $notification_id = time();
                        $ntype = 'refund_request';
                        if (!empty($users->firebase_user_key)) {
                            $notification_data["Notifications/" . $users->firebase_user_key . "/" . $notification_id] = [
                                "title" => $title,
                                "description" => $description,
                                "notificationType" => $ntype,
                                "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                                "orderId" => (string) $request->order_id,
                                "url" => "",
                                "imageURL" => '',
                                "read" => "0",
                                "seen" => "0",
                            ];
                            $this->database->getReference()->update($notification_data);
                        }

                        if (!empty($users->user_device_token)) {
                            send_single_notification(
                                $users->user_device_token,
                                [
                                    "title" => $title,
                                    "body" => $description,
                                    "icon" => 'myicon',
                                    "sound" => 'default',
                                    "click_action" => "EcomNotification"
                                ],
                                [
                                    "type" => $ntype,
                                    "notificationID" => $notification_id,
                                    "orderId" => (string) $request->order_id,
                                    "imageURL" => "",
                                ]
                            );
                        }
                    } else {
                        $status = 0;
                        $message = "You can't request refund for this order";
                    }
                } else {
                    $status = 0;
                    $message = "Error";
                }
            } else {

                //for service
                $order = OrderServiceModel::with(['services'])->where('user_id', $user_id)->where('order_id', $request->order_id)->first();
                if ($order) {
                    $highest_order_prd_status = OrderServiceItemsModel::where('order_id', $request->order_id)->orderby('order_status', 'desc')->first();


                    if (isset($highest_order_prd_status->order_status) && $highest_order_prd_status->order_status == 10) {
                        $users = User::find($user_id);
                        // $users->wallet_amount = $users->wallet_amount + $amount_to_credit;
                        // $users->save();
                        $c_st = config('global.order_status_cancelled');
                        $indata = [
                            'refund_method' => $request->payment_mode,
                            'refund_requested' => 1,
                            'refund_requested_date' => gmdate('Y-m-d H:i:s'),
                        ];
                        OrderServiceModel::where('order_id', $request->order_id)->update($indata);
                        //OrderProductsModel::where('order_id', $request->order_id)->update(['order_status'=>$c_st]);
                        $status = 1;
                        $message = "Your service order refund request send successfully.";


                        $title = 'Service Refund request send successfully';
                        $description = $message;
                        $notification_id = time();
                        $ntype = 'service_refund_request';
                        if (!empty($users->firebase_user_key)) {
                            $notification_data["Notifications/" . $users->firebase_user_key . "/" . $notification_id] = [
                                "title" => $title,
                                "description" => $description,
                                "notificationType" => $ntype,
                                "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                                "orderId" => (string) $request->order_id,
                                "url" => "",
                                "imageURL" => '',
                                "read" => "0",
                                "seen" => "0",
                            ];
                            $this->database->getReference()->update($notification_data);
                        }

                        if (!empty($users->user_device_token)) {
                            send_single_notification(
                                $users->user_device_token,
                                [
                                    "title" => $title,
                                    "body" => $description,
                                    "icon" => 'myicon',
                                    "sound" => 'default',
                                    "click_action" => "EcomNotification"
                                ],
                                [
                                    "type" => $ntype,
                                    "notificationID" => $notification_id,
                                    "orderId" => (string) $request->order_id,
                                    "imageURL" => "",
                                ]
                            );
                        }
                    } else {
                        $status  = 0;
                        $message = "You can't request refund for this service order";
                    }
                } else {
                    $status = 0;
                    $message = "Invalid order id";
                }
            }
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }

      /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function contracting(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $input = $request->all();
            $ins = [
                'description' => $request->description,
                'building_type' => $request->building_type,
                'contract_type' => $request->contract_type,
                'user_id' =>  $user_id,
                'status' => 0,
                'deleted' => 0,
            ];

            // if ($request->file("file")) {
            //     $response = image_save($request, config('global.contracts_image_upload_dir'), 'file');
            //     if ($response['status']) {
            //         $ins['file'] = $response['link'];
            //     }
            // }
            $extra_file_names = [];
            if($request->hasfile('file')) {
                  $i=0;
                  foreach($request->file('file') as $file)
                  {
                    //   $response = image_save($request, config('global.contracts_image_upload_dir'), 'file');
                    //   if ($response['status']) {
                    //     $ins['file'] = $response['link'];
                    //   }
                    $dir = config('global.upload_path')."/".config('global.contracts_image_upload_dir');
                    $file_name2 = time().uniqid().".".$file->getClientOriginalExtension();
                    $file->storeAs(config('global.upload_path')."/".config('global.contracts_image_upload_dir'),$file_name2,config('global.upload_bucket'));
                    $extra_file_names[] = $file_name2;
                    if($i==0){
                        $ins['file'] = $file_name2;
                    }
                    $i++;
                  }
              }
              $ins['multiple_files'] = json_encode($extra_file_names);

            $ins['created_at'] = gmdate('Y-m-d H:i:s');
            Contracting::create($ins);

            $status = "1";
            $message = "Contracting Submitted successfully";
            $d = Contracting::orderBy('id','desc')->get()->first();
            $post_id = $d->id;
            exec("php ".base_path()."/artisan contract_push:self ".$post_id." > /dev/null 2>&1 & ");
            exec("php ".base_path()."/artisan contract_mail:self ".$post_id." > /dev/null 2>&1 & ");
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => $errors], 200);
    }

    function contract_satatus_change(Request $request)
    {
        $status  = "0";
        $message = "";
        $title = "";
        $description = "";

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'contract_id' => 'required',
            'status_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Fill all required fields";
            $errors = $validator->messages();
        } else {

            $users = User::where("user_access_token", $request->access_token)->first();
            if ( $users ) {
                if(Contracting::where('id',$request->contract_id)
                    ->update(['status'=>$request->status_id])){
                    $status = "1";
                  
                    if($request->status_id == 1)
                    {
                        $message = "Contract Quote Accepted Successfully";
                        $title = 'Contract Quote Accepted';
                        $description = "you have successfully accepted quote";
                    }
                    if($request->status_id == 10)
                    {
                        $message = "Contract Quote Cancelled Successfully";
                        $title = 'Contract Quote Cancelled';
                        $description = "you have successfully cancelled quote";
                    }

                    $type = 1;
                    $notification_id = time();
                    $ntype = 'quote_created';

                    // if ( $request->status_id == 1 ) {

                    //     $title = 'Quote Accepted';
                    //     $description = "you have successfully accepted quote";
                    // }

                    if (!empty($users->firebase_user_key)) {
                        $notification_data["Notifications/" . $users->firebase_user_key . "/" . $notification_id] = [
                            "title" => $title,
                            "description" => $description,
                            "notificationType" => $ntype,
                            "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                            "orderId" => (string) $request->contract_id,
                            "Type" => (string) $type,
                            "url" => "",
                            "imageURL" => '',
                            "read" => "0",
                            "seen" => "0",
                        ];
                        $this->database->getReference()->update($notification_data);
                    }

                    if (!empty($users->user_device_token)) {
                        send_single_notification(
                            $users->user_device_token,
                            [
                                "title" => $title,
                                "body" => $description,
                                "icon" => 'myicon',
                                "sound" => 'default',
                                "click_action" => "EcomNotification"
                            ],
                            [
                                "type" => $ntype,
                                "notificationID" => $notification_id,
                                "orderId" => (string) $request->contract_id,
                                "Type" => (string) $type,
                                "imageURL" => "",
                            ]
                        );
                    }

                }else{
                    $message = "Something went wrong";
                }
            } else {
                $status  = "0";
                $message = "Session expired please login";
            }
        }
        
        return response()->json(['status' => $status, 'message' => $message ], 200);
      
    }

    function contract_payment_init(Request $request)
    {
        $status  = "0";
        $message = "";
        $payment_report = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'contract_id' => 'required'
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Fill all required fields";
            $errors = $validator->messages();
        } else {

            $user = User::where("user_access_token", $request->access_token)->first();
            if ( $user ) {
                if(Contracting::where('id',$request->contract_id)->first()){

                    $contract = Contracting::where('id',$request->contract_id)->first();

                    $price = $contract->price;

                    if ( $price <= 1.1 ) {

                        $status = "0";
                        $message = "Invalid contract amount";

                    } else {

                        $address = UserAdress::get_user_default_address($user->id);
                        if (empty($address)) {
                            $status = (string) 0;
                            $message = "You are not added any address, Please add address";
                        } else {


                            // \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                            // $checkout_session = \Stripe\PaymentIntent::create([
                            //     'amount' => $price * 100,
                            //     'currency' => 'JOD',
                            //     'description' => 'Contract amount',
                            //     'shipping' => [
                            //         'name' => $user->name ?? $user->first_name . ' ' . $user->last_name,
                            //         'address' => [
                            //             'line1' => $address->address,
                            //             'city' => $address->city_name,
                            //             'state' => $address->state_name,
                            //             'country' => $address->country_name,
                            //         ],
                            //     ],
                            // ]);

                            // $ref = $checkout_session->id;
                            
                            $invoice_id = $user->id . uniqid() . time();
                            
                            
                            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                            $checkout_session = \Stripe\PaymentIntent::create([
                                'amount' => $price * 100,
                                'currency' => 'JOD',
                                'description' => "product purchase",
                                'shipping' => [
                                    'name' => $user->name ?? $user->first_name . ' ' . $user->last_name,
                                    'address' => [
                                        'line1' => $address->address ?? '',
                                        'city' => $address->city_name ?? '',
                                        'state' => $address->state_name ?? '',
                                        'country' => $address->country_name ?? '',
                                    ],
                                ],
                            ]);
                    
                            $data['session_id'] = $checkout_session->id;
                            $ref = $checkout_session->client_secret;
        
                            

                            DB::table('contracting')->where('id', $request->contract_id)
                                ->update(['transaction_id' => $invoice_id, 'payment_ref' => $ref]);

                            $payment_report = [
                                'transaction_id' => $invoice_id,
                                'payment_status' => 'P',
                                'user_id' => $user->id,
                                'ref_id' => $ref,
                                'amount' => $price,
                                'method_type' => 'card',
                                'created_at' => gmdate('Y-m-d H:i:s'),
                            ];
                            $status = "1";
                        }
                    }

                }else{
                    $message = "Invalid contract";
                }
            } else {
                $status  = "0";
                $message = "Session expired please login";
            }
        }

        return response()->json(['status' => $status, 'message' => $message, 'payment_report' => $payment_report ], 200);

    }

    function verify_contract_payment(Request $request)
    {
        $status  = "0";
        $message = "";

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'transaction_id' => 'required',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Fill all required fields";
            $errors = $validator->messages();
        } else {

            $users = User::where("user_access_token", $request->access_token)->first();
            if ( $users ) {
                if(Contracting::where('transaction_id',$request->transaction_id)
                    ->update(['status'=>2])){
                    $status = "1";
                    $message = "Successfully Paid Contract Quote";

                    $ontract = Contracting::where('transaction_id',$request->transaction_id)
                        ->first();

                    if ( ! $ontract ) {
                        $status = "0";
                        $message = "Invalid Transaction ID";
                    } else {

                        $title = 'Contract Quote Paid';
                        $description = "you have successfully paid contract quote";
                        $notification_id = time();
                        $ntype = 'quote_created';

                        $type = 1;

                        if (!empty($users->firebase_user_key)) {
                            $notification_data["Notifications/" . $users->firebase_user_key . "/" . $notification_id] = [
                                "title" => $title,
                                "description" => $description,
                                "notificationType" => $ntype,
                                "createdAt" => gmdate("d-m-Y H:i:s", $notification_id),
                                "orderId" => (string) $ontract->id,
                                "Type" => (string) $type,
                                "url" => "",
                                "imageURL" => '',
                                "read" => "0",
                                "seen" => "0",
                            ];
                            $this->database->getReference()->update($notification_data);
                        }

                        if (!empty($users->user_device_token)) {
                            send_single_notification(
                                $users->user_device_token,
                                [
                                    "title" => $title,
                                    "body" => $description,
                                    "icon" => 'myicon',
                                    "sound" => 'default',
                                    "click_action" => "EcomNotification"
                                ],
                                [
                                    "type" => $ntype,
                                    "notificationID" => $notification_id,
                                    "orderId" => (string) $ontract->id,
                                    "Type" => (string) $type,
                                    "imageURL" => "",
                                ]
                            );
                        }
                    }

                }else{
                    $message = "Invalid payment reference";
                }
            } else {
                $status  = "0";
                $message = "Session expired please login";
            }
        }

        return response()->json(['status' => $status, 'message' => $message ], 200);

    }
}