<?php
require_once __DIR__ . '/../src/controllers/AnalyticsController.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: ?page=login");
    exit();
}

// Debug user session
error_log("User ID: " . $_SESSION['user_id']);

$analyticsController = new AnalyticsController();
$dashboard = $analyticsController->getAnalyticsDashboard($_SESSION['user_id']);

// Debug analytics data
error_log("Analytics Dashboard Data: " . print_r($dashboard, true));

// Prepare data for charts
$viewsData = array_column($dashboard['views_over_time'], 'views');
$dates = array_column($dashboard['views_over_time'], 'date');

// Get category data
$categoryNames = array_column($dashboard['category_performance'], 'name');
$categoryViews = array_column($dashboard['category_performance'], 'views');
$categoryLikes = array_column($dashboard['category_performance'], 'likes');

// Get engagement metrics
$metrics = $dashboard['engagement_metrics'];

// Debug prepared data
error_log("Prepared Chart Data: " . print_r([
    'viewsData' => $viewsData,
    'dates' => $dates,
    'categoryNames' => $categoryNames,
    'categoryViews' => $categoryViews,
    'categoryLikes' => $categoryLikes,
    'metrics' => $metrics
], true));
?>

<div class="max-w-7xl mx-auto">
    <style>
        #viewsChart,
        #categoryChart {
            height: 300px !important;
        }
    </style>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
        <p class="text-brand-gray mt-2">Track your content performance</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-soft">
            <div class="text-4xl font-bold text-brand-blue mb-2">
                <?php echo $metrics['total_views']; ?>
            </div>
            <div class="text-brand-gray">Total Views</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-soft">
            <div class="text-4xl font-bold text-brand-blue mb-2">
                <?php echo $metrics['total_likes']; ?>
            </div>
            <div class="text-brand-gray">Total Likes</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-soft">
            <div class="text-4xl font-bold text-brand-blue mb-2">
                <?php echo $metrics['avg_views_per_article']; ?>
            </div>
            <div class="text-brand-gray">Avg. Views per Article</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-soft">
            <div class="text-4xl font-bold text-brand-blue mb-2">
                <?php echo $metrics['total_articles']; ?>
            </div>
            <div class="text-brand-gray">Total Articles</div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid md:grid-cols-2 gap-6 mb-8">
        <!-- Views Over Time -->
        <div class="bg-white p-6 rounded-lg shadow-soft">
            <h2 class="text-xl font-bold mb-4">Views Over Time</h2>
            <div class="relative" style="min-height:300px;">
                <canvas id="viewsChart"></canvas>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="bg-white p-6 rounded-lg shadow-soft">
            <h2 class="text-xl font-bold mb-4">Category Performance</h2>
            <div class="relative" style="min-height:300px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Popular Articles -->
    <div class="bg-white p-6 rounded-lg shadow-soft">
        <h2 class="text-xl font-bold mb-4">Popular Articles</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Likes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dashboard['popular_articles'] as $article): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="?page=article&id=<?php echo $article['article_id']; ?>"
                                    class="text-brand-blue hover:text-blue-600 transition">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $article['views']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $article['likes_count']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $article['comments']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Initialize Charts -->
<script>
    // Data from PHP with fallbacks for empty data
    const dates = <?php echo !empty($dates) ? json_encode($dates) : json_encode([date('Y-m-d')]); ?>;
    const viewsData = <?php echo !empty($viewsData) ? json_encode($viewsData) : json_encode([0]); ?>;
    const categoryNames = <?php echo !empty($categoryNames) ? json_encode($categoryNames) : json_encode(['No Categories']); ?>;
    const categoryViews = <?php echo !empty($categoryViews) ? json_encode($categoryViews) : json_encode([0]); ?>;
    const categoryLikes = <?php echo !empty($categoryLikes) ? json_encode($categoryLikes) : json_encode([0]); ?>;

    console.log('Chart Data:', {
        dates,
        viewsData,
        categoryNames,
        categoryViews,
        categoryLikes
    });

    // Check if canvas elements exist
    const viewsCanvas = document.getElementById('viewsChart');
    const categoryCanvas = document.getElementById('categoryChart');
    console.log('Canvas Elements:', {
        viewsCanvas,
        categoryCanvas
    });

    if (!viewsCanvas || !categoryCanvas) {
        console.error('Canvas elements not found!');
        throw new Error('Required chart elements are missing');
    }

    // Views Over Time Chart
    new Chart(document.getElementById('viewsChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Views',
                data: viewsData,
                borderColor: '#4285F4',
                backgroundColor: 'rgba(66, 133, 244, 0.1)',
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#4285F4',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Category Performance Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: categoryNames,
            datasets: [{
                    label: 'Views',
                    data: categoryViews,
                    backgroundColor: 'rgba(66, 133, 244, 0.8)',
                    borderRadius: 6,
                    borderSkipped: false
                },
                {
                    label: 'Likes',
                    data: categoryLikes,
                    backgroundColor: 'rgba(52, 168, 83, 0.8)',
                    borderRadius: 6,
                    borderSkipped: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 20,
                        font: {
                            size: 13
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
</script>