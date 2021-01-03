<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use App\Models\User;
use App\Models\Team\Team;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\CommonFormRequest;

class ShowInfoRequest extends CommonFormRequest
{
    /**
     * @var User 유저 인스턴스
     */
    private ?User $user;

    /**
     * @var Team 팀 인스턴스
     */
    private Team $team;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->user = request()->user('sanctum');
        $this->team = $this->route('teamSlug');

        if ($this->user === null) {
            return $this->team->is_public;
        }

        if ($this->team->is_public === false) {
            return $this->user->can('viewTeam', $this->team);
        }

        return true;
    }

    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
