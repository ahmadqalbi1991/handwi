<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SkinColors;
use Illuminate\Http\Request;
use Validator;

class SkinColor extends Controller
{
    public function index()
    {
        //
        $page_heading = "Skin Colors";
        $colors = SkinColors::where(['deleted' => 0])->get();
        return view('admin.skin_color.list', compact('page_heading', 'colors'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Skin Colors";
        $mode = "create";
        $id = "";
        $color = "";
        $name = "";
        return view("admin.skin_color.create", compact('page_heading', 'mode', 'id', 'name', 'color'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
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
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $input = $request->all();
            $check_exist = SkinColors::where(['deleted' => 0, 'color' => $request->color])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'color' => $request->color,
                ];

                if ($request->id != "") {
                    $ins['updated_at'] = gmdate('Y-m-d H:i:s');
                    $color = SkinColors::find($request->id);
                    $color->update($ins);
                    $status = "1";
                    $message = "Color updated succesfully";
                } else {
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    SkinColors::create($ins);
                    $status = "1";
                    $message = "Color added successfully";
                }
            } else {
                $status = "0";
                $message = "Color should be unique";
                $errors['color'] = $request->color . " already added";
            }

        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }

    public function edit($id)
    {
        //
        $color = SkinColors::find($id);
        if ($color) {
            $page_heading = "Skin Colors";
            $mode = "edit";
            $id = $color->id;
            $name = $color->name;
            $color = $color->color;
            return view("admin.skin_color.create", compact('page_heading', 'mode', 'id', 'name', 'color'));
        } else {
            abort(404);
        }
    }

    public function destroy($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $color = SkinColors::find($id);
        if ($color) {
            $color->deleted = 1;
            $color->active = 0;
            $color->save();
            $status = "1";
            $message = "Color removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);
    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (SkinColors::where('id', $request->id)->update(['active' => $request->status])) {
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
