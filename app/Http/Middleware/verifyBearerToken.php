<?php

namespace App\Http\Middleware;

use App\Models\UserTable;
use Closure;
use Illuminate\Http\Request;
use Auth;

class verifyBearerToken
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
        if ($request->hasHeader('Authorization')) {
            if (strpos($request->header('Authorization'), 'Bearer ') === 0) {
                $token_explode = explode('Bearer ', $request->header('Authorization'));
                $user_data = UserTable::where('user_access_token', $token_explode[1])->first();
                if (!empty($user_data)) {
                    return $next($request);
                } else {
                    return response()->json([
                        'status' => '0',
                        'message' => __('messages.errors.invalid_token'),
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => '0',
                    'message' => __('messages.errors.invalid_token'),
                ], 401);
            }
        } else {
            return response()->json([
                'status' => '0',
                'message' => __('messages.errors.invalid_token'),
            ], 401);
        }
    }
}
