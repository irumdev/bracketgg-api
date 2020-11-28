<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

use App\Http\Requests\Rules\CreateChannel as CreateChannelRules;
use Illuminate\Support\Arr;

class CreateTeam
{
    public static function rules(): array
    {
        $rules = explode('|', CreateChannelRules::rules()['name']);
        return [
            'name' => join('|', Arr::replaceItemByKey($rules, count($rules) - 1, 'unique:App\Models\Team\Team,name'))
        ];
    }
}
