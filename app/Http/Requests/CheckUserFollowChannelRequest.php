<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckUserFollowChannelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @todo 넣을꺼 생각하기
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
