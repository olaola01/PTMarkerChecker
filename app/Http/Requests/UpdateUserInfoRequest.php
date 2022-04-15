<?php

namespace App\Http\Requests;

use App\Enum\RequestTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserInfoRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'first_name' => ['string'],
            'last_name' => ['string'],
            'email' => ['string'],
            'request_type' => ['string', Rule::in([RequestTypes::CREATE, RequestTypes::UPDATE, RequestTypes::DELETE])],
            'user_info_id' => ['integer']
        ];
    }
}
