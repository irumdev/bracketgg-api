<?php

declare(strict_types=1);

namespace App\Wrappers;

use App\Models\Common\Board\BaseArticle;

class ArticleEventWrapper
{
    public int $type;
    public BaseArticle $article;

    public function __construct(BaseArticle $baseArticle, int $type)
    {
        $this->article = $baseArticle;
        $this->type = $type;
    }
}
