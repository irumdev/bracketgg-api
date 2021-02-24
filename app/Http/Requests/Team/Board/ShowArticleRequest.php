<?php

declare(strict_types=1);

namespace App\Http\Requests\Team\Board;

use App\Http\Requests\CommonFormRequest;
use App\Helpers\ValidMessage;
use App\Models\Team\Board\Category;
use App\Models\User;
use App\Models\Team\Team;


use Illuminate\Contracts\Validation\Validator as ValidContract;

class ShowArticleRequest extends CommonFormRequest
{
    private ?User $user;
    private Category $category;
    private Team $team;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $request = request();
        $this->user = $request->user('sanctum');
        $this->team = $request->route('teamSlug');
        $this->category = $request->route('teamBoardCategory');

        if ($this->user === null) {
            return $this->category->is_public;
        }

        if ($this->category->is_public === false) {
            return $this->user->can('viewTeam', $this->team);
        }
        return true;
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
}
