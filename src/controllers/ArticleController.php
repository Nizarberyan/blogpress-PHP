<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../../config/database.php';

if (!class_exists('Article')) {
    die('Article class not found');
}

class ArticleController
{
    private $article;

    public function __construct()
    {
        try {
            $this->article = new Article();
            if (!is_object($this->article)) {
                throw new Exception('Failed to create Article instance');
            }
        } catch (Exception $e) {
            die("Error initializing ArticleController: " . $e->getMessage());
        }
    }

    public function createArticle($title, $content, $author_id, $status = 'draft', $image_url = '', $categories = [])
    {
        $this->article->title = $title;
        $this->article->content = $content;
        $this->article->author_id = $author_id;
        $this->article->status = $status;
        $this->article->image_url = $image_url;
        $this->article->categories = $categories;

        return $this->article->create();
    }

    public function getArticles($page = 1, $author_id = null)
    {
        return $this->article->getAll($page, $author_id);
    }

    public function getArticle($article_id)
    {
        return $this->article->getById($article_id);
    }

    public function updateArticle($article_id, $title, $content, $author_id, $categories = [])
    {
        $this->article->article_id = $article_id;
        $this->article->title = $title;
        $this->article->content = $content;
        $this->article->author_id = $author_id;
        $this->article->categories = $categories;

        return $this->article->update();
    }

    public function deleteArticle($article_id, $author_id)
    {
        return $this->article->delete($article_id, $author_id);
    }

    public function getTotalPages($author_id = null)
    {
        try {
            if (!method_exists($this->article, 'getTotalPages')) {
                throw new Exception('Method getTotalPages does not exist in Article class');
            }
            return $this->article->getTotalPages($author_id);
        } catch (Exception $e) {
            error_log("Error in getTotalPages: " . $e->getMessage());
            return 1; // Return default value if error occurs
        }
    }

    public function incrementViews($article_id)
    {
        return $this->article->incrementViews($article_id);
    }

    public function toggleLike($article_id, $user_id)
    {
        try {
            $db = Database::getInstance();
            $db->beginTransaction();

            // Update likes_count in articles table
            $stmt = $db->prepare("UPDATE articles SET likes_count = likes_count + 1 WHERE article_id = ?");
            $success = $stmt->execute([$article_id]);

            if ($success) {
                $db->commit();
                return true;
            } else {
                $db->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error toggling like: " . $e->getMessage());
            return false;
        }
    }

    public function getLikeCount($article_id)
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT likes_count FROM articles WHERE article_id = ?");
            $stmt->execute([$article_id]);
            return $stmt->fetchColumn() ?: 0;
        } catch (PDOException $e) {
            error_log("Error getting like count: " . $e->getMessage());
            return 0;
        }
    }

    public function isLikedByUser($article_id, $user_id)
    {
        // Since we don't track individual likes, always return false
        return false;
    }
}
