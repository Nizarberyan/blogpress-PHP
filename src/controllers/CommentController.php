<?php
require_once __DIR__ . '/../models/Comment.php';

class CommentController
{
    private $comment;

    public function __construct()
    {
        $this->comment = new Comment();
    }

    public function createComment($article_id, $user_id, $content)
    {
        return $this->comment->create($article_id, $user_id, $content);
    }

    public function getComments($article_id, $page = 1)
    {
        return $this->comment->getByArticle($article_id, $page);
    }

    public function getTotalComments($article_id)
    {
        return $this->comment->getTotalComments($article_id);
    }
}
