<?php

declare(strict_types=1);

namespace App\Http\Requests\Team\Invite;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Team\Team;
use App\Helpers\ValidMessage;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Http\Requests\CommonFormRequest;

class InviteRequest extends CommonFormRequest
{
    private User $user;
    private User $inviteUser;
    private Team $team;

    /**
     * @var int 이미 초대장을 보낸 상황
     */
    public const ALREADY_SEND_INVITE_CARD = 1;

    /**
     * @var int 이미 팀원
     */
    public const RECEIVER_ALREADY_TEAM_MEMBER = 2;

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

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }

    /**
     * @override
     * @see Illuminate\Http\Concerns\InteractsWithInput
     */
    public function all($keys = null): array
    {
        return array_merge(request()->all(), [
            'user' => $this->inviteUser,
            'inviteUserId' => $this->inviteUser,
            'team' => $this->route('teamSlug')
        ]);
    }

    public function messages(): array
    {
        return [
            'user.already_invite' => self::ALREADY_SEND_INVITE_CARD,
            'inviteUserId.is_not_team_member' => self::RECEIVER_ALREADY_TEAM_MEMBER
        ];
    }
}
