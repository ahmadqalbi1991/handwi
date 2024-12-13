<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_id' => 'required|numeric|exists:product,id',
            'message' => 'required|string',
            'subject' => 'required|string',
            'expected_quantity' => 'required|numeric',
        ];
    }
}
