<?php
require_once __DIR__ . '/../src/controllers/ArticleController.php';

// Initialize controller
$articleController = new ArticleController();

// Get current page from URL parameter
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;

// Get articles
$articles = $articleController->getArticles($current_page);

// Get total pages with error handling
try {
    $total_pages = $articleController->getTotalPages();
} catch (Exception $e) {
    error_log("Error getting total pages: " . $e->getMessage());
    $total_pages = 1;
}

// Handle article deletion if user is logged in
if ($isLoggedIn && isset($_POST['delete_article'])) {
    $article_id = $_POST['article_id'];
    if ($articleController->deleteArticle($article_id, $_SESSION['user_id'])) {
        header("Location: ?page=articles&deleted=1");
        exit();
    }
}
?>

<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Articles</h1>
        <?php if ($isLoggedIn): ?>
            <a href="?page=create_article"
                class="bg-brand-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">
                Create New Article
            </a>
        <?php endif; ?>
    </div>

    <!-- Articles Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($articles as $article): ?>
            <div class="bg-white rounded-lg shadow-soft overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-2">
                        <a href="?page=article&id=<?php echo $article['article_id']; ?>"
                            class="text-gray-900 hover:text-brand-blue transition">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                    </h2>

                    <p class="text-brand-gray mb-4">
                        <?php echo substr(strip_tags($article['content']), 0, 150) . '...'; ?>
                    </p>

                    <div class="flex justify-between items-center text-sm text-brand-gray">
                        <span>By <?php echo htmlspecialchars($article['author_name']); ?></span>
                        <span><?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                    </div>

                    <?php if ($isLoggedIn && $_SESSION['user_id'] == $article['author_id']): ?>
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
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                        <?php
                        $isLiked = $articleController->isLikedByUser($article['article_id'], $_SESSION['user_id']);
                        ?>
                        <button id="likeButton"
                            class="flex items-center space-x-2 text-brand-gray hover:text-red-500 transition <?php echo $isLiked ? 'text-red-500' : ''; ?>"
                            onclick="likeArticle(<?php echo $article['article_id']; ?>)">
                            <span><?php echo $isLiked ? '❤️' : '🤍'; ?></span>
                            <span id="likeCount"><?php echo $article['likes_count']; ?></span>
                        </button>
                    <?php endif; ?>

                    <div class="flex flex-wrap gap-2 mt-2">
                        <?php if (!empty($article['categories']) && is_array($article['categories'])): ?>
                            <?php foreach ($article['categories'] as $category): ?>
                                <?php if (isset($category['name'])): ?>
                                    <span class="inline-block bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-sm">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-8 flex justify-center space-x-2">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=articles&p=<?php echo $i; ?>"
                    class="<?php echo $current_page == $i ? 'bg-brand-blue text-white' : 'bg-white text-brand-gray hover:bg-gray-50'; ?> 
                          px-4 py-2 rounded-full transition">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>