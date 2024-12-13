<?php

namespace App\Http\Middleware;

use App\Models\LoginInfo;
use Closure;
use Illuminate\Http\Request;
use Auth;

class verifyApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
//        if ($request->hasHeader('Authorization')) {
//            $token = $request->bearerToken();
//            $login_info = LoginInfo::where(['user_id' => Auth::id(), 'auth_token' => $token])->first();
//            if (!$login_info && !($request->hasHeader('allowWOA') && $request->header('allowWOA'))) {
//                return return_response('0', 401, __('messages.errors.invalid_token'));
//            }
//        }

        if ($request->hasHeader('apiKey')) {
            $env_key = explode(':', env('APP_KEY'))[1];
            if ($request->header('apiKey') === $env_key) {
                return $next($request);
            } else {
                $message = __('messages.errors.api_key_invalid');
            }
        } else {
            $message = __('messages.errors.api_key_missing');
        }

        return response()->json([
            'status' => 0,
            'message' => $message
        ], 404);
    }
}
