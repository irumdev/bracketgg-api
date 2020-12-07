<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

use App\Http\Requests\Rules\CreateChannel as CreateChannelRules;
use Illuminate\Support\Arr;

/**
 * 팀 생성시 룰 을 만들어주는 클래스 입니다.
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CreateTeam
{
    /**
     * 팀 생성 할 때 룰을 생성해주는 메소드 입니다.
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return array 팀 이름 규칙
     */
    public static function rules(): array
    {
        $rules = explode('|', CreateChannelRules::rules()['name']);
        return [
            'name' => join('|', Arr::replaceItemByKey($rules, count($rules) - 1, 'unique:App\Models\Team\Team,name'))
        ];
    }
}
