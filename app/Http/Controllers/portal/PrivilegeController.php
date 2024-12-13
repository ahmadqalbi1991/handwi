<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPrivileges;

class PrivilegeController extends Controller
{
    public function privilege(Request $request){

        $store_manager_id = decrypt($request->id);

        $record = User::select('users.*', 'd.designation')->join('designations as d', 'd.id', '=', 'users.designation_id')->where('users.id', $store_manager_id)->first();

        $page_heading = "Set Privileges for " . $record->designation;

        return view("vendor.privilege.create", compact('page_heading', 'record'));
    }
    public function save_privilege( Request $request ){

        $user_id = $request->id;
        $designation_id = User::select('designation_id')->where('id', $user_id)->first()->designation_id;
        $access_groups = json_encode($request->access_groups);

        UserPrivileges::updateOrCreate(
            [
                'user_id'   => $user_id,
                "designation_id" => $designation_id
            ],
            [
                "user_id" => $user_id,
                "designation_id" => $designation_id,
                "privileges" => $access_groups,
                "status" => 1
            ]
        );
        return redirect()->back()->withInput()->with('message', 'Privileges updated successfully');;
    }
}
