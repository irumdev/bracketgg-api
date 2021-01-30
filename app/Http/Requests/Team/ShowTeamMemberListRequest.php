<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ShowTeamMemberListRequest extends FormRequest
{
    /**
     * @var User 유저 인스턴스
     */
    private User $user;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->user = Auth::user();
        return $this->user->can('viewTeam', $this->route('teamSlug'));
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
