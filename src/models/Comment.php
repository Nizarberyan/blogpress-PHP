<?php
require_once __DIR__ . '/../../config/database.php';

class Comment
{
    private $conn;
    private $table_name = 'comments';

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    public function create($article_id, $user_id, $content)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                 (article_id, user_id, content) 
                 VALUES (:article_id, :user_id, :content)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":article_id", $article_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":content", $content);

        return $stmt->execute();
    }

    public function getByArticle($article_id, $page = 1)
    {
        $items_per_page = COMMENTS_PER_PAGE;
        $offset = ($page - 1) * $items_per_page;

        $query = "SELECT c.*, u.username 
                 FROM " . $this->table_name . " c
                 LEFT JOIN users u ON c.user_id = u.user_id
                 WHERE article_id = :article_id
                 ORDER BY c.created_at DESC
                 LIMIT :offset, :items_per_page";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $article_id);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":items_per_page", $items_per_page, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalComments($article_id)
    {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                 WHERE article_id = :article_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $article_id);
        $stmt->execute();

        return $stmt->fetchColumn();
    }
}
