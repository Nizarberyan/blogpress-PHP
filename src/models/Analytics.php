<?php

require_once __DIR__ . '/../../config/database.php';

class Analytics
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    public function getViewsOverTime($user_id, $days = 30)
    {
        try {
            $query = "SELECT DATE(created_at) as date, SUM(views_count) as views
                    FROM articles
                    WHERE author_id = :user_id
                    AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL :days DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':days', $days);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return !empty($result) ? $result : [['date' => date('Y-m-d'), 'views' => 0]];
        } catch (PDOException $e) {
            return [['date' => date('Y-m-d'), 'views' => 0]];
        }
    }

    public function getPopularArticles($user_id, $limit = 5)
    {
        $query = "SELECT a.title, a.article_id,
                        a.views_count as views,
                        COUNT(DISTINCT c.comment_id) as comments,
                        a.likes_count
                 FROM articles a
                 LEFT JOIN comments c ON a.article_id = c.article_id
                 WHERE a.author_id = :user_id
                 GROUP BY a.article_id
                 ORDER BY a.views_count DESC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryPerformance($user_id)
    {
        try {
            $query = "SELECT 
                        COALESCE(c.name, 'Uncategorized') as name, 
                        SUM(a.views_count) as views,
                        COUNT(DISTINCT com.comment_id) as comments,
                        COALESCE(SUM(a.likes_count), 0) as likes
                    FROM articles a
                    LEFT JOIN article_categories ac ON a.article_id = ac.article_id
                    LEFT JOIN categories c ON ac.category_id = c.category_id
                    LEFT JOIN comments com ON a.article_id = com.article_id
                    WHERE a.author_id = :user_id
                    GROUP BY COALESCE(c.name, 'Uncategorized')
                    ORDER BY views DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ensure we have numeric values for the chart
            $result = array_map(function ($row) {
                return [
                    'name' => $row['name'],
                    'views' => (int)$row['views'],
                    'comments' => (int)$row['comments'],
                    'likes' => (int)$row['likes']
                ];
            }, $result);

            return !empty($result) ? $result : [
                [
                    'name' => 'Uncategorized',
                    'views' => 0,
                    'comments' => 0,
                    'likes' => 0
                ]
            ];
        } catch (PDOException $e) {
            return [
                [
                    'name' => 'Uncategorized',
                    'views' => 0,
                    'comments' => 0,
                    'likes' => 0
                ]
            ];
        }
    }

    public function getEngagementMetrics($user_id, $days = 30)
    {
        $query = "SELECT 
                    SUM(views_count) as total_views,
                    COUNT(DISTINCT c.comment_id) as total_comments,
                    SUM(likes_count) as total_likes,
                    COUNT(DISTINCT a.article_id) as total_articles,
                    ROUND(AVG(views_count), 2) as avg_views_per_article,
                    ROUND(AVG(likes_count), 2) as avg_likes_per_article
                 FROM articles a
                 LEFT JOIN comments c ON a.article_id = c.article_id
                 WHERE a.author_id = :user_id
                 AND a.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Handle null values
            return [
                'total_views' => $result['total_views'] ?? 0,
                'total_comments' => $result['total_comments'] ?? 0,
                'total_likes' => $result['total_likes'] ?? 0,
                'total_articles' => $result['total_articles'] ?? 0,
                'avg_views_per_article' => $result['avg_views_per_article'] ?? 0,
                'avg_likes_per_article' => $result['avg_likes_per_article'] ?? 0
            ];
        } catch (PDOException $e) {
            error_log("Analytics error: " . $e->getMessage());
            return [
                'total_views' => 0,
                'total_comments' => 0,
                'total_likes' => 0,
                'total_articles' => 0,
                'avg_views_per_article' => 0,
                'avg_likes_per_article' => 0
            ];
        }
    }
}
