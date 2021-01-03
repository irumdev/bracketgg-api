<?php

declare(strict_types=1);

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Model;
use App\Factories\BroadcastFactory;

class Broadcast extends Model
{
    protected $table = 'team_broadcasts';

    protected $fillable = ['team_id', 'broadcast_address', 'platform'];

    public static array $platforms = BroadcastFactory::PLATFORMS;
}
