<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\BuildingTypes;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transport;
use Validator;

class TransportController extends Controller
{

    public function list(Request $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => (string) 0,
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $page = $request->page ?? 0;
        if (isset($page))
            $page = ($page==0) ? 0 : $page-1;

        $limit = $request->limit ?? 10;
        $offset = $page * $limit;

        $serviceTypes = Transport::where(['active' => 1])->skip($offset)
        ->take($limit)->get();

        $o_data['list'] = convert_all_elements_to_string($serviceTypes);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }

    
}