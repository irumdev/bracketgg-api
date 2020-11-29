<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UnFollowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    private const AUTORIZE_FAIL = 1;

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
               $user->can('unFollowChannel');
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->response->fail([
                'code' => $this->buildAuthorizeErrorMessage($this->user),
            ], Response::HTTP_FORBIDDEN)
        );
    }

    private function buildAuthorizeErrorMessage(User $user): int
    {
        $message = self::AUTORIZE_FAIL;
        switch ($user) {
            case $user->can('followChannel') === false:
                $message = self::AUTORIZE_FAIL;
                break;

            default:
                $message = self::AUTORIZE_FAIL;
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
