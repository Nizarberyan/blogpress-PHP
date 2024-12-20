<?php
require_once __DIR__ . '/../src/controllers/ArticleController.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: ?page=login");
    exit();
}

// Initialize controller
$articleController = new ArticleController();

// Get user's articles
$articles = $articleController->getArticles(1, $_SESSION['user_id']);

// Calculate basic statistics
$total_articles = count($articles);
$total_views = 0;
$total_likes = 0;

foreach ($articles as $article) {
    $total_views += $article['views_count'];
    $total_likes += $article['likes_count'];
}

?>

<div class="max-w-7xl mx-auto">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </h1>
        <p class="text-brand-gray">
            Manage your articles and track your blogging progress.
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-soft">
            <div class="text-4xl font-bold text-brand-blue mb-2">
                <?php echo $total_articles; ?>
            </div>
            <div class="text-brand-gray">Total Articles</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-soft">
            <div class="text-4xl font-bold text-brand-blue mb-2">
                <?php echo $total_views; ?>
            </div>
            <div class="text-brand-gray">Total Views</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-soft">
            <div class="text-4xl font-bold text-brand-blue mb-2">
                <?php echo $total_likes; ?>
            </div>
            <div class="text-brand-gray">Total Likes</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex space-x-4 mb-8">
        <a href="?page=create_article" 
           class="bg-brand-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">
            Create New Article
        </a>
    </div>

    <!-- Recent Articles -->
    <div>
        <h2 class="text-xl font-bold mb-4">Your Articles</h2>
        <?php if (empty($articles)): ?>
            <div class="bg-white p-6 rounded-lg shadow-soft text-brand-gray">
                No articles yet. Start writing your first article!
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 gap-6">
                <?php foreach ($articles as $article): ?>
                    <div class="bg-white p-6 rounded-lg shadow-soft">
                        <h3 class="font-bold mb-2">
                            <a href="?page=article&id=<?php echo $article['article_id']; ?>" 
                               class="text-gray-900 hover:text-brand-blue transition">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h3>
                        <div class="text-sm text-brand-gray mb-3">
                            Published on <?php echo date('M j, Y', strtotime($article['created_at'])); ?>
                        </div>
                        <div class="flex space-x-4 text-sm text-brand-gray">
                            <span>👁 <?php echo $article['views_count']; ?> views</span>
                            <span>❤️ <?php echo $article['likes_count']; ?> likes</span>
                            <span>⏱ <?php echo $article['reading_time']; ?> min read</span>
                        </div>
                        <div class="mt-4 flex space-x-3">
                            <a href="?page=edit_article&id=<?php echo $article['article_id']; ?>"
                               class="text-brand-blue hover:text-blue-600 transition">
                                Edit
                            </a>
                            <form method="POST" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this article?');">
                                <input type="hidden" name="article_id" value="<?php echo $article['article_id']; ?>">
                                <button type="submit" name="delete_article"
                                        class="text-red-600 hover:text-red-700 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
