<?php

require_once __DIR__ . '/../models/Analytics.php';

class AnalyticsController {
    private $analytics;

    public function __construct() {
        $this->analytics = new Analytics();
    }

    public function getViewsOverTime($user_id, $days = 30) {
        return $this->analytics->getViewsOverTime($user_id, $days);
    }

    public function getPopularArticles($user_id, $limit = 5) {
        return $this->analytics->getPopularArticles($user_id, $limit);
    }

    public function getCategoryPerformance($user_id) {
        return $this->analytics->getCategoryPerformance($user_id);
    }

    public function getEngagementMetrics($user_id, $days = 30) {
        return $this->analytics->getEngagementMetrics($user_id, $days);
    }

    public function getAnalyticsDashboard($user_id) {
        return [
            'views_over_time' => $this->getViewsOverTime($user_id),
            'popular_articles' => $this->getPopularArticles($user_id),
            'category_performance' => $this->getCategoryPerformance($user_id),
            'engagement_metrics' => $this->getEngagementMetrics($user_id)
        ];
    }
}
