<?php
require_once __DIR__ . '/../src/controllers/ArticleController.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: ?page=login");
    exit();
}

// Get article ID from URL
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$article_id) {
    header("Location: ?page=articles");
    exit();
}

// Initialize controller and get article
$articleController = new ArticleController();
$article = $articleController->getArticle($article_id);

// If article doesn't exist or user is not the author, redirect
if (!$article || $_SESSION['user_id'] != $article['author_id']) {
    header("Location: ?page=articles");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    // Basic validation
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($content)) {
        $errors[] = "Content is required";
    }

    // If no errors, update the article
    if (empty($errors)) {
        if ($articleController->updateArticle($article_id, $title, $content, $_SESSION['user_id'])) {
            header("Location: ?page=article&id=" . $article_id . "&updated=1");
            exit();
        } else {
            $errors[] = "Failed to update article";
        }
    }
}
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Article</h1>
        <p class="text-brand-gray mt-2">Update your article content.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white shadow-soft rounded-lg p-6">
        <div class="mb-6">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                Title
            </label>
            <input type="text"
                id="title"
                name="title"
                value="<?php echo htmlspecialchars($article['title']); ?>"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-brand-blue transition"
                required>
        </div>

        <div class="mb-6">
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                Content
            </label>
            <textarea id="content"
                name="content"
                rows="12"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-brand-blue transition"
                required><?php echo htmlspecialchars($article['content']); ?></textarea>
        </div>

        <div class="mb-6">
            <label for="categories" class="block text-sm font-medium text-gray-700 mb-2">
                Categories
            </label>
            <div class="grid grid-cols-2 gap-4">
                <?php
                $categoryController = new CategoryController();
                $categories = $categoryController->getAllCategories();
                $articleCategories = $article->getCategories();
                $selectedCategories = array_column($articleCategories, 'category_id');

                foreach ($categories as $category):
                ?>
                    <label class="inline-flex items-center">
                        <input type="checkbox"
                            name="categories[]"
                            value="<?php echo $category['category_id']; ?>"
                            <?php echo in_array($category['category_id'], $selectedCategories) ? 'checked' : ''; ?>
                            class="rounded border-gray-300 text-brand-blue focus:border-brand-blue focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2"><?php echo htmlspecialchars($category['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex justify-between items-center">
            <button type="submit"
                class="bg-brand-blue text-white px-6 py-2 rounded-full hover:bg-blue-600 transition">
                Update Article
            </button>
            <a href="?page=article&id=<?php echo $article_id; ?>"
                class="text-brand-gray hover:text-brand-blue transition">
                Cancel
            </a>
        </div>
    </form>
</div>