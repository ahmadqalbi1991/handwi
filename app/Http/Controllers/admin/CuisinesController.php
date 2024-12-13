<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\Cuisine;
use Illuminate\Http\Request;
use Validator;

class CuisinesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_heading = "Cuisines";
        $Cuisines = Cuisine::where(['deleted' => '0'])->orderBy('sort_order', 'asc')->get();
        return view('admin.cuisine.list', compact('page_heading', 'Cuisines'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Cuisines";
        $mode = "create";
        $id = "";
        $Cuisine = "";
        $name = "";
        $status="";
        return view("admin.cuisine.create", compact('page_heading', 'Cuisine', 'id', 'name','status'));
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
        $errors = [];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $input = $request->all();

            $luser_name = strtolower($request->name);
            $check_user_name_exist = Cuisine::whereRaw("LOWER(name) = '$luser_name'")
                ->where('id', '!=', $request->id)->get()->toArray();
            if ($check_user_name_exist) {
                $status = "0";
                $message = "name should be unique";
                $errors['name'] = "Already exist";
                echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
                die();
            }

            $ins = [
                'name' => $request->name,
                'status' => $request->status,
            ];

            if ($request->id != "") {
                $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                $user = Cuisine::find($request->id);
                $user->update($ins);

                $status = "1";
                $message = "Cuisine updated succesfully";
            } else {
                $ins['created_at'] = gmdate('Y-m-d H:i:s');
                $c_id = Cuisine::create($ins)->id;

                $status = "1";
                $message = "Cuisine added successfully";
            }
        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // if (!get_user_permission('activity_types', 'u')) {
        //     return redirect()->route('admin.restricted_page');
        // }
        $page_heading = "Edit Cuisines";
        $Cuisine = Cuisine::find($id);
        if (!$Cuisine) {
            abort(404);
        }

        if ($Cuisine) {
            $name = $Cuisine->name;
            $status = $Cuisine->status;
            return view("admin.cuisine.create", compact('page_heading', 'name', 'id','status'));
        } else {
            abort(404);
        }
    }


    public function destroy($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $datatb = Cuisine::find($id);
        if ($datatb) {
            $datatb->deleted = 1;
            $datatb->save();
            $status = "1";
            $message = "Cuisines removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }

    public function get_cuisines(Request $request){

        $activity_types = Cuisine::select('id','name as activity_name')->where(['deleted' => 0])->get();
        $html = view("admin.cuisine.options", compact('activity_types'))->render();
        return response()->json(['html' => $html],200);
    }

    public function sort(Request $request)
    {
        if ($request->ajax()) {
            $status = 0;
            $message = '';

            $items = $request->items;
            $items = explode(",", $items);
            $sorted = Cuisine::sort_item($items);
            if ($sorted) {
                $status = 1;
            }

            echo json_encode(['status' => $status, 'message' => $message]);

        } else {
            $page_heading = "Sort Cuisines";

            $list = Cuisine::where(['deleted' => 0])->orderBy('sort_order', 'asc')->get();
            $back = url("admin/cuisines");
            return view("admin.sort", compact('page_heading', 'list','back'));
        }
    }

    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (Cuisine::where('id', $request->id)->update(['status' => $request->status])) {
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
}
