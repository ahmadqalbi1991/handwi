<?php

namespace App\Http\Controllers\Api\V2\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CustomRequestModel;
use DB;
use Validator;
use App\Models\User;
use App\Models\Rating;
use App\Classes\FaceReg;

class RatingController extends Controller
{
    
    public function review_list(REQUEST $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required'
        ]);

        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {

            $user_id = $this->validateAccesToken($request->access_token);
            $review = Rating::join('order_products','order_products.product_id','=','ratings.product_id')
            ->select('order_id','product_name','ratings.product_id','ratings.product_varient_id','ratings.rating','ratings.title',
                'ratings.comment','ratings.created_at')
            ->join('product','product.id','=','ratings.product_id')
            ->where('product_vender_id',$user_id)
            ->get();
                 
            if($review->first()) {
                foreach ($review as $key => $value) {
                    $review[$key]->order_number = config('global.sale_order_prefix').date(date('Ymd', strtotime($value->created_at))).$value->order_id;
                    $review[$key]->date  = get_date_in_timezone($value->created_at, 'd-M-y H:i A');
                }
                $o_data = $review;
            }
            else
            {
                $o_data = [];
            }
            $o_data = convert_all_elements_to_string($o_data);
        }
        return response()->json(['status' => $status, 'errors' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
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
            if ($user->verified == 1) {
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
}
