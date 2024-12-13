<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ChangeEmailRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'old_email' => 'required',
            'email' => 'required|required_with:confirm_email|same:confirm_email|unique:users,email',
            'confirm_email' => 'required',
        ];
    }
}
