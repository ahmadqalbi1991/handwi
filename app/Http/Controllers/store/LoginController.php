<?php

namespace App\Http\Controllers\store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //
    public function login()
    {
        if (Auth::check() && (Auth::user()->role == '1')) {
            return redirect()->route('store.dashboard');
        }
        // echo Hash::make('Hello@1985');
        return view('store_manager.login');
    }
    public function check_login(Request $request)
    {

        $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required',
        ]);

        // Validate request
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            if (Auth::check() && (Auth::user()->role == '4')) {
                $request->session()->put('user_id',Auth::user()->id);
                return response()->json(['success' => true, 'message' => "Logged in successfully."]);
            }elseif (Auth::check() && (Auth::user()->active == '0')) {
                return response()->json(['success' => false, 'message' => "You are blocked by admin!"]);
            } else {
                return response()->json(['success' => false, 'message' => "Invalid Credentials!"]);
            }
        }

        return response()->json(['success' => false, 'message' => "Invalid Credentials!"]);
    }
    public function logout(){
        session()->pull("user_id");
        Auth::logout();
        return redirect()->route('store.login');
    }
}
