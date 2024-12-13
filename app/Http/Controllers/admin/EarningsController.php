<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ChefEditedImport;
use App\Models\Restaurants;
use Illuminate\Http\Request;
use App\Models\User as Users;
use \App\Models\VendorModel;
use \App\Models\OrderModel;
use \App\Models\Partner;
use \App\Models\Config;
use Illuminate\Support\Carbon;
use Validator;
use Auth,Hash,DB;
use App\Exports\ChefExport;
use App\Exports\ChefEditExport;
use App\Exports\ChefPendingApprovalExport;
use App\Exports\ReportChefEarnings;
use Excel;
use App\Libraries\FPDFExtended;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Exports\ExportReports;

class EarningsController extends Controller
{
    public function index(Request $request)
    {

        $page_title = 'Chef List';
        $available = $request->avail ==1 ? 1 :'';
        if($available == 1) {
            $page_title = 'Available Chef List';
        }
        return view('admin.chef.index', compact('page_title','available'));
    }

    public function export(Request $request) 
    {
        if (!check_permission('module_customer_view')) {

        }
        else 
            return Excel::download(new ChefExport, 'chef_'.date('Y_M_d_his').'.xlsx');


                    
    }

    public function export_edit(Request $request) 
    {
        set_time_limit(300);
        if (!check_permission('module_customer_view')) {

        }
        else 
            return Excel::download(new ChefEditExport, 'chef_edit_'.date('Y_M_d_his').'.xlsx');
            
    }


    public function import_edit(Request $request) 
    {
        // echo realpath('./chef_edit_2023_Nov_20_064707.xlsx');exit;
        set_time_limit(300);
        if (!check_permission('module_customer_view')) {

        }
        else 
             (new ChefEditedImport)->import('./chef_edit_2023_Nov_20_064707.xlsx');

        return "";
         
    }

    public function exportPdf(Request $request) {

        $pdf = new FPDFExtended();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 14);
        // Table with 20 rows and 4 columns
        $pdf->SetWidths(array(30, 50, 30, 40));
        for($i=0;$i<20;$i++)
            $pdf->Row(array($this->GenerateSentence(), $this->GenerateSentence(), $this->GenerateSentence(), $this->GenerateSentence()));
        $pdf->Output();

    }

    public function exportApprovals(Request $request) {
        // $data = Partner::with('country','city', 'province');
        // $data = $data->where('user_type', 1)->where('registration_status',0);
        // print_r($data->get())
        
        return Excel::download(new ChefPendingApprovalExport($request), 'chef_approval.xlsx');
    }
    

    public function getData(Request $request)
    {

        if (!check_permission('module_customer_view')) {
            $json_data = [
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
            ];

            echo json_encode($json_data);
            die();
        }
        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'created_at',
            3 => 'commission',
            4=>'cheforders_count',
            5=>'cancelledcheforder_count',
            6=>'cheforders_sum_grand_total',
            7=>'cheforders_sum_restaurant_commission_amount',
           // 8=>'chef_payment_sent_sum_restaurant_commission_amount',
            8=>'chef_payment_approved_sum_restaurant_commission_amount',
            9=>'chef_payment_requested_sum_restaurant_commission_amount',

            10=>'status',
            11=>'available',
            12=>'updated_at'
        ];
        $data = Users::where("user_type", 'chef')->withCount('cheforders')->withSum('cheforders','grand_total')->withSum('cheforders','restaurant_commission_amount')->withCount('cancelledcheforder')->withSum('chefPaymentSent','restaurant_commission_amount')->withSum('chefPaymentRequested','restaurant_commission_amount')->withSum('chefPaymentApproved','restaurant_commission_amount')->with(['ratings','restaurant'])->join('restaurants','users.id','restaurants.owner_uid')->whereIn('status',[1,0]);
        if (isset($request->name)) {
            $request->name = str_replace("'", "''", $request->name);
             $data->whereRaw("(users.name ilike '%" . $request->name . "%' or restaurants.name ilike '%" . $request->name . "%')");
        }
        if (isset($request->email)) {
            $data->whereRaw("(users.email ilike '%".$request->email."%')");
        }
        if (isset($request->available) && $request->available ==1) {
            $data->where('users.available', 1);
        }

        if (isset($request->phone_number)) {
            $data->where(DB::raw("concat(dial_code,'',phone_number)"),"like","%".$request->phone_number."%");
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
                ->get();  //  print_r($record);
        } else {
            $search = $request->input('search.value');
            $data->where('name', 'ILIKE', "%{$search}%");
            $record = $data->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $data->count();
        }

        $data = [];
        if (!empty($record)) {

            foreach ($record as $key => $row) {

                $address_list = route('admin.address_list', $row->id);
                $edit = route('admin.chef.edit', $row->id);
                $view = route('admin.chef.view', $row->id);
                $dishlist = route('admin.chef.dishlist', $row->id);
                $orderhistory = route('admin.sales_order', $row->id);
                $paymenthistory = route('admin.chef.earnings', $row->id);
                $available = $row->available == 1 ? '<span class="available"></span>' : '<span class="unavailable"></span>';
                if($row->status && $row->available == 1 ){
                    $available = '<span class="available"></span>';
                }else{
                    if(!$row->status && $row->available == 1 ){
                        $available = '<span class="unavailable"></span>';
                    }
                    else if($row->status && $row->available != 1 ){
                        $available = '<span class="unavailable"></span>';
                    }
                    else if(!$row->status && $row->available != 1 ){
                        $available = '<span class="unavailable"></span>';
                    }else{
                        $available = '<span class="available"></span>';
                    }
                }
                if($row->status == 0)
                {
                    $available = "";
                }

                $name =  '<div class="">
                            <div>
                                <div><a style="display: flex; justify-content: flex-start; gap: 5px; max-width: 250px; white-space: pre-wrap;" href="' . $view . '">' . $row->name.'-'.$row->restaurant->name .$available.' </a></div>
                                <div class="text-muted">' . $row->email . '</div>
                                <div><span>' . $row->dial_code . $row->phone_number . '</span></div> <div><span></div>
                            </div>
                        </div>';
                $checked = ($row->status) ? "checked" : "";
                $active = ($row->status) ? 1 : 0;

                $status = '<label class="custom-control custom-switch custom-switch-md">
                <input class="custom-control-input" type="checkbox" ' . $checked . ' data-role="active-switch" data-href="' . url('admin/customer_change_status/' . $row->id . '/' . $active) . '" />
                <span class="custom-control-label"></span>
            </label>';



                $action = '
            <ul class="" style="padding: 0;">
            <li class="nav-item dropdown user-profile-dropdown">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="flaticon-gear"></span>
                </a>
                <div class="dropdown-menu " aria-labelledby="userProfileDropdown">
                <a class="dropdown-item" title="View" href="' . $view . '">View</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" title="Edit" href="' . $edit . '">Edit</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" title="Menu" href="' . $dishlist . '">Menu</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" title="Order History" href="' . $orderhistory . '">Order History</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" title="Payments" href="' . $paymenthistory . '">Payments</a>
                <div class="dropdown-divider"></div>
                 <a class="dropdown-item" onclick="showDeleteModal(' . $row->id . ')" title="Remove"  href="javascript:;">Remove</a>
               </div>
                </li>
            </ul>';
                $counter = ($request->input('start')) ? ($request->input('start') + ($key + 1)) : ($key + 1);
                $nestedData['id'] = $counter;
                $nestedData['name'] = $name;
                $nestedData['available'] = $available;

                $nestedData['date_details'] = '<p>Reg: '.dateformat($row->created_at).'</p>
                ';

                if($row->license_expiry!=null && $row->license_expiry!='1970-01-01')
                    $nestedData['date_details'] .= '<p>Expiry: '.dateformat($row->license_expiry).'</p>';

                $nestedData['cash_point'] = ($row->points - $row->user_points) . ' (Earned:' . $row->points . '/Spent:' . $row->used_points . ')';
                $nestedData['status'] = $status;
                $nestedData['updated_at'] = date('j M Y', strtotime($row->updated_at));
                $nestedData['available'] = ($row->available) ? "Yes" : "No";
                $nestedData['commission'] = ($row->commission > 0 ) ? $row->commission : "Default";
                $nestedData['total_orders'] = $row->cheforders->count();
                $nestedData['total_order_amount'] = number_format($row->cheforders->sum('grand_total'),2);
                $nestedData['chef_commission'] = number_format($row->cheforders->sum('restaurant_commission_amount'),2);
                $nestedData['total_cancelled_orders'] = $row->cancelledcheforder->count();
                $nestedData['payment_sent'] = number_format($row->chef_payment_sent_sum_restaurant_commission_amount,2);
                $nestedData['payment_approved'] = number_format($row->chef_payment_approved_sum_restaurant_commission_amount,2);
                $nestedData['payment_requested'] = number_format($row->chef_payment_requested_sum_restaurant_commission_amount,2);
                $nestedData['publish_menu'] = $row->new_updation!=null ? "notiy_row" : "trw";
                $nestedData['action'] = $action; //"<a target='_blank' class='btn btn-primary' href='{$edit}' title='Address List' ><i class='fa fa-home'></i></a> ";
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

    public function pendingChef(Request $request)
    {
        $page_title = 'New Chef Approval';
        return view('admin.chef.apporoval_pending', compact('page_title'));
    }

    public function pendingChefData(Request $request)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        if (!check_permission('module_partnership_view')) {
            $json_data = [
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
            ];

            echo json_encode($json_data);
            die();
        }

        $columns = [
            0 => 'id',
            1 => 'owner_name',
            // 2 => 'owner_email',
            // 3 => 'owner_mobile',
            2 => 'location',
            3 => 'id',
            4 => 'preparation_time'
        ];

        $data = Partner::with('country','city', 'province')->where('emirates_id','!=',NULL);

         if (isset($request->owner_name)) {
            $data->where('owner_name', 'ilike', '%' . $request->owner_name . '%');
        }
        if (isset($request->restaurant_name)) {
            $data->whereRaw("(owner_name ilike '%" . $request->restaurant_name . "%')");
        }
        if (isset($request->city_id)) {
            $data->where('city_id', $request->city_id);
        }
        $data->where('user_type', 1)->where('registration_status', $request->registration_status);

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
                ->get(); //print_r($record);
        } else {
            $search = $request->input('search.value');


            $data->where('partner_registration.restaurant_name', 'ILIKE', "%{$search}%");

            $record = $data->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $data->count();
        }

        $data = []; 
        if (!empty($record)) {
            foreach ($record as $key => $row) {

                $edit = url('admin/edit_pending_chef', $row->id);
                $view = url('admin/requests/view', $row->id);
                $user = url('admin/requests/change_to_user', $row->id);

                $image = $row->image ? '<img class="img-sm rounded-circle mb-md-0 mr-2" src='.$row->image.' height="40" width="40" />':'';
                $owner =   '<div class="d-flex align-items-start">
                        <div class="mr-2"><div>'.$image.'</div></div>
                        <div>
                            <div>'.$row->owner_name.'<br>'.$row->owner_email.'<br>'.$row->owner_dial_code.$row->owner_mobile.'</div>
                        </div>
                    </div>
                    ' ;
                $logo = $row->logo ? '<img class="img-sm rounded-circle mb-md-0 mr-2" src='.$row->logo.' height="40" width="40" />':'';
                $name =   '<div class="d-flex align-items-start">
                        <div class="mr-2"><div>'.$logo.'</div></div>
                        <div>
                            <div>'.$row->restaurant_name.'<br>'.$row->owner_email.'<br>'.$row->owner_dial_code.' '.$row->owner_mobile. '</div>
                        </div>
                    </div>
                    ';



                $region = isset($row->province->name_en) ? $row->province->name_en.',<br>' : '';
                $region .= isset($row->city->city_name_en) ? $row->city->city_name_en.',<br>' : '';
                $region .= isset($row->country->country_name) ? $row->country->country_name : '';

                $counter = ($request->input('start')) ? ($request->input('start') + ($key + 1)) : ($key + 1);
                $nestedData['id'] = $counter; // print_r($row);
                // $nestedData['email'] = $row->owner_email;
                // $nestedData['mobile'] = $row->owner_dial_code.$row->owner_mobile;
                $docuements = "";
                if(!empty($row->emirates_id))
                {
                    $docuements .= '<div style=" display: flex; align-items: center; gap:5px; margin-bottom: 10px"><span style="width: 140px; display: block;">Emirates ID </span>';
                    $docuements .= '<a class="btn-viewdoc" data-fancybox data-src="'.public_url().$row->emirates_id.'" href="'.$row->emirates_id.'">View</a>';
                    $docuements .= '<a class="btn-downloaddoc" href="'.public_url().$row->emirates_id.'" target="_blank" download>Download</a>';
                    $docuements .= "</div>";

                }
                if(!empty($row->passport))
                {
                    $docuements .= '<div style=" display: flex; align-items: center; gap:5px; margin-bottom: 10px"><span style="width: 140px; display: block;">Passport ID</span>';                    
                    $docuements .= '<a class="btn-viewdoc" data-fancybox data-src="'.public_url().$row->passport.'" href="'.$row->passport.'">View</a>';
                    $docuements .= '<a class="btn-downloaddoc" href="'.public_url().$row->passport.'" target="_blank" download>Download</a> ';
                    $docuements .= '</div>';
                }
                if(!empty($row->license_doc))
                {
                  //  $docuements .= '<br/><a href="'.public_url().$row->license_doc.'" target="_blank" download><span class="tag-doc btn btn-primary btn-success mt-1"><i class="fa fa-download"></i> Trade License</span></a> <a data-fancybox data-src="'.public_url().$row->license_doc.'" href="'.$row->license_doc.'"><span class="tag-doc btn btn-primary btn-success mt-1"><i class="fa fa-eye"></i> Trade License</span></a>';
                    $docuements .= '<div style=" display: flex; align-items: center; gap:5px; margin-bottom: 10px"><span style="width: 140px; display: block;">Trade License</span>';
                    $docuements .= '<a class="btn-viewdoc" data-fancybox data-src="'.public_url().$row->license_doc.'" href="'.$row->license_doc.'">View</a>';
                    $docuements .= '<a class="btn-downloaddoc" href="'.public_url().$row->license_doc.'" target="_blank" download>Download</a> ';
                    $docuements .= '</div>';
                }

                if(!empty($row->bank_account_proof))
                {
                   // $docuements .= '<br/><a href="'.public_url().$row->bank_account_proof.'" target="_blank" download><span class="tag-doc btn btn-primary btn-success mt-1"><i class="fa fa-download"></i> Bank Account Proof</span></a> <a data-fancybox data-src="'.public_url().$row->bank_account_proof.'" href="'.$row->bank_account_proof.'"><span class="tag-doc btn btn-primary btn-success mt-1"><i class="fa fa-eye"></i> Bank Account Proof</span></a>';
                    $docuements .= '<div style=" display: flex; align-items: center; gap:5px; margin-bottom: 10px"><span style="width: 140px; display: block;">Bank Account Proof</span>';
                    $docuements .= '<a class="btn-viewdoc" data-fancybox data-src="'.public_url().$row->bank_account_proof.'" href="'.$row->bank_account_proof.'">View</a>';
                    $docuements .= '<a class="btn-downloaddoc" href="'.public_url().$row->bank_account_proof.'" target="_blank" download>Download</a> ';
                    $docuements .= '</div>';
                }
                if(!empty($row->visa_copy))
                {
                    //$docuements .= '<br/><a href="'.public_url().$row->visa_copy.'" target="_blank" download><span class="tag-doc btn btn-primary btn-success mt-1"><i class="fa fa-download"></i> Residency Visa</span></a> <a data-fancybox data-src="'.public_url().$row->visa_copy.'" href="'.$row->visa_copy.'"><span class="tag-doc btn btn-primary btn-success mt-1"><i class="fa fa-eye"></i> Residency Visa</span></a>';
                    $docuements .= '<div style=" display: flex; align-items: center; gap:5px;"><span style="width: 140px; display: block;">Residency Visa</span>';
                    $docuements .= '<a class="btn-viewdoc" data-fancybox data-src="'.public_url().$row->visa_copy.'" href="'.$row->visa_copy.'">View</a>';
                    $docuements .= '<a class="btn-downloaddoc" href="'.public_url().$row->visa_copy.'" target="_blank" download>Download</a> ';
                    $docuements .= '</div>';
                }



                $nestedData['details'] = $owner;
                $nestedData['documents'] = $docuements;
                $nestedData['license_expiry'] = $row->license_expiry;
                $nestedData['avg_time'] = $row->preparation_time." ".$row->preparation_unit.' - '.$row->preparation_time_to.$row->preparation_unit;

                $nestedData['region'] = (strlen($row->location)>20) ? substr($row->location, 0,20).'...':$row->location;//isset($row->province->name_en)?$row->province->name_en:'';
                // $nestedData['owner_email'] = $row->owner_email;
                // $nestedData['owner_mobile'] = $row->owner_dial_code.$row->owner_mobile;
                $nestedData['created_at'] = $row->created_at ? date('j M Y', strtotime($row->created_at)) : '';

                $actions = "<a  class='btn btn-orange mb-2' style='margin: 0 5px 10px; height: 26px; font-size: 10px !important; line-height: initial; border-radius: 5px !important; padding: 8px 10px !important;' href='{$view}' title='View' >View</a> <br>
                <a  class='btn btn-orange mb-2' style='margin: 0 5px 10px; height: 26px; font-size: 10px !important; line-height: initial; border-radius: 5px !important; padding: 8px 10px !important;' href='{$edit}' title='Edit' >Edit</a> <br>
                ";
                if($row->registration_status ==0) {
                    $actions .= "<a  class='btn btn-green mb-2' style='margin: 0 5px 10px; height: 26px; font-size: 10px !important; line-height: initial; border-radius: 5px !important; padding: 8px 10px !important;' href='javascript:void(0)'  onclick='showApproveModal(" . $row->id . ",1)' title='Approve' >Approve</a><br>
                    <a  class='btn btn-red' style='margin: 0 5px 0px; height: 26px; font-size: 10px !important; line-height: initial; border-radius: 5px !important; padding: 8px 10px !important;' href='javascript:void(0)'  onclick='showApproveModal(" . $row->id . ",2)' title='Reject' >Reject</a>";
                }
                 if($request->registration_status ==2) {
                    $actions.= "<a  class='btn btn-green mb-2' style='margin: 0 5px 0px; height: 26px; font-size: 10px !important; line-height: initial; border-radius: 5px !important; padding: 8px 10px !important;' href='javascript:void(0)' onclick='showApproveModal(" . $row->id . ",0)'  title='View' >Move To Pending Approval</a><br>
                ";
                }
                $nestedData['action'] = $actions;
                $data[] = $nestedData;
            }
        }
        
        $json_data = [
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data,
        ];   

        echo json_encode($json_data,JSON_INVALID_UTF8_IGNORE);
    }

    public function rejectedChef(Request $request)
    {
        $page_title = 'Rejected Chefs';
        return view('admin.chef.rejected_chef', compact('page_title'));
    }

    public function rejectedChefData(Request $request)
    {

        if (!check_permission('module_partnership_view')) {
            $json_data = [
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
            ];

            echo json_encode($json_data);
            die();
        }

        $columns = [
            0 => 'id',
            1 => 'restaurant_name',
            2 => 'owner_name',
            3 => 'city_id',
            4 => 'updated_at',
            5 => 'id'
        ];

        $data = Partner::with('country','city', 'province');

         if (isset($request->owner_name)) {
            $data->where('owner_name', 'ilike', '%' . $request->owner_name . '%');
        }
        if (isset($request->restaurant_name)) {
            $data->where('restaurant_name', $request->restaurant_name);
        }
        if (isset($request->city_id)) {
            $data->where('city_id', $request->city_id);
        }
        $data->where('user_type', 1)->where('registration_status', 2);

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


            $data->where('partner_registration.restaurant_name', 'ILIKE', "%{$search}%");

            $record = $data->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $data->count();
        }

        $data = [];
        if (!empty($record)) {
            foreach ($record as $key => $row) {

                $edit = url('admin/requests/view', $row->id);
                $user = url('admin/requests/change_to_user', $row->id);

                $image = $row->image ? '<img class="img-sm rounded-circle mb-md-0 mr-2" src='.public_url().$row->image.' height="40" width="40" />':'';
                $owner =   '<div class="d-flex align-items-start">
                        <div class="mr-2"><div>'.$image.'</div></div>
                        <div>
                            <div>'.$row->owner_name. '</div>
                        </div>
                    </div>
                    ' ;
                $logo = $row->logo ? '<img class="img-sm rounded-circle mb-md-0 mr-2" src='.public_url().$row->logo.' height="40" width="40" />':'';
                $name =   '<div class="d-flex align-items-start">
                        <div class="mr-2"><div>'.$logo.'</div></div>
                        <div>
                            <div>'.$row->restaurant_name.'<br>'.$row->owner_email.'<br>'.$row->owner_dial_code.' '.$row->owner_mobile. '</div>
                        </div>
                    </div>
                    ';



                $region = isset($row->province->name_en) ? $row->province->name_en.',<br>' : '';
                $region .= isset($row->city->city_name_en) ? $row->city->city_name_en.',<br>' : '';
                $region .= isset($row->country->country_name) ? $row->country->country_name : '';

                $counter = ($request->input('start')) ? ($request->input('start') + ($key + 1)) : ($key + 1);
                $nestedData['id'] = $counter;
                $nestedData['restaurant_name'] = $name;
                $nestedData['details'] = $owner;
                $nestedData['region'] = $region;//isset($row->province->name_en)?$row->province->name_en:'';
                // $nestedData['owner_email'] = $row->owner_email;
                // $nestedData['owner_mobile'] = $row->owner_dial_code.$row->owner_mobile;
                $nestedData['created_at'] = $row->created_at ? date('j M Y', strtotime($row->created_at)) : '';

                $nestedData['action'] = "<a  class='btn btn-primary' href='{$edit}' title='View' ><i class='fa fa-eye'></i></a>";

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

    public function edit(Request $request, $id)
    {
        $page_heading = "Edit Chef";
        $codes      = \App\Models\Country::orderBy('country_id', 'desc')->where('country_language_code', 1)->get();
        $countries      = \App\Models\Country::orderBy('country_id', 'desc')->where('country_id', 4)->where('country_language_code', 1)->get();
        $cities         = \App\Models\City::orderBy('id', 'desc')->get();

        $customer = Users::where("user_type", 'chef')->where('id', $id)->first();
        $restaurent = \App\Models\Restaurants::where('owner_uid',$id)->get()->first();

        $cuisine = \App\Models\Cuisine::where("active", 1)->get();
        $selectedCuisines = \App\Models\ChefCuisine::where('user_id',$id)->get('cuisine_id')->toArray() ;
        $selectedCuisines = array_column($selectedCuisines, 'cuisine_id');
        $config = Config::get()->first();

        $banks = \App\Models\Banks::orderBy('id', 'asc')->get();
        return view('admin.chef.edit', compact('page_heading', 'cuisine','selectedCuisines','customer','codes','countries','cities','restaurent','banks','config'));
    }
    public function create(Request $request)
    {
        $page_heading = "Add New Chef";
        $codes      = \App\Models\Country::orderBy('country_id', 'desc')->where('country_language_code', 1)->get();
        $countries      = \App\Models\Country::orderBy('country_id', 'desc')->where('country_id', 4)->where('country_language_code', 1)->get();
        $cities         = \App\Models\City::orderBy('id', 'desc')->get();

        $customer = new Users();
        $restaurent =  new \App\Models\Restaurants();
        $banks = \App\Models\Banks::orderBy('id', 'asc')->get();
        $cuisine = \App\Models\Cuisine::where("active", 1)->get();
        $config = Config::get()->first();
        $selectedCuisines = [];
        return view('admin.chef.edit', compact('page_heading', 'customer','selectedCuisines','cuisine','codes','countries','cities','restaurent','banks','config'));
    }
    public function view(Request $request, $id)
    {
        $page_heading = "View Chef";
        $customer = Users::where("user_type", 'chef')->with('bank')->where('id', $id)->first();
        if(!$customer){
            return redirect()->back();

        }
         $restaurent = \App\Models\Restaurants::where('owner_uid',$id)->get()->first();
        return view('admin.chef.view', compact('page_heading', 'customer','restaurent'));
    }

    public function save(Request $request)
    {
        $status = "0";
        $message = '';
        $errors = [];
        $rules = [
            'first_name' => 'required',
            // 'email' => 'required|email|unique:users,email,' . auth()->user()->id,
            'last_name' => 'required',
            'country_id' => '',
            'city_id' => '',
            'brand_name'=>'required',
            'start_time'=>'',
            'end_time'=>'',


            'license_no' => 'required_if:user_type,==,driver',
            'license_expiry' => 'required_if:user_type,==,driver',
            'vehicle_type' => 'required_if:user_type,==,driver',
            'vehicle_no' => 'required_if:user_type,==,driver',
            'vehicle_expiry' => 'required_if:user_type,==,driver',

            'bank_id' => '',
            'account_no' => '',
            'ifsc' => '',
            'swift' => '',
            'logo' => 'max:5120',
            'image' => 'max:5120',
        ];
        if($request->id ==null) {
            $rules['email'] = 'required|email';
        }

        $validator = Validator::make($request->all(), $rules, [
            'first_name.required' => 'First Name is required',
            'last_name.required' => 'Last Name is required',
            'email.required' => 'Email is required',
            'country_id.required' => 'Country is required',
            // 'providers_id.required' => 'outlet is required',
            'city_id.required' => 'City is required',
            'brand_name.required' => 'Brand Name is required',
            'license_no.required_if' => 'License Number is required',
            'license_expiry.required_if' => 'License expiry date is required',
            'vehicle_type.required_if' => 'Vehicle Type is required',
            'vehicle_no.required_if' => 'Vehicle Number is required',
            'vehicle_expiry.required_if' => 'Vehicle Expiry date is required',
            'bank_id.required' => 'Bank is required',
            'account_no.required' => 'Account Number is required',
            'ifsc.required' => 'IFSC Code is required',
            'swift.required' => 'Swift Code is required',
            'dial_code.required' => 'Dial Code is required',
            'phone_number.required' => 'Mobile Number is required',
            'image.image' => 'should be in image format (.jpg,.jpeg,.png)',
            'end_time'          => 'Must be grater than start time',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = $validator->messages()->first();
            foreach ($validator->messages()->toArray() as $key => $row) {
                $errors[0][$key] = $row[0];
            }
        } else {
                if($request->id ==null) {
                    $checkUserExist = \App\Models\Users::where('email',$request->email)->where('user_type','chef')->count();
                    if($checkUserExist > 0 ) {
                        $status = "0";
                        $message = "";
                        $errors[0]['email'] = "Email should be unique";
                        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
                    }
                }
                if(strtotime($request->start_time) >= strtotime($request->end_time)) { 
                    $status = "0";
                    $message = "";
                    $errors[0]['end_time'] = "End time should be greater than start time";
                } else { 
                    if($request->id > 0 ) { 
                    $uData = \App\Models\Users::find($request->id);
                    if($uData->weekly_order_mode !=(int)$request->weekly_order_mode || $uData->start_time!= date("H:i:s", strtotime($request->start_time)) || $uData->end_time != date("H:i:s", strtotime($request->end_time)) ) { 
                        $uData->weekly_order_mode = (int)$request->weekly_order_mode;
                        $uData->save();
                        $days = Config('global.days') ;
                        $farray = [];
                        $farray['weekly_mode'] = (int)$request->weekly_order_mode;
                        foreach($days as $key =>$val){
                            $farray[$val] = 1;
                            $farray[$key.'_from'] = date("H:i:s", strtotime($request->start_time));
                            $farray[$key.'_to'] = date("H:i:s", strtotime($request->end_time));               
                            
                        }
                        \App\Models\FoodItem::where('chef_id',$uData->id)->update($farray);
                        

                    }
                    }
                    $res = \App\Models\users::saveChef($request->id,$request);
                    if($res ) {
                        $message = 'Chef Saved Successfully';
                        $status = "1";
                    } else {
                        $status = "0";
                        $message = "Error occured, please try again";
                    }

                }
        }

        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }

    public function dishList(Request $request)
    {
        $chef_id  = $request->id;

        $restaurent = \App\Models\Restaurants::where('owner_uid', $chef_id)->get()->first();

        if($restaurent ) {
            $page_title = 'My Dishes - '. $restaurent->name;
            
            return view('admin.chef.dishlist', compact('page_title','chef_id'));
        }
    }

     public function getDishesData(Request $request)
    {
        if (!check_permission('module_food_item_view')) {
            $json_data = [
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
            ];

            echo json_encode($json_data);
            die();
        }
        $columns = [
            0 => 'restaurant_id',
            1 => 'name',
            2 =>'chef_id',
            3 => 'is_publish',
            4  =>'status',
            5 =>'created_at'
        ];

        $data = \App\Models\FoodItem::with(['chef'])->where('deleted', 0);

        if (isset($request->name)) {
            $data->where('food_items.name', 'ilike', '%' . $request->name . '%');
        }
        if (isset($request->chef_id)) {
            $data->where('food_items.chef_id', $request->chef_id);
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


            $data->where('food_items.name', 'ILIKE', "%{$search}%");

            $record = $data->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $data->count();
        }

        $data = [];
        if (!empty($record)) {
            foreach ($record as $key => $row) {

                $nestedData['id'] = '<div class="expend"></div>
                 <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input record_row"  data-record-id="' . $row->id . '" id="check-' . $row->id . '">
                    <label class="custom-control-label" for="check-' . $row->id . '">&nbsp;</label>
                 </div>';

                $edit = check_permission('module_food_item_add') ? url('admin/food_item/edit', $row->id) : '#' ;
                $delete = check_permission('module_food_item_delete') ?  url('admin/food_item/delete', $row->id) : '#' ;
                $action = '
                 <ul class="">
                 <li class="nav-item dropdown user-profile-dropdown">
                     <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                       <span class="flaticon-gear"></span>
                     </a>
                     <div class="dropdown-menu " aria-labelledby="userProfileDropdown">
                     <a class="dropdown-item" title="Edit" href="' . $edit . '">Edit</a>
                     <div class="dropdown-divider"></div>
                     <a class="dropdown-item" onclick="showDeleteModal(' . $row->id . ')" title="Delete"  href="javascript:;">Remove</a>

                    </div>
                     </li>
                 </ul>';

                 $details =   '<div class="d-flex align-items-start">

                        <div>
                            <div>'.$row->name.'<br><div class="text-muted"><small><i class="mdi mdi-book-open-variant"></i> </small></div></div>
                        </div>
                    </div>
                    ';
                $res_view =  $row->restaurant ? route('admin.restaurants.view',  $row->restaurant->id) : '';
                $restaurant =  $row->chef ? '<div class="d-flex align-items-start">
                        <div class="mr-2"><div><img class="img-sm rounded-circle mb-md-0 mr-2" src='.$row->chef->image.' height="40" width="40" /></div></div>
                        <div>
                            <div> <a class="dropdown-item" title="View" href="' . $res_view . '">'.$row->chef->name.' &nbsp;<i class="flaticon-view-1 bs-tooltip" data-placement="top" data-original-title="View"></i></a></div>
                        </div>
                    </div>
                    ' : '';


                $counter = ($request->input('start')) ? ($request->input('start') + ($key + 1)) : ($key + 1);
                $nestedData['id'] = $counter;
                $nestedData['name'] = $details;
                $nestedData['regular_price'] = $row->regular_price;
                $nestedData['sale_price'] = $row->sale_price;
                $nestedData['description'] = substr($row->description,0,25);
                $nestedData['sufficient_for'] = $row->sufficient_for;
                $stock = $row->out_of_stock ? 'checked' : '';
                $nestedData['out_of_stock'] = '<label class="custom-control custom-switch custom-switch-md">
                                                        <input class="custom-control-input" type="checkbox" ' . $stock . ' data-role="active-switch" data-href="' . route('admin.food_items.changeStockStatus', ['id' => $row->id]) . '" />
                                                    <span class="custom-control-label"></span>
                                                </label>
                                        ';
                $nestedData['quantity'] = $row->quantity;
                $nestedData['restaurant'] = $restaurant;
                $nestedData['menu'] = $row->menu->name??'';
                // $nestedData['active'] = ($row->active) ? 'Active' : 'Inactive';
                $status   = $row->active ? "checked" : "";
                $nestedData['active'] ='<label class="custom-control custom-switch custom-switch-md">
                                                        <input class="custom-control-input" type="checkbox" ' . $status . ' data-role="active-switch" data-href="' . route('admin.food_items.toggle_status', ['id' => $row->id]) . '" />
                                                    <span class="custom-control-label"></span>
                                                </label>
                                        ';
                $nestedData['updated_at'] = date('j M Y', strtotime($row->updated_at));
                $publish   = $row->is_publish ? "checked" : "";
                $nestedData['publish'] = '<label class="custom-control custom-switch custom-switch-md">
                                                        <input class="custom-control-input" type="checkbox" ' . $publish . ' data-role="active-switch" data-href="' . route('admin.food_items.publish', ['id' => $row->id]) . '" />
                                                    <span class="custom-control-label"></span>
                                                </label>
                                        ';
                $nestedData['publish_menu'] = $row->is_publish ? "trw" : "notiy_row";
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
    public function payments(Request $request)
    {
        $page_heading = "E-Commerce Earnings";
        $chef_id = $request->id;
        $chefDetails = Users::find($chef_id);
        $payStatus = [];
        $total = 0;
        $details = [];
        $orders = [];
        $total_vendor_commission = 0;
        $vendor_commission_approved = 0;
        $cod_commission = 0;
        $cod_amount = 0;
        if($chefDetails) {
            $orders = \App\Models\OrderModel::select([DB::raw('sum(total)'),DB::raw('count(order_id)')])
            // ->where('chef_id',$request->id)
            ->whereHas('products', function($q) use($request){
                return $q->where('vendor_id',$request->id);
            })
            ->where('status',config('global.order_status_delivered'))
            // ->leftjoin('order_products','order_products.order_id','orders.order_id')
            ->first();

            $details = \App\Models\OrderModel::select(['withdraw_status',DB::raw('sum(vendor_commission)')])->where('status',config('global.order_status_delivered'))
            ->whereHas('products', function($q) use($request){
                return $q->where('vendor_id',$request->id);
            })->groupBy('withdraw_status')->get();

            foreach ($details as $key => $value) {
                $payStatus[$value->withdraw_status] = $value->sum;
                $total += $value->sum;
            }

            $total_vendor_commission = OrderModel::where('status',4)->where('vendor_id',$request->id)->get()->sum('vendor_commission_per');  
            $vendor_commission_approved = OrderModel::where('status',4)->where('vendor_id',$request->id)->where('withdraw_status',3)->get()->sum('vendor_commission_per');  
            $cod_commission = OrderModel::where('status',4)->where('vendor_id',$request->id)->where('payment_mode',5)->where('withdraw_status',3)->get()->sum('admin_commission_per');  
            $cod_amount = OrderModel::where('status',4)->where('vendor_id',$request->id)->where('payment_mode',5)->where('withdraw_status',3)->get()->sum('vendor_commission_per');  
            
        }
        $sellers = VendorModel::select('users.id', 'vendor_details.company_name as name')->where(['role'=>'3','users.verified'=>'1','users.deleted'=>'0','users.deleted'=>'0'])
        ->leftjoin('vendor_details','vendor_details.user_id','=','users.id');
        // ->get();

        if(request()->activity_id){
            $sellers->where('activity_id',request()->activity_id);
        }else{
            $sellers->whereIn('activity_id',[7,5,3]);
        }
        $sellers = $sellers->get();

        if($request->export != 1){
            return view('admin.chef.payment', compact('page_heading','total','payStatus','chef_id','chefDetails','orders','sellers','total_vendor_commission','vendor_commission_approved','cod_commission','cod_amount'));
        }else{
            $data = \App\Models\OrderModel::
                where('status',config('global.order_status_delivered'))->with('users')
                ->whereNotNull('withdraw_status');

            if(isset($request->order_number)) {
                $data = $data ->where('order_no',$request->order_number);
            }
            if(isset($request->payment_mode)) {
                $data = $data ->where('payment_mode',$request->payment_mode);
            }
            if($request->chef_id) {
                $data = $data->where('vendor_id',$request->chef_id);
            }
            if(isset($request->request_date)) {
                $data = $data ->whereDate('created_at',Carbon::parse($request->request_date)->toDate());
            }
            if(isset($request->from_date)) {
                $data = $data ->whereDate('created_at','>=',Carbon::parse($request->from_date)->toDate());
            }
            if(isset($request->to_date)) {
                $data = $data ->whereDate('created_at','<=',Carbon::parse($request->to_date)->toDate());
            }
            if(isset($request->request_status)) {
                $data = $data ->where('withdraw_status',$request->request_status);
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
                        $admin_share = $val->admin_commission ;
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
            2 => 'order_id',
            3 => 'user_id',
            4 => 'vendor_id',
            5 => 'grand_total',
            6 => 'admin_commission_per',
            7 => 'vendor_commission_per',
            8 => 'payment_mode',
            9 => 'status',
            10 => 'created_at',
            11 => 'created_at',
        ];
        OrderModel::whereNull('withdraw_status')->update(['withdraw_status'=>0]);
        $data = \App\Models\OrderModel::
            where('status',config('global.order_status_delivered'))->with('users');
            //->whereNotNull('withdraw_status');

        if(isset($request->order_number)) {
            $data = $data ->where('order_no',$request->order_number);
        }
        if(isset($request->payment_mode)) {
            $data = $data ->where('payment_mode',$request->payment_mode);
        }
        if($request->chef_id) {
            $data = $data->where('vendor_id',$request->chef_id);
        }
        if(isset($request->request_date)) {
            $data = $data ->whereDate('created_at',Carbon::parse($request->request_date)->toDate());
        }
        if(isset($request->from_date)) {
            $data = $data ->whereDate('created_at','>=',Carbon::parse($request->from_date)->toDate());
        }
        if(isset($request->to_date)) {
            $data = $data ->whereDate('created_at','<=',Carbon::parse($request->to_date)->toDate());
        }
        if(isset($request->request_status)) {
            $data = $data ->where('withdraw_status',$request->request_status);
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
                $nestedData['checkbox'] = '<input type="checkbox" name="order_ids[]" value="' . $row->order_id . '" class="sub_chk" onclick="checkClick(this)" />';
                if($row->withdraw_status ==4 || $row->withdraw_status==3){
                    $nestedData['checkbox'] = '';
                }
                $nestedData['id'] = $counter;
                $nestedData['order_id'] = $row->order_no;
                $nestedData['customer'] = $row->users->name;

                $nestedData['vendor'] = $row->vendor && $row->vendor->vendordata ? $row->vendor->vendordata->company_name: '';
                $withdraw_status = Config('global.withdraw_status');
                $nestedData['status'] = $withdraw_status[(int)$row->withdraw_status];
                $nestedData['commission'] = $row->vendor_commission_per;

                $nestedData['admin_commission_per'] = $row->admin_commission_per;    
                $nestedData['payment_mode'] = payment_mode($row->payment_mode);  

                $nestedData['grand_total'] = $row->grand_total;
                $nestedData['created_at'] = get_date_in_timezone($row->created_at, 'd-M-y h:i A');

                   $action = '
                        <a class="btn btn-primary view_orderdetail" href="'.url('admin/order_details',$row->order_id).'" orderid="'.$row->order_id.'" title="Customer View" >View</a>
                   ';
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

    public function rejectChefChanges(Request $request)
    {
        $chef = Users::where('id', $request->chef_id)->first();
        if ($chef) {
            $chef->new_updation       = '';
            if ($chef->save()) {
                exec("php ".base_path()."/artisan read:notification chef-edited ".$chef->id." > /dev/null 2>&1 & ");
                return response()->json(['status' => 1, 'message' => 'Status has been updated.']);
            }
        }
        return response()->json(['status' => 0, 'message' => 'Unable to update status.']);
    }

     public function remove(Request $request)
    {
        $status = "0" ; $message = "";
        if (!check_permission('module_food_item_delete')) {
            $status = "0";
            $message = "You don't have permission to do this action";
            echo json_encode(['status' => $status, 'message' => $message]);
            die();
        }
        $checkOrderExist = \App\Models\OrderModel::where('chef_id',$request->id)->first();
        $checkFoodExist = \App\Models\FoodItem::where('chef_id',$request->id)->first();
        if($checkOrderExist == null && $checkFoodExist==null) {
            \App\Models\Users::where('id', $request->id)->update(['status' => 5]);
            $status = "1";
            $message = "Record removed successfully";
        } else {
            $message = "Unable to delete as order or food exist ";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    public function remove_newfiles(Request $request)
    {
        $status = "0";
        $message = "";
        $chef = Users::where('id', $request->chef_id)->first();
        if ($chef) {
            $updation = (array)json_decode($chef->new_updation);
            unset($updation[$request->ftype]);
            $chef->new_updation = json_encode($updation);
            $chef->save();
            $status = "1";
            $message = "Done";

        }
        return response()->json(['status' => $status, 'message' => $message]);
    }

    public function get_chef_dining_times(){
        $chefs = new Chef();
    }
    public function get_chef_ear(){
        $page_title = 'Chef Earnings';
        $available = '';
        return view('admin.chef.ear_list', compact('page_title','available'));
    }

    public function exportChefEarningsXLSXReport(Request $request) {

        return Excel::download(new ReportChefEarnings($request), 'chef_earnings.xlsx');
    }

    public function exportChefEarningsCSVReport(Request $request) {
        return Excel::download(new ReportChefEarnings($request), 'chef_earnings.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportChefEarningsPdfReport(Request $request) {
        $users = Users::select(
                    "users.name as name",
                    "restaurants.name as rname",
                    "restaurants.brand_name",
                    "zip","country.title_name as country_name", "city_name_en",
                    "restaurants.street", "restaurants.street2", "description_en",
                    "sun_from","sun_to","mon_from", "mon_to", "tues_from", "tues_to", "wed_from", "wed_to", "thurs_from","thurs_to", "fri_from", "fri_to", "sat_from", "sat_to",
                    "preparation_unit",
                    "preparation_time",
                    "preparation_unit_to",
                    "users.license_expiry",
                    "users.id",
                    "users.location",
            
                    DB::raw(" (dial_code || phone_number) as phone "), 
                    "email", "location", "restaurants.created_at",
                    DB::raw("(CASE WHEN gender='1' THEN 'Male' WHEN gender='2' THEN 'Female' WHEN gender is null THEN '' ELSE '' END) as gender"),
                    "banks.name_en as bank_name",
                    "users.account_no as account_no",
                    "users.ifsc as iban",
                    "users.swift as swift",
                    DB::raw('(select sum(restaurant_commission_amount) as d_earning_total from orders where chef_id=users.id and order_status='.config('global.order_status_delivered').')'),
                    DB::raw('(select sum(restaurant_commission_amount) as requested_for_payment from orders where chef_id=users.id and withdraw_status=0 and order_status='.config('global.order_status_delivered').')'),
                    DB::raw('(select sum(restaurant_commission_amount) as request_sent from orders where chef_id=users.id and withdraw_status=1 and order_status='.config('global.order_status_delivered').')'),
                    DB::raw('(select sum(restaurant_commission_amount) as payment_received from orders where chef_id=users.id and withdraw_status=2 and order_status='.config('global.order_status_delivered').')'),
                    DB::raw('(select sum(restaurant_commission_amount) as payment_approved from orders where chef_id=users.id and withdraw_status=3 and order_status='.config('global.order_status_delivered').')'),
                    DB::raw('(select sum(restaurant_commission_amount) as payment_rejected from orders where chef_id=users.id and withdraw_status=4 and order_status='.config('global.order_status_delivered').')'),
                    )->where("user_type", 'chef')
                    ->leftJoin("restaurants", "restaurants.owner_uid", "=", "users.id")
                    ->leftJoin("country","country.country_id", "=", "restaurants.country_id")
                    ->leftJoin("banks","banks.id", "=", "users.bank_id")
                    ->leftJoin("city","city.id", "=", "restaurants.city_id")
                    ->whereIn('users.status',[1,0])
                    ->orderBy('created_at','DESC');

        if (isset($this->request->name)) {
            $users = $users->where('name', 'ilike', '%' . $this->request->name . '%');
        }
        if (isset($this->request->email)) {
            $users =  $users->where('email', $this->request->email);
        }

        if (isset($this->request->phone_number)) {
            $users = $users->where(DB::raw("concat(dial_code,'',phone_number)"),"like","%".$this->request->phone_number."%");
        }

        $earnings_report_data =  $users->get();

        $pdf = new FPDFExtended();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);
        // Table with 20 rows and 4 columns
        $pdf->SetWidths(array(10, 40, 30, 30, 25, 25, 25));
        $pdf->Row(array("S.No", "Name", "Total Earnings","Requested Payment", "Payment Received","Payment Approved", "Payment Declined"));
        // $pdf->Output('D', "twat.pdf");
        foreach ($earnings_report_data as $key => $data) {


            // return [
            //     $user->brand_name,
            //     $user->name,
            //     " ".$user->phone,
            //     $user->email,
            //     date("d-m-Y", strtotime($user->created_at)),
            //     (!empty($user->license_expiry)) ? date("d-m-Y", strtotime($user->license_expiry)) : '',
            //     // $user->street.PHP_EOL. $user->street2.PHP_EOL. $user->city_name_en.PHP_EOL. $user->country_name.PHP_EOL. $user->zip ,
            //     $user->location,
            //     $user->d_earning_total,
            //     $user->requested_for_payment,
            //     $user->request_sent,
            //     $user->payment_received,
            //     $user->payment_approved,
            //     $user->payment_rejected,
              
            //     $user->bank_name,
            //     " ".$user->account_no,
            //     $user->iban,
            //     $user->swift,
            // ];
           

            $pdf->Row(array(($key + 1), 
                        $data->name, 
                        $data->d_earning_total, 
                        $data->requested_for_payment, 
                        $data->payment_received, 
                                   
                        $data->payment_approved, 
                        $data->payment_rejected));


            
        }

        $pdf->Output('D', "chef_eranings_report.pdf");
    }


    public function get_chef_wise_total_earnings(REQUEST $request){

        if (!check_permission('module_customer_view')) {
            $json_data = [
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
            ];

            echo json_encode($json_data);
            die();
        }
        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'd_earning_total',
            3 => 'requested_for_payment',
            4=>'request_sent',
            5=>'payment_received',
            6=>'payment_approved',
            7=>'payment_rejected',
            8=>'payment_approved',
            9=>'payment_requested'
        ];
        $data = Users::where("user_type", 'chef')->select('*',
            DB::raw('(select sum(restaurant_commission_amount) as d_earning_total from orders where chef_id=users.id and order_status='.config('global.order_status_delivered').')'),
            DB::raw('(select sum(restaurant_commission_amount) as requested_for_payment from orders where chef_id=users.id and withdraw_status=0 and order_status='.config('global.order_status_delivered').')'),
            DB::raw('(select sum(restaurant_commission_amount) as request_sent from orders where chef_id=users.id and withdraw_status=1 and order_status='.config('global.order_status_delivered').')'),
            DB::raw('(select sum(restaurant_commission_amount) as payment_received from orders where chef_id=users.id and withdraw_status=2 and order_status='.config('global.order_status_delivered').')'),
            DB::raw('(select sum(restaurant_commission_amount) as payment_approved from orders where chef_id=users.id and withdraw_status=3 and order_status='.config('global.order_status_delivered').')'),
            DB::raw('(select sum(restaurant_commission_amount) as payment_rejected from orders where chef_id=users.id and withdraw_status=4 and order_status='.config('global.order_status_delivered').')')
        )->whereIn('status',[1,0]);
        if (isset($request->name)) {
             $data->where('name', 'ilike', '%' . $request->name . '%');
        }
        if (isset($request->email)) {
            $data->where('email', $request->email);
        }

        if (isset($request->phone_number)) {
            $data->where(DB::raw("concat(dial_code,'',phone_number)"),"like","%".$request->phone_number."%");
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
                ->get();  //  print_r($record);
        } else {
            $search = $request->input('search.value');
            $data->where('name', 'ILIKE', "%{$search}%");
            $record = $data->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $data->count();
        }

        $data = [];
        if (!empty($record)) {

            foreach ($record as $key => $row) {

                $address_list = route('admin.address_list', $row->id);
                $edit = route('admin.chef.edit', $row->id);
                $view = route('admin.chef.view', $row->id);
                $dishlist = route('admin.chef.dishlist', $row->id);
                $orderhistory = route('admin.sales_order', $row->id);
                $paymenthistory = route('admin.chef.earnings', $row->id);
                $available = $row->available == 1 ? '<span class="available"></span>' : '<span class="unavailable"></span>';
                if($row->status && $row->available == 1 ){
                    $available = '<span class="available"></span>';
                }else{
                    if(!$row->status && $row->available == 1 ){
                        $available = '<span class="unavailable"></span>';
                    }
                    else if($row->status && $row->available != 1 ){
                        $available = '<span class="unavailable"></span>';
                    }
                    else if(!$row->status && $row->available != 1 ){
                        $available = '<span class="unavailable"></span>';
                    }else{
                        $available = '<span class="available"></span>';
                    }
                }
                if($row->status == 0)
                {
                    $available = "";
                }

                $name =  '<div class="">
                            <div>
                                <div><a style="display: flex; justify-content: flex-start; gap: 5px; max-width: 250px; white-space: pre-wrap;" href="' . $view . '">' . $row->name .$available.' </a></div>
                                <div class="text-muted">' . $row->email . '</div>
                                <div><span>' . $row->dial_code . $row->phone_number . '</span></div> <div><span></div>
                            </div>
                        </div>';
                $checked = ($row->status) ? "checked" : "";
                $active = ($row->status) ? 1 : 0;

                $status = '<label class="custom-control custom-switch custom-switch-md">
                <input class="custom-control-input" type="checkbox" ' . $checked . ' data-role="active-switch" data-href="' . url('admin/customer_change_status/' . $row->id . '/' . $active) . '" />
                <span class="custom-control-label"></span>
            </label>';



                $action = "<a  class='btn btn-primary' href='".$paymenthistory."'><span class='flaticon-gear'></span></a>";/* '
            <ul class="" style="padding: 0;">
            <li class="nav-item dropdown user-profile-dropdown">
            <a class="dropdown-item" title="Payments" href="' . $paymenthistory . '"> <span class="flaticon-gear"></span></a>
            </li>
                
            </ul>';*/
                $counter = ($request->input('start')) ? ($request->input('start') + ($key + 1)) : ($key + 1);
                $nestedData['id'] = $counter;
                $nestedData['name'] = $name;
                $nestedData['d_earning_total'] = $row->d_earning_total??0;
                $nestedData['requested_for_payment'] = $row->requested_for_payment??0;
                $nestedData['request_sent'] = $row->request_sent;
                $nestedData['payment_received'] = $row->payment_received??0;
                $nestedData['payment_approved'] = $row->payment_approved??0;
                $nestedData['payment_rejected'] = $row->payment_rejected??0;
                $nestedData['payment_approved'] = $row->payment_approved??0;
                $nestedData['payment_requested'] = $row->payment_requested??0;
                
                $nestedData['action'] = $action; //"<a target='_blank' class='btn btn-primary' href='{$edit}' title='Address List' ><i class='fa fa-home'></i></a> ";
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

    //     $list = Users::where("user_type", 'chef')->select('*',
    //     DB::raw('(select sum(chef_earning) as d_earning_total from orders where chef_id=users.id and order_status='.config('global.order_status_delivered').')'),
    //     DB::raw('(select sum(chef_earning) as requested_for_payment from orders where chef_id=users.id and withdraw_status=0 and order_status='.config('global.order_status_delivered').')'),
    //     DB::raw('(select sum(chef_earning) as request_sent from orders where chef_id=users.id and withdraw_status=1 and order_status='.config('global.order_status_delivered').')'),
    //     DB::raw('(select sum(chef_earning) as payment_received from orders where chef_id=users.id and withdraw_status=2 and order_status='.config('global.order_status_delivered').')'),
    //     DB::raw('(select sum(chef_earning) as payment_approved from orders where chef_id=users.id and withdraw_status=3 and order_status='.config('global.order_status_delivered').')'),
    //     DB::raw('(select sum(chef_earning) as payment_rejected from orders where chef_id=users.id and withdraw_status=4 and order_status='.config('global.order_status_delivered').')')
    // );
    //     echo $list->toSql();
    //     echo '<pre>';
    //    print_r($list->orderBy('id','desc')->get()->toArray());
    }

    public function recurring_chef(Request $request)
    {

        $page_title = 'Recurring Chef List';
        $available = $request->avail ==1 ? 1 :'';
        if($available == 1) {
            $page_title = 'Available Chef List';
        }
        return view('admin.chef.recurring', compact('page_title','available'));
    }

    public function getRecurringData(Request $request)
    {

        if (!check_permission('module_customer_view')) {
            $json_data = [
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
            ];

            echo json_encode($json_data);
            die();
        }
        $columns = [
            0 => 'id',
            1 => 'name',
           // 2 => 'created_at',
            2 => 'commission',
            3=>'cheforders_count',
            4=>'cancelledcheforder_count',
            5=>'cheforders_sum_grand_total',
            6=>'cheforders_sum_restaurant_commission_amount',
            7=>'chef_payment_sent_sum_restaurant_commission_amount',
            8=>'chef_payment_approved_sum_restaurant_commission_amount',
            9=>'chef_payment_requested_sum_restaurant_commission_amount',

            10=>'status',
            11=>'available',
            12=>'updated_at'
        ];
        $data = Users::where("user_type", 'chef')->withCount('cheforders')->withSum('cheforders','grand_total')->withSum('cheforders','restaurant_commission_amount')->withCount('cancelledcheforder')->withSum('chefPaymentSent','restaurant_commission_amount')->withSum('chefPaymentRequested','restaurant_commission_amount')->withSum('chefPaymentApproved','restaurant_commission_amount')->with(['ratings','restaurant'])->whereIn('users.status',[1,0])->join('orders','orders.chef_id','users.id')->groupBy('users.id')->whereDate('orders.created_at','>=',date('Y-m-d',strtotime("-2 months")))->has('cheforders','>',1);
        if (isset($request->name)) {
             $data->where('name', 'ilike', '%' . $request->name . '%');
        }
        if (isset($request->email)) {
            $data->where('email', $request->email);
        }
        if (isset($request->available) && $request->available ==1) {
            $data->where('available', 1);
        }

        if (isset($request->phone_number)) {
            $data->where(DB::raw("concat(dial_code,'',phone_number)"),"like","%".$request->phone_number."%");
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
               // ->orderBy('users.id','desc')
                ->orderBy($order, $dir)

                ->get();  //  print_r($record);
        } else {
            $search = $request->input('search.value');
            $data->where('name', 'ILIKE', "%{$search}%");
            $record = $data->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $data->count();
        }

        $data = [];
        if (!empty($record)) {

            foreach ($record as $key => $row) {

                $address_list = route('admin.address_list', $row->id);
                $edit = route('admin.chef.edit', $row->id);
                $view = route('admin.chef.view', $row->id);
                $dishlist = route('admin.chef.dishlist', $row->id);
                $orderhistory = route('admin.sales_order', $row->id);
                $paymenthistory = route('admin.chef.earnings', $row->id);
                $available = $row->available == 1 ? '<span class="available"></span>' : '<span class="unavailable"></span>';
                if($row->status && $row->available == 1 ){
                    $available = '<span class="available"></span>';
                }else{
                    if(!$row->status && $row->available == 1 ){
                        $available = '<span class="unavailable"></span>';
                    }
                    else if($row->status && $row->available != 1 ){
                        $available = '<span class="unavailable"></span>';
                    }
                    else if(!$row->status && $row->available != 1 ){
                        $available = '<span class="unavailable"></span>';
                    }else{
                        $available = '<span class="available"></span>';
                    }
                }
                if($row->status == 0)
                {
                    $available = "";
                }

                $name =  '<div class="">
                            <div>
                                <div><a style="display: flex; justify-content: flex-start; gap: 5px; max-width: 250px; white-space: pre-wrap;" href="' . $view . '">' . $row->name.'-'.$row->restaurant->name .$available.' </a></div>
                                <div class="text-muted">' . $row->email . '</div>
                                <div><span>' . $row->dial_code . $row->phone_number . '</span></div> <div><span></div>
                            </div>
                        </div>';
                $checked = ($row->status) ? "checked" : "";
                $active = ($row->status) ? 1 : 0;

                $status = '<label class="custom-control custom-switch custom-switch-md">
                <input class="custom-control-input" type="checkbox" ' . $checked . ' data-role="active-switch" data-href="' . url('admin/customer_change_status/' . $row->id . '/' . $active) . '" />
                <span class="custom-control-label"></span>
            </label>';



                $action = '
            <ul class="" style="padding: 0;">
            <li class="nav-item dropdown user-profile-dropdown">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="flaticon-gear"></span>
                </a>
                <div class="dropdown-menu " aria-labelledby="userProfileDropdown">
                <a class="dropdown-item" title="View" href="' . $view . '">View</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" title="Edit" href="' . $edit . '">Edit</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" title="Menu" href="' . $dishlist . '">Menu</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" title="Order History" href="' . $orderhistory . '">Order History</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" title="Payments" href="' . $paymenthistory . '">Payments</a>
                <div class="dropdown-divider"></div>
                 <a class="dropdown-item" onclick="showDeleteModal(' . $row->id . ')" title="Remove"  href="javascript:;">Remove</a>
               </div>
                </li>
            </ul>';
                $counter = ($request->input('start')) ? ($request->input('start') + ($key + 1)) : ($key + 1);
                $nestedData['id'] = $counter;
                $nestedData['name'] = $name;
                $nestedData['available'] = $available;

                $nestedData['date_details'] = '<p>Reg: '.dateformat($row->created_at).'</p>
                ';

                if($row->license_expiry!=null && $row->license_expiry!='1970-01-01')
                    $nestedData['date_details'] .= '<p>Expiry: '.dateformat($row->license_expiry).'</p>';

                $nestedData['cash_point'] = ($row->points - $row->user_points) . ' (Earned:' . $row->points . '/Spent:' . $row->used_points . ')';
                $nestedData['status'] = $status;
                $nestedData['updated_at'] = date('j M Y', strtotime($row->updated_at));
                $nestedData['available'] = ($row->available) ? "Yes" : "No";
                $nestedData['commission'] = ($row->commission > 0 ) ? $row->commission : "Default";
                $nestedData['total_orders'] = $row->cheforders->count();
                $nestedData['total_order_amount'] = number_format($row->cheforders->sum('grand_total'),2);
                $nestedData['chef_commission'] = number_format($row->cheforders->sum('restaurant_commission_amount'),2);
                $nestedData['total_cancelled_orders'] = $row->cancelledcheforder->count();
                $nestedData['payment_sent'] = number_format($row->chef_payment_sent_sum_restaurant_commission_amount,2);
                $nestedData['payment_approved'] = number_format($row->chef_payment_approved_sum_restaurant_commission_amount,2);
                $nestedData['payment_requested'] = number_format($row->chef_payment_requested_sum_restaurant_commission_amount,2);
                $nestedData['publish_menu'] = $row->new_updation!=null ? "notiy_row" : "trw";
                $nestedData['action'] = $action; //"<a target='_blank' class='btn btn-primary' href='{$edit}' title='Address List' ><i class='fa fa-home'></i></a> ";
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

    public function editPendingChef(Request $request)
    {
        $page_heading = 'Edit Chef Registration';
        $customer =   \App\Models\Partner::with('province','country','companies')->where('id',$request->id)->first();
         $codes      = \App\Models\Country::orderBy('country_id', 'desc')->where('country_language_code', 1)->get();
        $countries      = \App\Models\Country::orderBy('country_id', 'desc')->where('country_id', 4)->where('country_language_code', 1)->get();
        $cities         = \App\Models\City::orderBy('id', 'desc')->get();

       
        $cuisine = \App\Models\Cuisine::where("active", 1)->get();
        $selectedCuisines = [];
        $config = Config::get()->first();
        //PRINT_R($customer);
        $banks = \App\Models\Banks::orderBy('id', 'asc')->get();
        return view('admin.chef.edit_pending', compact('page_heading','customer','codes','countries','cuisine','selectedCuisines','config','banks','cities'));
    }

    public function savePendingChef(Request $request)
    {
        $status = "0";
        $message = '';
        $errors = [];
        $rules = [
            'first_name' => 'required',
            // 'email' => 'required|email|unique:users,email,' . auth()->user()->id,
            'last_name' => 'required',
            'country_id' => '',
            'city_id' => '',
            'brand_name'=>'required',
            'start_time'=>'',
            'end_time'=>'',


            'license_no' => 'required_if:user_type,==,driver',
            'license_expiry' => 'required_if:user_type,==,driver',
            'vehicle_type' => 'required_if:user_type,==,driver',
            'vehicle_no' => 'required_if:user_type,==,driver',
            'vehicle_expiry' => 'required_if:user_type,==,driver',

            'bank_id' => '',
            'account_no' => '',
            'ifsc' => '',
            'swift' => '',
            'logo' => 'max:5120',
            'image' => 'max:5120',
        ];
        if($request->id ==null) {
            $rules['email'] = 'required|email|unique:users,email';
        }

        $validator = Validator::make($request->all(), $rules, [
            'first_name.required' => 'First Name is required',
            'last_name.required' => 'Last Name is required',
            'email.required' => 'Email is required',
            'country_id.required' => 'Country is required',
            // 'providers_id.required' => 'outlet is required',
            'city_id.required' => 'City is required',
            'brand_name.required' => 'Brand Name is required',
            'license_no.required_if' => 'License Number is required',
            'license_expiry.required_if' => 'License expiry date is required',
            'vehicle_type.required_if' => 'Vehicle Type is required',
            'vehicle_no.required_if' => 'Vehicle Number is required',
            'vehicle_expiry.required_if' => 'Vehicle Expiry date is required',
            'bank_id.required' => 'Bank is required',
            'account_no.required' => 'Account Number is required',
            'ifsc.required' => 'IFSC Code is required',
            'swift.required' => 'Swift Code is required',
            'dial_code.required' => 'Dial Code is required',
            'phone_number.required' => 'Mobile Number is required',
            'image.image' => 'should be in image format (.jpg,.jpeg,.png)',
            'end_time'          => 'Must be grater than start time',
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = $validator->messages()->first();
            foreach ($validator->messages()->toArray() as $key => $row) {
                $errors[0][$key] = $row[0];
            }
        } else {
            if(strtotime($request->start_time) >= strtotime($request->end_time)) { 
                $status = "0";
                $message = "";
                $errors[0]['end_time'] = "End time should be greater than start time";
            } else { 
                $partner = \App\Models\Partner::find($request->id);
                $partner->brand_name = $request->brand_name;
                $partner->brand_name_ar = $request->brand_name_ar;
                $partner->brand_name = $request->brand_name;
                $partner->first_name = $request->first_name;
                $partner->last_name = $request->last_name;
                $partner->owner_dial_code = $request->dial_code;
                $partner->owner_mobile = $request->phone_number;
                $partner->gender = $request->gender;
                $partner->cuisine_ids = implode(",",$request->cousine);
                $partner->license_expiraion = date("Y-m-d",strtotime($request->license_expiry));
                $partner->maintainance_expiration = date("Y-m-d",strtotime($request->maintenance_expiry));
                $partner->agreement_expiration = date("Y-m-d",strtotime($request->agreement_expiry));
                $partner->preparation_time = $request->preparation_time;
                $partner->preparation_time_to = $request->preparation_time_to;
                $partner->preparation_unit = $request->preparation_unit;
                $partner->order_limit_per_hour = $request->order_limit_per_hour;
                $partner->admin_commission = $request->admin_commission;
                $partner->delivery_fee = $request->delivery_fee;
                $partner->allow_order_type = $request->allow_ordertype;
                $partner->license_ownership = $request->license_ownership;
                $partner->about_me = $request->about_me;
                $partner->about_me_ar = $request->about_me_ar;
                $partner->bank_id = $request->bank_id;
                $partner->branch = $request->bank_branch;
                $partner->account_number = $request->account_no;
                $partner->benificiary = $request->benificiary;
                $partner->iban_number = $request->ifsc;
                $partner->swift = $request->swift;

                $partner->start_time = date("H:i:s", strtotime($request->start_time));
                $partner->end_time = date("H:i:s", strtotime($request->end_time));
                $partner->country_id = $request->country_id;
                $partner->city_id = $request->city_id;
                $partner->loc_nickname = $request->loc_nickname;
                $partner->apartment_num = $request->apartment_num;
                $partner->building = $request->building;
                $partner->street = $request->street;
                $partner->landmark = $request->landmark;
                $partner->location = $request->txt_location;
                $partner->weekly_ordermode = $request->weekly_order_mode;
                if ($request->file("trade_license")) {
                    $response = image_upload($request, 'users', 'trade_license');
                    if ($response['status']) {
                        $partner->license_doc = $response['link'];
                    }
                }
                if ($request->file("emirates_id")) {
                    $response = image_upload($request, 'users', 'emirates_id');
                    if ($response['status']) {
                        $partner->emirates_id = $response['link'];
                    }
                }
                if ($request->file("visa_copy")) {
                    $response = image_upload($request, 'users', 'visa_copy');
                    if ($response['status']) {
                        $partner->visa_copy = $response['link'];
                    }
                }
                if ($request->file("passport")) {
                    $response = image_upload($request, 'users', 'passport');
                    if ($response['status']) {
                        $partner->passport = $response['link'];
                    }
                }
                 if ($request->file("bank_account_proof")) {
                    $response = image_upload($request, 'users', 'bank_account_proof');
                    if ($response['status']) {
                        $partner->bank_account_proof = $response['link'];
                    }
                }
                if ($request->file("image")) {
                    $response = image_upload($request, 'users', 'image');
                    if ($response['status']) {
                        $partner->image = $response['link'];
                    }
                }
                if ($request->file("cover_image")) {
                        $response = image_upload($request, 'users', 'cover_image');
                        if ($response['status']) {
                            $partner->cover_image = $response['link'];
                        }
                    }
                    if ($request->location) {
                        $location = explode(",", $request->location);
                        $lat = $location[0];
                        $long = $location[1];
                   }
                    $partner->latitude      = $lat;
                    $partner->longitude     = $long;
                $partner->save();
                $status = "1";
                $message = "Saved Successfully";
            }
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }

    public function sort(Request $request)
    {
        $page_title = "Sort Chef";  
        $request->user_id  = 0;     
        
        $cusine_name = 'cuisines.cuisine_name';
        $brand_name =  'restaurants.brand_name';
        $splits = ',';
        
        $users = Users::select(['restaurants.*','users.available','users.weekly_order_mode','restaurants.brand_name as brand_name_en', $brand_name,'users.id as user_id', 'users.email', 'users.name as chef_name', 'users.image', 'users.weekly_order_mode', 'users.email', 'users.rating', 'users.allow_ordertype', DB::raw("string_agg($cusine_name,'".$splits."') as cuisine_name"), 'users.id', 'users.location', 'users.latitude', 'users.longitude', DB::raw("(select coalesce(count(chef_user_id),0) from favourate_chef where favourate_chef.chef_user_id=users.id and favourate_chef.user_id=" . $request->user_id . "  ) favourate_chef"), DB::raw("(select coalesce(avg(rating),0) from user_rating where user_rating.user_id=users.id ) as avg_rating")])->leftjoin('restaurants', 'restaurants.owner_uid', 'users.id')->leftjoin('chef_cuisines', 'chef_cuisines.user_id', 'users.id')
            ->leftjoin('cuisines', 'cuisines.id', 'chef_cuisines.cuisine_id')
            ->where('users.status','!=',5)->where('users.status',1);
        
        
        $is_sorted = false;
        
        $users = $users->orderBy('users.sort_order', 'asc');
        
        
        $users = $users->where('user_type', 'chef')->with(['food_items', 'ratings'])
        ->whereHas('food_items', function ($query){
            $query->where('food_items.is_publish', '=', 1)->where('food_items.active', '=', 1);
        })
        ->groupBy(['users.id', 'restaurants.id'])->get();
        $chefs = $users;
        return view('admin.chef.sort', compact('page_title','chefs'));
    }

    public function savesort(Request $request)
    {
        
        $details = $request->details;
        foreach ($details as $key => $value) {
            Users::where('id',$value)->update(['sort_order'=>$key+1]);
        }
        $status = "1";
        $message = "Sort order saved successfully"; 
        return response()->json(['status' => $status, 'message' => $message]);exit;
    }

    public function exportChefPdfReport(Request $request)
    {
        $data = Users::where("user_type", 'chef')->withCount('cheforders')->withSum('cheforders','grand_total')->withSum('cheforders','restaurant_commission_amount')->withCount('cancelledcheforder')->withSum('chefPaymentSent','restaurant_commission_amount')->withSum('chefPaymentRequested','restaurant_commission_amount')->withSum('chefPaymentApproved','restaurant_commission_amount')->with(['ratings','restaurant'])->join('restaurants','users.id','restaurants.owner_uid')->whereIn('status',[1,0]);
        if (isset($request->name)) {
            $request->name = str_replace("'", "''", $request->name);
             $data->whereRaw("(users.name ilike '%" . $request->name . "%' or restaurants.name ilike '%" . $request->name . "%')");
        }
        if (isset($request->email)) {
            $data->whereRaw("(users.email ilike '%".$request->email."%')");
        }
        if (isset($request->available) && $request->available ==1) {
            $data->where('users.available', 1);
        }

        if (isset($request->phone_number)) {
            $data->where(DB::raw("concat(dial_code,'',phone_number)"),"like","%".$request->phone_number."%");
        }
        $chef_data =  $data->get();
        $pdf = new FPDFExtended();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);        
        $pdf->SetWidths(array(10, 30,15, 20, 20, 20, 20, 21,20,20,30));
        $pdf->Row(array("S.No", "Name", "Tom %","Orders", "Cancelled Orders", "Order Amount","Chef share", "Payment Approved","Payment Requested","Date"));
        foreach ($chef_data as $key => $row) {
            $commission = ($row->commission > 0 ) ? $row->commission : "Default";

            $pdf->Row(array(($key + 1),$row->name,$commission,
                        $row->cheforders->count(),
                        $row->cancelledcheforder->count(),
                        number_format($row->cheforders->sum('grand_total'),2),
                        number_format($row->cheforders->sum('restaurant_commission_amount'),2),
                        number_format($row->chef_payment_approved_sum_restaurant_commission_amount,2),
                        number_format($row->chef_payment_requested_sum_restaurant_commission_amount,2),
                        date('j M Y', strtotime($row->updated_at))
                        ));
        }
        
        $pdf->Output('D', "chef_".date('Y_M_d_his').".pdf");
    }
     public function exportChefCSV(Request $request) { 
        return Excel::download(new ChefExport($request), 'chef_'.date('Y_M_d_his').'.csv', \Maatwebsite\Excel\Excel::CSV);
    }
    public function chefApprovalPdf(Request $request)
    {
        $data = Partner::with('country','city', 'province')->where('emirates_id','!=',NULL);
        if (isset($request->owner_name)) {
            $data->where('owner_name', 'ilike', '%' . $request->owner_name . '%');
        }
        if (isset($request->restaurant_name)) {
            $data->whereRaw("(owner_name ilike '%" . $request->restaurant_name . "%')");
        }       
        $data->where('user_type', 1)->where('registration_status', $request->registration_status);
        $chef_data =  $data->get(); 
        $pdf = new FPDFExtended();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);        
        $pdf->SetWidths(array(10, 30,15, 20, 20, 20, 20, 21,20,20,30));
        $pdf->Row(array("S.No", "Brand Name","Owner Name","Email","Phone Number", "Registration Date", "Preperation Time"));
        foreach ($chef_data as $key => $row) {
            $pdf->Row(array(($key + 1),
                                $row->restaurant_name,
                                $row->owner_name,
                                $row->owner_email,
                                $row->owner_dial_code.$row->owner_mobile,
                                $row->created_at ? date('j M Y', strtotime($row->created_at)) : '',
                                $row->preparation_time." ".$row->preparation_unit.' - '.$row->preparation_time_to.$row->preparation_unit
                            ));
        }
        $pdf->Output('D', "chef_approval".date('Y_M_d_his').".pdf");

    }
    public function exportChefApprovalCSV(Request $request) { 
        return Excel::download(new ChefPendingApprovalExport($request), 'chef_approval'.date('Y_M_d_his').'.csv', \Maatwebsite\Excel\Excel::CSV);
    }

}
