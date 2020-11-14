<?php

declare(strict_types=1);

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Model;
use App\Factories\BroadcastFactory;

class Broadcast extends Model
{
    /**
     * @todo 같은플랫폼으로 여러개개 들어갈 수 있음
     * @todo 같은플랫폼 같은주소는 안됨
     */
    public array $platforms = BroadcastFactory::PLATFORMS;
}
