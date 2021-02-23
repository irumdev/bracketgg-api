<?php

declare(strict_types=1);

namespace App\Events\Listeners\Board;

use Carbon\Carbon;
use App\Models\ArticleViewLog;
use Illuminate\Support\Facades\DB;
use App\Models\Common\Board\BaseArticle;
use Illuminate\Database\Eloquent\Builder;

use App\Events\Dispatchrs\Board\ViewArticle as ViewArticleEvent;
use App\Exceptions\DBtransActionFail;

class ViewArticle
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  InviteCard  $event
     * @return void
     */
    public function handle(ViewArticleEvent $event): void
    {
        $this->increaseViewCount(
            $event->articleEvent->type,
            $event->articleEvent->article
        );
    }

    private function isAlreadyViewArticle(int $type, BaseArticle $article): bool
    {
        return ArticleViewLog::where(function (Builder $condition) use ($article, $type): void {
            $condition->whereBetween(ArticleViewLog::CREATED_AT, [
                Carbon::now()->format('Y-m-d 00:00:00'),
                Carbon::now()->format('Y-m-d 23:59:59'),
            ]);
            $condition->where([
                ['article_id', '=', $article->id],
                ['article_type', '=', $type],
                ['lookup_ip', '=', request()->ip()],
            ]);
        })->exists();
    }

    public function increaseViewCount(int $type, BaseArticle $article): void
    {
        $alreadyViewArticle = $this->isAlreadyViewArticle($type, $article);

        if ($alreadyViewArticle === false) {
            DB::transaction(function () use ($article, $type): void {
                $viewLog = ArticleViewLog::create([
                    'article_id' => $article->id,
                    'article_type' => $type,
                    'lookup_ip' => request()->ip(),
                ]);
                $article->see_count += 1;
                $saveResult = $article->save();

                throw_unless(
                    $saveResult && ($viewLog !== null),
                    new DBtransActionFail()
                );
            });
        }
    }
}
