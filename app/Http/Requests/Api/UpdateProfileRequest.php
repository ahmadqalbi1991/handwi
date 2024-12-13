<?php

namespace App\Http\Requests\Api;

class UpdateProfileRequest extends BaseRequest
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
            'user_image' => 'mimes:jpeg,png,jpg'
        ];
    }
}
