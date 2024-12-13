<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return return_response('0', 401, __('messages.errors.invalid_token'));
        }

        if (!$request->expectsJson()) {
            return return_response('0', 401, __('messages.errors.invalid_token'));
        }
    }

    /**
     * Handle unauthenticated users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     */
    protected function unauthenticated($request, array $guards)
    {
        return return_response('0', 401, __('messages.errors.invalid_token'));
    }
}
