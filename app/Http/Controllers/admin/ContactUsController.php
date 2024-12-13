<?php

namespace App\Http\Controllers\admin;

use App\Models\ContactUsModel;
use App\Http\Controllers\Controller;

class ContactUsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
        $page_heading = "Contact Us";
        $filter = [];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $search_key = $params['search_key'];
        $list = ContactUsModel::get_list($filter, $params)->paginate(10);
        return view("admin.contact.list", compact("page_heading", "list", "search_key"));
    }

    public function delete($id = '')
    {
        // if (!have_prmission('CNTCT_US', 'D')) {
        //     $status = "0";
        //     $message = "You don't have permission to do this action";
        //     echo json_encode(['status' => $status, 'message' => $message]);die();
        // }
        ContactUsModel::where('id', $id)->delete();
        $status = "1";
        $message = "details removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }

}
