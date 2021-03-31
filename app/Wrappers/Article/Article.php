<?php

declare(strict_types=1);

namespace App\Wrappers\Article;

use App\Models\Common\Board\BaseCategory;
use App\Models\User;
use App\Contracts\TeamAndChannelContract;

/**
 * 게시글 래퍼 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Article
{
    public function __construct(
        public User $writer,
        TeamAndChannelContract $articleOwnerGroup,
        public BaseCategory $category,
        public string $title,
        public string $content
    ) {
        $this->writer = $writer;
        $this->category = $category;
        $this->articleOwnerGroup = $articleOwnerGroup;
        $this->title = $title;
        $this->content = $content;
    }

    /**
     * 게시글 생성 시 데이터를
     * 게시글 테이블에 맞는 insert 형태로 가공하는 메소드 입니다.
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return array 생성시 필요한 데이터
     */
    public function reformForCreateArticle(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => $this->writer->id,
            'category_id' => $this->category->id,
            $this->category->relatedKey => $this->articleOwnerGroup->id,
            'see_count' => 0,
            'like_count' => 0,
            'unlike_count' => 0,
            'comment_count' => 0,
        ];
    }
}
