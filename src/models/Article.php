<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/MarkdownParser.php';

class Article
{
    private $conn;
    private $table_name = 'articles';

    public $article_id;
    public $title;
    public $slug;
    public $content;
    public $author_id;
    public $views_count;
    public $likes_count;
    public $reading_time;
    public $created_at;
    public $updated_at;
    public $categories = [];
    public $status;
    public $image_url;

    public function __construct()
    {
        $this->conn = Database::getInstance();
        if (!$this->conn) {
            throw new Exception("Database connection failed");
        }
    }

    public function create()
    {
        $this->conn->beginTransaction();

        try {
            $query = "INSERT INTO " . $this->table_name . "
                     (title, content, slug, author_id, views_count, likes_count, reading_time, status, image_url)
                     VALUES
                     (:title, :content, :slug, :author_id, 0, 0, :reading_time, :status, :image_url)";

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->title)));

            $word_count = str_word_count(strip_tags($this->content));
            $reading_time = ceil($word_count / 200);

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":title", $this->title);
            $stmt->bindParam(":content", $this->content);
            $stmt->bindParam(":slug", $slug);
            $stmt->bindParam(":author_id", $this->author_id);
            $stmt->bindParam(":reading_time", $reading_time);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":image_url", $this->image_url);

            if ($stmt->execute()) {
                $this->article_id = $this->conn->lastInsertId();

                // Add categories
                if (!empty($this->categories)) {
                    $this->updateCategories();
                }

                $this->conn->commit();
                return true;
            }

            $this->conn->rollBack();
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function updateCategories()
    {
        // First delete existing categories
        $query = "DELETE FROM article_categories WHERE article_id = :article_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $this->article_id);
        $stmt->execute();

        // Insert new categories
        if (!empty($this->categories)) {
            $query = "INSERT INTO article_categories (article_id, category_id) VALUES (:article_id, :category_id)";
            $stmt = $this->conn->prepare($query);

            foreach ($this->categories as $category_id) {
                $stmt->bindParam(":article_id", $this->article_id);
                $stmt->bindParam(":category_id", $category_id);
                $stmt->execute();
            }
        }
    }

    public function getAll($page = 1, $author_id = null)
    {
        $items_per_page = ARTICLES_PER_PAGE;
        $offset = ($page - 1) * $items_per_page;

        $query = "SELECT a.*, u.username as author_name,
                        GROUP_CONCAT(
                            DISTINCT CONCAT(c.category_id, ':', c.name)
                        ) as categories
                 FROM " . $this->table_name . " a
                 LEFT JOIN users u ON a.author_id = u.user_id
                 LEFT JOIN article_categories ac ON a.article_id = ac.article_id
                 LEFT JOIN categories c ON ac.category_id = c.category_id";

        if ($author_id) {
            $query .= " WHERE a.author_id = :author_id";
        }

        $query .= " GROUP BY a.article_id
                   ORDER BY a.created_at DESC
                   LIMIT :offset, :items_per_page";

        $stmt = $this->conn->prepare($query);

        if ($author_id) {
            $stmt->bindParam(":author_id", $author_id);
        }
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":items_per_page", $items_per_page, PDO::PARAM_INT);
        $stmt->execute();

        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process categories for each article
        foreach ($articles as &$article) {
            if (!empty($article['categories'])) {
                $categoriesArray = explode(',', $article['categories']);
                $article['categories'] = array_map(function ($cat) {
                    list($id, $name) = explode(':', $cat);
                    return ['category_id' => $id, 'name' => $name];
                }, $categoriesArray);
            } else {
                $article['categories'] = [];
            }
        }

        return $articles;
    }

    public function getById($article_id)
    {
        $query = "SELECT a.*, u.username as author_name,
                        GROUP_CONCAT(
                            DISTINCT CONCAT(c.category_id, ':', c.name)
                        ) as categories
                 FROM " . $this->table_name . " a
                 LEFT JOIN users u ON a.author_id = u.user_id
                 LEFT JOIN article_categories ac ON a.article_id = ac.article_id
                 LEFT JOIN categories c ON ac.category_id = c.category_id
                 WHERE a.article_id = :article_id
                 GROUP BY a.article_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $article_id);
        $stmt->execute();

        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($article) {
            // Parse markdown content
            $parser = MarkdownParser::getInstance();
            $article['content_html'] = $parser->parse($article['content']);

            // Process categories
            if (!empty($article['categories'])) {
                $categoriesArray = explode(',', $article['categories']);
                $article['categories'] = array_map(function ($cat) {
                    list($id, $name) = explode(':', $cat);
                    return ['category_id' => $id, 'name' => $name];
                }, $categoriesArray);
            } else {
                $article['categories'] = [];
            }
        }

        return $article;
    }

    public function update()
    {
        $this->conn->beginTransaction();

        try {
            $query = "UPDATE " . $this->table_name . "
                     SET title = :title,
                         content = :content,
                         slug = :slug,
                         reading_time = :reading_time
                     WHERE article_id = :article_id AND author_id = :author_id";

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->title)));

            $word_count = str_word_count(strip_tags($this->content));
            $reading_time = ceil($word_count / 200);

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":title", $this->title);
            $stmt->bindParam(":content", $this->content);
            $stmt->bindParam(":slug", $slug);
            $stmt->bindParam(":reading_time", $reading_time);
            $stmt->bindParam(":article_id", $this->article_id);
            $stmt->bindParam(":author_id", $this->author_id);

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // Update categories
            if (isset($this->categories)) {
                // Remove old categories
                $delete_query = "DELETE FROM article_categories WHERE article_id = :article_id";
                $delete_stmt = $this->conn->prepare($delete_query);
                $delete_stmt->bindParam(":article_id", $this->article_id);

                if (!$delete_stmt->execute()) {
                    $this->conn->rollBack();
                    return false;
                }

                // Add new categories
                if (!empty($this->categories)) {
                    $cat_query = "INSERT INTO article_categories (article_id, category_id) VALUES (:article_id, :category_id)";
                    $cat_stmt = $this->conn->prepare($cat_query);

                    foreach ($this->categories as $category_id) {
                        $cat_stmt->bindParam(":article_id", $this->article_id);
                        $cat_stmt->bindParam(":category_id", $category_id);
                        if (!$cat_stmt->execute()) {
                            $this->conn->rollBack();
                            return false;
                        }
                    }
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function delete($article_id, $author_id)
    {
        $this->conn->beginTransaction();

        try {
            // Delete article categories first
            $cat_query = "DELETE FROM article_categories WHERE article_id = :article_id";
            $cat_stmt = $this->conn->prepare($cat_query);
            $cat_stmt->bindParam(":article_id", $article_id);

            if (!$cat_stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // Delete article views
            $views_query = "DELETE FROM article_views WHERE article_id = :article_id";
            $views_stmt = $this->conn->prepare($views_query);
            $views_stmt->bindParam(":article_id", $article_id);

            if (!$views_stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // Delete comments
            $comments_query = "DELETE FROM comments WHERE article_id = :article_id";
            $comments_stmt = $this->conn->prepare($comments_query);
            $comments_stmt->bindParam(":article_id", $article_id);

            if (!$comments_stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // Finally delete the article
            $article_query = "DELETE FROM " . $this->table_name . "
                            WHERE article_id = :article_id AND author_id = :author_id";
            $article_stmt = $this->conn->prepare($article_query);
            $article_stmt->bindParam(":article_id", $article_id);
            $article_stmt->bindParam(":author_id", $author_id);

            if (!$article_stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function incrementViews($article_id)
    {
        // Record view in article_views table
        $view_query = "INSERT INTO article_views (article_id, user_id) 
                      VALUES (:article_id, :user_id)";
        $view_stmt = $this->conn->prepare($view_query);
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $view_stmt->bindParam(":article_id", $article_id);
        $view_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $view_stmt->execute();

        // Update views count in articles table
        $update_query = "UPDATE articles 
                        SET views_count = (
                            SELECT COUNT(*) 
                            FROM article_views 
                            WHERE article_id = :article_id
                        )
                        WHERE article_id = :article_id";

        $update_stmt = $this->conn->prepare($update_query);
        $update_stmt->bindParam(":article_id", $article_id);
        return $update_stmt->execute();
    }

    public function getPreviewContent($content, $length = 150)
    {
        // Strip markdown and truncate
        $plainText = strip_tags(MarkdownParser::getInstance()->parse($content));
        return substr($plainText, 0, $length) . '...';
    }

    public function getTotalPages($author_id = null)
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;

        if ($author_id) {
            $query .= " WHERE author_id = :author_id";
        }

        $stmt = $this->conn->prepare($query);

        if ($author_id) {
            $stmt->bindParam(":author_id", $author_id);
        }

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ceil($row['total'] / ARTICLES_PER_PAGE);
    }

    public function setCategories($category_ids)
    {
        try {
            // First delete existing categories for this article
            $delete_query = "DELETE FROM article_categories WHERE article_id = :article_id";
            $delete_stmt = $this->conn->prepare($delete_query);
            $delete_stmt->bindParam(":article_id", $this->article_id);
            $delete_stmt->execute();

            // Then insert new categories
            $insert_query = "INSERT INTO article_categories (article_id, category_id) VALUES (:article_id, :category_id)";
            $insert_stmt = $this->conn->prepare($insert_query);

            foreach ($category_ids as $category_id) {
                $insert_stmt->bindParam(":article_id", $this->article_id);
                $insert_stmt->bindParam(":category_id", $category_id);
                $insert_stmt->execute();
            }

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getCategories()
    {
        $query = "SELECT c.category_id, c.name 
                 FROM categories c
                 JOIN article_categories ac ON c.category_id = ac.category_id
                 WHERE ac.article_id = :article_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $this->article_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
