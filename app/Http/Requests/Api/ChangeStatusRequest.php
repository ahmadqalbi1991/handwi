<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_id' => 'required',
            'order_status' => 'required|numeric',
            'access_token' => 'required',
            'fcm_token' => 'required',
            'device_type' => 'required'
        ];
    }
}
