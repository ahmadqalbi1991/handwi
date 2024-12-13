<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public static function authorizeStatic($id)
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        $msg = "";
        foreach ($validator->getMessageBag()->toArray() as $key => $errMsg) {

            $msg = $errMsg[0];
            $errors[$key] = $errMsg[0];
        }
        $response = new JsonResponse([
            'success' => false,
            'msg'=>$msg,
            'code' => 422,
            'data' => (object)[],
            'errors' => (object)$errors
        ], 200);

        throw new ValidationException($validator, $response);
    }
}
