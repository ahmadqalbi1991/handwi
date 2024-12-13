<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\Models\User;
use App\Models\Notifications;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    private function validateAccesToken($access_token)
    {

        $user = User::where(['user_access_token' => $access_token])->get();

        if ($user->count() == 0) {
            http_response_code(401);
            echo json_encode([
                'status' => "0",
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
                    'status' => "0",
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ]);
                exit;
                return response()->json([
                    'status' => "0",
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ], 401);
                exit;
            }
        }
    }

    /**
     * Show the data on app with  new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function list(Request $request)
    {
        $status = (string)1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => (string)0, 'message' => $message,   'errors' => (object) $errors,
            ], 200);
        }
        $user_id = $this->validateAccesToken($request->access_token);
        $notifications = Notifications::where(['user_id' => $user_id])->get();

        $o_data['list'] = convert_all_elements_to_string($notifications);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
}