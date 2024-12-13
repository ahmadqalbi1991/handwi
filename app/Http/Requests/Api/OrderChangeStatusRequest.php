<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OrderChangeStatusRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_id' => 'required|numeric',
            'order_status' => 'required|numeric',
            'comment' => 'nullable|string'
        ];
    }
}
