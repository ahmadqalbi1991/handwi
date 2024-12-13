<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Hash;


class LoginController extends Controller
{
    //
    public function clear()
    {
        \Artisan::call('optimize');
        \Artisan::call('optimize:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('config:cache');
        \Artisan::call('view:clear');
        \Artisan::call('config:clear');
        
        return redirect()->back();
    }
    
    public function login()
    {
        //send_normal_SMS("OTP to confirm Laconcierge registration is 5834","971564005096");
        if (Auth::check() && (Auth::user()->role == '1')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }
    public function check_login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required',
        ]);

        if (Auth::attempt(['user_email_id' => $request->email, 'password' => $request->password])) {
            if (Auth::check() && (Auth::user()->is_admin)) {
                return response()->json(['success' => true, 'message' => "Logged in successfully."]);
            } else {
                return response()->json(['success' => false, 'message' => "Invalid Credentials!"]);
            }
        }

        return response()->json(['success' => false, 'message' => "Invalid Credentials!"]);
    }
    public function logout(){
        Auth::logout();
        return redirect()->route('admin.login');
    }
}
