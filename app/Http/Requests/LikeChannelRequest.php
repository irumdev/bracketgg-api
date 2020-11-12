<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ChannelFan;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Helpers\ResponseBuilder;
use Illuminate\Support\Facades\Auth;

class LikeChannelRequest extends FormRequest
{
    private ResponseBuilder $responseBuilder;
    private User $user;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
        $this->user = Auth::user();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->user;
        return $user && $user->can('likeChannel');
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail([
                'code' => $this->buildAuthorizeErrorMessage($this->user),
            ], Response::HTTP_UNAUTHORIZED)
        );
    }


    private function buildAuthorizeErrorMessage(User $user): int
    {
        $message = ChannelFan::AUTORIZE_FAIL;
        switch ($user) {
            case $user->can('likeChannel') === false:
                $message = ChannelFan::AUTORIZE_FAIL;
                break;

            default:
                $message = ChannelFan::AUTORIZE_FAIL;
                break;
        }
        return $message;
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
