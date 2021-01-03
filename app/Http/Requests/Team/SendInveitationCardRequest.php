<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseBuilder;
use App\Models\Team\Team;
use App\Models\Team\Member as TeamMember;
use App\Helpers\ValidMessage;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

class SendInveitationCardRequest extends FormRequest
{
    private User $user;
    private User $inviteUser;
    private Team $team;
    private ResponseBuilder $responseBuilder;

    /**
     * @var int 이미 초대장을 보낸 상황
     */
    public const ALREADY_SEND_INVITE_CARD = 1;

    /**
     * @var int 이미 팀원
     */
    public const RECEIVER_ALREADY_TEAM_MEMBER = 2;

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
        $this->team = $this->route('teamSlug');
        $this->inviteUser = $this->route('userIdx');
        return $this->user->can('inviteMember', [
            $this->team,
            $this->inviteUser
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user' => [
                'bail',
                'alreadyInvite',
            ],
            'inviteUserId' => 'bail|isNotTeamMember'
        ];
    }

    private function throwHttpException($message, int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail($message, $httpStatus)
        );
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwHttpException(ValidMessage::first($validator));
    }

    /**
     * @override
     * @see Illuminate\Http\Concerns\InteractsWithInput
     */
    public function all($keys = null)
    {
        return array_merge(request()->all(), [
            'user' => $this->inviteUser,
            'inviteUserId' => $this->inviteUser->id,
            'team' => $this->route('teamSlug')
        ]);
    }

    public function messages(): array
    {
        return [
            'user.already_invite' => json_encode(['code' => self::ALREADY_SEND_INVITE_CARD]),
            'inviteUserId.is_not_team_member' => json_encode(['code' => self::RECEIVER_ALREADY_TEAM_MEMBER])
        ];
    }
}
