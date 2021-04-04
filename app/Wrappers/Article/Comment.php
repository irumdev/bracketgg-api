<?php

declare(strict_types=1);

namespace App\Wrappers\Article;

use App\Models\Common\Board\BaseCategory;
use App\Models\User;
use App\Contracts\TeamAndChannelContract;
use App\Models\Common\Board\BaseArticle;

/**
 * 게시글 댓글 래퍼 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Comment
{
    public function __construct(
        public BaseArticle $article,
        public ?int $parent = null,
        public User $writer,
        TeamAndChannelContract $articleOwnerGroup,
        public string $content
    ) {
        $this->article = $article;
        $this->parent = $parent;
        $this->writer = $writer;
        $this->articleOwnerGroup = $articleOwnerGroup;
        $this->content = $content;
    }

    /**
     * 댓글 생성 시 데이터를
     * 댓글 테이블에 맞는 insert 형태로 가공하는 메소드 입니다.
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return array 생성시 필요한 데이터
     */
    public function reformForCreateComment(): array
    {
        return [
            'article_id' => $this->article->id,
            'parent_id' => $this->parent,
            'user_id' => $this->writer->id,
            $this->article->relateKeyName => $this->articleOwnerGroup->id,
            'content' => strip_tags($this->content)
        ];
    }
}
