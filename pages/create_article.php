<?php
require_once __DIR__ . '/../src/controllers/ArticleController.php';
require_once __DIR__ . '/../src/controllers/CategoryController.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: ?page=login");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $image_url = '';

    // Basic validation
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($content)) {
        $errors[] = "Content is required";
    }
}
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create New Article</h1>
        <p class="text-brand-gray mt-2">Share your thoughts with the world.</p>
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
                value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-brand-blue transition"
                required>
        </div>

        <div class="mb-6">
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                Content
            </label>
            <!-- Markdown Toolbar -->
            <div class="flex space-x-2 mb-2">
                <button type="button" onclick="insertMarkdown('bold')"
                    class="px-2 py-1 text-sm border rounded hover:bg-gray-50">B</button>
                <button type="button" onclick="insertMarkdown('italic')"
                    class="px-2 py-1 text-sm border rounded hover:bg-gray-50">I</button>
                <button type="button" onclick="insertMarkdown('heading')"
                    class="px-2 py-1 text-sm border rounded hover:bg-gray-50">H</button>
                <button type="button" onclick="insertMarkdown('link')"
                    class="px-2 py-1 text-sm border rounded hover:bg-gray-50">🔗</button>
                <button type="button" onclick="insertMarkdown('image')"
                    class="px-2 py-1 text-sm border rounded hover:bg-gray-50">📷</button>
                <button type="button" onclick="insertMarkdown('code')"
                    class="px-2 py-1 text-sm border rounded hover:bg-gray-50">{'</>'}</button>
                <button type="button" onclick="insertMarkdown('list')"
                    class="px-2 py-1 text-sm border rounded hover:bg-gray-50">•</button>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <textarea id="content"
                        name="content"
                        rows="12"
                        onkeyup="updatePreview()"
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-brand-blue transition"
                        required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                </div>
                <div>
                    <div id="preview" class="prose w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 h-[288px] overflow-y-auto">
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                Status
            </label>
            <select id="status"
                name="status"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-brand-blue transition">
                <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] === 'draft') ? 'selected' : ''; ?>>
                    Draft
                </option>
                <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] === 'published') ? 'selected' : ''; ?>>
                    Published
                </option>
            </select>
        </div>

        <div class="mb-6">
            <label for="categories" class="block text-sm font-medium text-gray-700 mb-2">
                Categories
            </label>
            <div class="grid grid-cols-2 gap-4">
                <?php
                $categoryController = new CategoryController();
                $categories = $categoryController->getAllCategories();
                foreach ($categories as $category):
                ?>
                    <label class="inline-flex items-center">
                        <input type="checkbox"
                            name="categories[]"
                            value="<?php echo $category['category_id']; ?>"
                            class="rounded border-gray-300 text-brand-blue focus:border-brand-blue focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2"><?php echo htmlspecialchars($category['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex justify-between items-center">
            <button type="submit"
                class="bg-brand-blue text-white px-6 py-2 rounded-full hover:bg-blue-600 transition">
                Create Article
            </button>
            <a href="?page=articles"
                class="text-brand-gray hover:text-brand-blue transition">
                Cancel
            </a>
        </div>
    </form>
</div>

<!-- Include Markdown editor script -->
<script src="public/js/markdown-editor.js"></script>

<!-- Initialize preview -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        updatePreview();
    });
</script>