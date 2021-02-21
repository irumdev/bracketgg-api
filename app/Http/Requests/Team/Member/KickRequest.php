<?php

declare(strict_types=1);

namespace App\Http\Requests\Team\Member;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class KickRequest extends FormRequest
{
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
        return $this->user->can('kickTeamMember', [
            $this->route('teamSlug'),
            $this->route('userIdx'),
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

        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'team' => $this->route('teamSlug'),
            'willKickUser' => $this->route('user'),
        ]);
    }
}
