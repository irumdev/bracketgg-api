<?php

namespace App\Repositories;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class ChannelRepository
{
    private Channel $channel;
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    public function findByUserId(string $userId): Builder
    {
        return Channel::whereHas('user', function ($query) use ($userId) {
            $query->where('owner', $userId);
        })->with(User::$channelsInfo);
        // return User::findOrFail($userId)->channels()->with(User::$channelsInfo)->get();
    }

    public function findById(string $id): Channel
    {
        return Channel::with(User::$channelsInfo)->findOrFail($id);
    }
}
