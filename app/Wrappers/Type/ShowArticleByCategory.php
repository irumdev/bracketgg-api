<?php

declare(strict_types=1);

namespace App\Wrappers\Type;

use App\Models\Common\Board\BaseArticle;
use Illuminate\Database\Eloquent\Model;

class ShowArticleByCategory
{
    public Model $model;
    public int $perPage;
    public string $category;

    public function __construct(Model $model, int $perPage, string $categoey)
    {
        $this->model = $model;
        $this->perPage = $perPage;
        $this->category = $categoey;
    }
}
