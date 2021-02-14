<?php

declare(strict_types=1);

namespace App\Repositories\Team;

use App\Factories\BoardFactory;

use App\Models\Team\Team;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Team\Board\Article as TeamBoardArticle;
use App\Models\ArticleViewLog;
use App\Repositories\Common\BoardRespository as BaseBoardRepository;

class BoardRespository extends BaseBoardRepository
{
}
