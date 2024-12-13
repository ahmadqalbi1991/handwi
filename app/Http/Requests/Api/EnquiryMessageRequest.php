<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EnquiryMessageRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'enquiry_id' => 'required|numeric|exists:enquiry,id',
            'message' => 'required|string',
        ];
    }
}
