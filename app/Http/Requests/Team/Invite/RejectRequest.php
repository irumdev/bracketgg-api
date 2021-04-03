<?php

declare(strict_types=1);

namespace App\Http\Requests\Team\Invite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class RejectRequest extends FormRequest
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
        $this->setUser();
        return $this->user->can('rejectInvite', [
            $this->route('teamSlug')
        ]);
    }

    private function setUser(): void
    {
        $reqeustUser = $this->route('userIdx');
        if (is_null($reqeustUser) === false) {
            $this->user = $reqeustUser;
        }
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
