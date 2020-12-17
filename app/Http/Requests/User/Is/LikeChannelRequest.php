<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Is;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 유저가 채널을 좋아요 여부 판단 전 데이터를 검중하는 클래스 입니다.
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class LikeChannelRequest extends FormRequest
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
