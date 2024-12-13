<?php

namespace App\Http\Controllers\Admin;

use App\Models\HelpModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class HelpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_heading = "Help";
        $filter = [];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $search_key = $params['search_key'];
        $list = HelpModel::get_help_list($filter, $params)->paginate(10);
        return view("admin.help.list", compact("page_heading", "list", "search_key"));
    }
    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $status = "0";
            $message = "";
            $errors = '';
            $validator = Validator::make($request->all(),
                [
                    'question' => 'required',
                    'answer' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = "0";
                $message = "Validation error occured";
                $errors = $validator->messages();
            } else {
                $ins['active'] = $request->active;
                $ins['title'] = $request->question;
                $ins['description'] = $request->answer;
                $ins['created_at'] = gmdate('Y-m-d H:i:s');
                $ins['created_by'] = session("user_id");
                $ins['updated_by'] = session("user_id");
                $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                if (HelpModel::insert($ins)) {
                    $status = "1";
                    $message = "Help created";
                    $errors = '';
                } else {
                    $status = "0";
                    $message = "Something went wrong";
                    $errors = '';
                }
            }
            echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
        } else {
            $page_heading = "Create Help";
            return view('admin.help.create', compact('page_heading'));
        }

    }
    public function edit($id = '')
    {
        $faq = HelpModel::find($id);
        if ($faq) {
            $page_heading = "Edit Help";
            return view('admin.help.edit', compact('page_heading', 'faq'));
        } else {
            abort(404);
        }
    }

    public function update(Request $request)
    {
        $status = "0";
        $message = "";
        $errors = '';
        $validator = Validator::make($request->all(),
            [
                'question' => 'required',
                'answer' => 'required',
            ]
        );
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $ins['active'] = $request->active;
            $ins['title'] = $request->question;
            $ins['description'] = $request->answer;
            $ins['updated_at'] = gmdate('Y-m-d H:i:s');
            $ins['updated_by'] = session("user_id");
            
            if (HelpModel::where('id', $request->id)->update($ins)) {

                $status = "1";
                $message = "Help updated";
                $errors = '';
            } else {
                $status = "0";
                $message = "Something went wrong";
                $errors = '';
            }
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);die();
    }
    public function delete($id = '')
    {
        HelpModel::where('id', $id)->delete();
        $status = "1";
        $message = "Help removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }

}
