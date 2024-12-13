<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'dial_code' => 'required',
            'phone' => 'required|numeric|unique:users',
            'email' => 'required|unique:users',
            'company_name' => 'required',
            'trade_license_number' => 'required',
            'password' => 'required|min:6',
            'fcm_token' => 'required',
            'device_type' => 'required'
        ];
    }
}
