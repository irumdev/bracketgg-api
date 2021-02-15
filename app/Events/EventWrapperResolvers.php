<?php

declare(strict_types=1);

namespace App\Events;

use App\Events\Dispatchrs\Team\InviteCard;
use App\Wrappers\TeamInviteCard as TeamInviteCard;

use App\Models\Team\Team;
use App\Models\Common\Board\BaseArticle;

use App\Events\Dispatchrs\Board\ViewArticle as ViewArticleEventDispatcher;
use App\Wrappers\ArticleEventWrapper;

if (! function_exists('teamInviteResolver')) {
    function teamInviteResolver(Team $team, int $tagetUser, int $type): InviteCard
    {
        return new InviteCard(
            new TeamInviteCard($team, $tagetUser, $type)
        );
    }
}

if (! function_exists('viewArticleResolver')) {
    function viewArticleResolver(BaseArticle $article, int $type)
    {
        return new ViewArticleEventDispatcher(
            new ArticleEventWrapper($article, $type)
        );
    }
}
