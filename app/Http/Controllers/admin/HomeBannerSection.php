<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppHomeSection;

class HomeBannerSection extends Controller
{
    //
    public function index(REQUEST $request){
        $page_heading = "Home Banner Section";
        $list = AppHomeSection::orderBy('sort_order','asc')->get();
        return view('admin.home_banner_section.list', compact('page_heading', 'list'));
    }
    public function updateOrder(Request $request)
    {
        $sortedIDs = $request->input('sortedIDs');

        // Loop through sorted IDs and update the order in the database
        foreach ($sortedIDs as $index => $id) {
            AppHomeSection::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (AppHomeSection::where('id', $request->id)->update(['status' => $request->status])) {
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
    public function destroy($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        AppHomeSection::where(['id'=>$id])->delete();

        echo json_encode(['status' => 1, 'message' => "removed successfully", 'o_data' => $o_data]);
    }
}
