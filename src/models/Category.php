<?php

require_once __DIR__ . '/../../config/database.php';

class Category
{
    private $conn;
    private $table_name = 'categories';

    public $category_id;
    public $name;
    public $description;

    public function __construct()
    {
        $this->conn = Database::getInstance();
        if (!$this->conn) {
            throw new Exception("Database connection failed");
        }
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                 (name, description) VALUES (:name, :description)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);

        return $stmt->execute();
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($category_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . "
                 SET name = :name, description = :description
                 WHERE category_id = :category_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);

        return $stmt->execute();
    }

    public function delete($category_id)
    {
        // First check if category is being used
        $check_query = "SELECT COUNT(*) as count FROM article_categories WHERE category_id = :category_id";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":category_id", $category_id);
        $check_stmt->execute();
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            return false; // Category is in use
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);

        return $stmt->execute();
    }

    public function getArticleCategories($article_id)
    {
        $query = "SELECT c.* 
                 FROM " . $this->table_name . " c
                 JOIN article_categories ac ON c.category_id = ac.category_id
                 WHERE ac.article_id = :article_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $article_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
