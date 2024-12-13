<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OrderListRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'page_no' => 'required|numeric',
            'order_status' => 'required|numeric',
            'per_page_rec' => 'required|numeric'
        ];
    }
}
