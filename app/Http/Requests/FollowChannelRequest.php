<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\ChannelFollower;
use App\Helpers\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

class FollowChannelRequest extends FormRequest
{
    private User $user;
    private ResponseBuilder $response;

    public function __construct(ResponseBuilder $response)
    {
        $this->user = Auth::user();
        $this->response = $response;
    }

    public function authorize(): bool
    {
        $user = $this->user;
        return $user &&
               $user->can('followChannel') &&
               $user->id !== $this->route('slug')->owner;
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->response->fail([
                'code' => $this->buildAuthorizeErrorMessage($this->user),
            ], Response::HTTP_UNAUTHORIZED)
        );
    }

    private function buildAuthorizeErrorMessage(User $user): int
    {
        $message = ChannelFollower::AUTORIZE_FAIL;
        switch ($user) {
            case $user->can('followChannel') === false:
                $message = ChannelFollower::AUTORIZE_FAIL;
                break;

            case $user->id === $this->route('slug')->owner:
                $message = ChannelFollower::OWNER_FOLLOW_OWNER;
                break;

            default:
                $message = ChannelFollower::AUTORIZE_FAIL;
                break;
        }
        return $message;
    }

    public function rules(): array
    {
        return [];
    }
}
