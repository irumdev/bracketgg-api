<?php

declare(strict_types=1);

namespace App\Http\Requests\Team\Invite;

use App\Helpers\ValidMessage;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\CommonFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator as ValidContract;

class NormalUserJoinRequest extends CommonFormRequest
{
    /**
     * @var int 이미 초대장을 보낸 상황
     */
    public const ALREADY_SEND_JOIN_REQUEST = 1;

    /**
     * @var int 이미 팀원
     */
    public const SENDER_ALREADY_TEAM_MEMBER = 2;

    private User $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
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

    public function prepareForValidation(): void
    {
        $this->merge([
            'user' => $this->user,
            'inviteUserId' => $this->user,
        ]);
    }

    public function messages(): array
    {
        return [
            'user.already_invite' => self::ALREADY_SEND_JOIN_REQUEST,
            'inviteUserId.is_not_team_member' => self::SENDER_ALREADY_TEAM_MEMBER
        ];
    }
}
