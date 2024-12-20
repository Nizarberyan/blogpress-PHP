<?php
require_once __DIR__ . '/../src/controllers/ArticleController.php';
require_once __DIR__ . '/../src/controllers/CommentController.php';

// Get article ID from URL
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$article_id) {
    header("Location: ?page=articles");
    exit();
}

// Initialize controller and get article
$articleController = new ArticleController();
$article = $articleController->getArticle($article_id);

// If article doesn't exist, redirect to articles page
if (!$article) {
    header("Location: ?page=articles");
    exit();
}

// Increment view count
$articleController->incrementViews($article_id);
?>

<div class="max-w-4xl mx-auto">
    <!-- Article Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            <?php echo htmlspecialchars($article['title']); ?>
        </h1>

        <div class="flex items-center justify-between text-brand-gray mb-6">
            <div class="flex items-center space-x-4">
                <span>By <?php echo htmlspecialchars($article['author_name']); ?></span>
                <span>•</span>
                <span><?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                <span>•</span>
                <span><?php echo $article['reading_time']; ?> min read</span>
            </div>
            <div class="flex items-center space-x-4">
                <span>👁 <?php echo $article['views_count']; ?> views</span>
                <span>❤️ <?php echo $article['likes_count']; ?> likes</span>
            </div>
        </div>

        <?php if ($isLoggedIn && $_SESSION['user_id'] == $article['author_id']): ?>
            <div class="flex space-x-3">
                <a href="?page=edit_article&id=<?php echo $article['article_id']; ?>"
                    class="text-brand-blue hover:text-blue-600 transition">
                    Edit Article
                </a>
                <form method="POST" class="inline"
                    onsubmit="return confirm('Are you sure you want to delete this article?');">
                    <input type="hidden" name="article_id" value="<?php echo $article['article_id']; ?>">
                    <button type="submit" name="delete_article"
                        class="text-red-600 hover:text-red-700 transition">
                        Delete Article
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Article Content -->
    <div class="bg-white rounded-lg shadow-soft p-8 mb-8">
        <div class="prose max-w-none">
            <?php echo $article['content_html']; ?>
        </div>
    </div>

    <!-- Article Footer -->
    <div class="flex justify-between items-center py-6 border-t border-gray-200">
        <a href="?page=articles"
            class="text-brand-blue hover:text-blue-600 transition">
            ← Back to Articles
        </a>
        <?php if ($isLoggedIn): ?>
            <button id="likeButton"
                class="flex items-center space-x-2 text-brand-gray hover:text-red-500 transition"
                onclick="likeArticle(<?php echo $article['article_id']; ?>)">
                <span>❤️</span>
                <span id="likeCount"><?php echo $article['likes_count']; ?></span>
            </button>
        <?php endif; ?>
    </div>

    <!-- Comments Section -->
    <div class="mt-8 border-t border-gray-200 pt-8">
        <h3 class="text-2xl font-bold mb-6">Comments</h3>

        <?php if ($isLoggedIn): ?>
            <div class="mb-8">
                <form id="commentForm" class="space-y-4">
                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                            Add a comment
                        </label>
                        <textarea id="comment" name="comment" rows="3"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-brand-blue transition"
                            required></textarea>
                    </div>
                    <button type="submit"
                        class="bg-brand-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">
                        Post Comment
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div id="comments-container">
            <?php
            $commentController = new CommentController();
            $comments = $commentController->getComments($article_id);
            foreach ($comments as $comment):
            ?>
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-medium text-brand-blue">
                            <?php echo htmlspecialchars($comment['username']); ?>
                        </div>
                        <div class="text-sm text-brand-gray">
                            <?php echo date('M j, Y', strtotime($comment['created_at'])); ?>
                        </div>
                    </div>
                    <div class="text-gray-700">
                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($isLoggedIn): ?>
        <script>
            document.getElementById('commentForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const content = document.getElementById('comment').value;

                fetch('api/add_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            article_id: <?php echo $article_id; ?>,
                            content: content
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Clear the form
                            document.getElementById('comment').value = '';

                            // Refresh comments
                            const container = document.getElementById('comments-container');
                            container.innerHTML = data.comments.map(comment => `
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="flex justify-between items-start mb-2">
                            <div class="font-medium text-brand-blue">
                                ${comment.username}
                            </div>
                            <div class="text-sm text-brand-gray">
                                ${new Date(comment.created_at).toLocaleDateString()}
                            </div>
                        </div>
                        <div class="text-gray-700">
                            ${comment.content.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                `).join('');
                        } else {
                            alert('Failed to add comment. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
            });
        </script>
    <?php endif; ?>
</div>

<?php if ($isLoggedIn): ?>
    <script>
        function likeArticle(articleId) {
            fetch('api/like_article.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        article_id: articleId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const likeCount = document.getElementById('likeCount');
                        const likeButton = document.getElementById('likeButton');
                        likeCount.textContent = data.likes;

                        // Toggle heart icon and color
                        if (likeButton.classList.contains('text-red-500')) {
                            likeButton.classList.remove('text-red-500');
                            likeButton.querySelector('span').textContent = '🤍';
                        } else {
                            likeButton.classList.add('text-red-500');
                            likeButton.querySelector('span').textContent = '❤️';
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
<?php endif; ?>