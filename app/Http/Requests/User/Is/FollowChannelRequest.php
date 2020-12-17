<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Is;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 채널 팔로우 여부 판단 전 데이터를 검증하는 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class FollowChannelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
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
