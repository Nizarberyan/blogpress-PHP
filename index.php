<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/controllers/AuthController.php';
require_once __DIR__ . '/src/controllers/ArticleController.php';
require_once __DIR__ . '/src/controllers/AnalyticsController.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Get current page from URL parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Handle article deletion
if ($isLoggedIn && isset($_POST['delete_article'])) {
    $articleController = new ArticleController();
    $article_id = $_POST['article_id'];
    if ($articleController->deleteArticle($article_id, $_SESSION['user_id'])) {
        header("Location: ?page=articles&deleted=1");
        exit();
    }
}

// Handle all possible redirects before any output
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $page === 'login') {
    $authController = new AuthController();
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($authController->login($username, $password)) {
        header("Location: index.php?page=dashboard");
        exit();
    }
}

if ($page === 'dashboard' && !$isLoggedIn) {
    header('Location: ?page=login');
    exit;
} elseif ($page === 'logout') {
    session_destroy();
    header('Location: ?page=home');
    exit;
}

// Add this after line 28 and before line 31
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && $page === 'create_article') {
    $articleController = new ArticleController();
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $categories = $_POST['categories'] ?? [];
    $image_url = '';

    if (!empty($title) && !empty($content)) {
        if ($articleController->createArticle($title, $content, $_SESSION['user_id'], $status, $image_url, $categories)) {
            header("Location: ?page=articles&created=1");
            exit();
        }
    }
}

// Add this after line 49 and before the DOCTYPE HTML (line 69)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $page === 'signup') {
    $authController = new AuthController();
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password === $confirm_password && strlen($password) >= 6) {
        if ($authController->register($username, $email, $password)) {
            header("Location: ?page=login");
            exit();
        }
    }
}

// Rest of your HTML code starts here
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlogPress - Your Modern Blogging Platform</title>
    <link rel="icon" type="png" href="public\assets\images\icons\favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-blue': '#4285F4',
                        'brand-gray': '#666666',
                    },
                    boxShadow: {
                        'soft': '0 2px 15px rgba(0, 0, 0, 0.05)',
                    },
                },
            },
        }
    </script>
    <style type="text/css">
        .menu-items {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 300px;
            background: white;
            box-shadow: -2px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 50;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }

        .menu-items.active {
            display: block;
            transform: translateX(0);
        }

        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 40;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .menu-overlay.active {
            display: block;
            opacity: 1;
        }

        .prose {
            max-width: 65ch;
            color: #374151;
        }

        .prose p {
            margin-top: 1.25em;
            margin-bottom: 1.25em;
        }

        .prose h2 {
            font-size: 1.5em;
            margin-top: 2em;
            margin-bottom: 1em;
            font-weight: 700;
        }

        .prose pre {
            background-color: #f3f4f6;
            padding: 1em;
            border-radius: 0.375rem;
            overflow-x: auto;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <!-- Menu Overlay -->
    <div id="menu-overlay" class="menu-overlay"></div>

    <header class="bg-white shadow-soft border-b border-gray-100 relative">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="?page=home" class="text-2xl font-bold text-brand-blue">BlogPress</a>

                <!-- Menu Button -->
                <button id="menu-button" class="p-2 rounded-lg hover:bg-gray-100 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Slide-out Menu -->
    <div id="menu-items" class="menu-items">
        <div class="p-6">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-xl font-bold text-gray-900">Menu</h2>
                <button id="close-menu" class="p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav class="space-y-4">
                <a href="?page=home" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-brand-blue transition">Home</a>
                <a href="?page=articles" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-brand-blue transition">Articles</a>
                <?php if ($isLoggedIn): ?>
                    <a href="?page=dashboard" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-brand-blue transition">Dashboard</a>
                    <a href="?page=analytics" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-brand-blue transition">Analytics</a>
                    <hr class="my-4 border-gray-200">
                    <a href="?page=logout" class="block px-4 py-2 rounded-lg bg-brand-blue text-white hover:bg-blue-600 transition">Logout</a>
                <?php else: ?>
                    <hr class="my-4 border-gray-200">
                    <a href="?page=login" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-brand-blue transition">Login</a>
                    <a href="?page=signup" class="block px-4 py-2 rounded-lg bg-brand-blue text-white hover:bg-blue-600 transition">Sign Up</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <main class="container mx-auto px-4 py-16">
        <?php
        switch ($page) {
            case 'home':
                include 'pages/home.php';
                break;
            case 'login':
                include 'pages/login.php';
                break;
            case 'signup':
                include 'pages/signup.php';
                break;
            case 'dashboard':
                include 'pages/dashboard.php';
                break;
            case 'articles':
                include 'pages/articles.php';
                break;
            case 'article':
                include 'pages/article.php';
                break;
            case 'create_article':
                include 'pages/create_article.php';
                break;
            case 'edit_article':
                include 'pages/edit_article.php';
                break;
            case 'analytics':
                include 'pages/analytics.php';
                break;
            case 'logout':
                session_destroy();
                header('Location: ?page=home');
                exit;
                break;
            default:
                include 'pages/home.php';
        }
        ?>
    </main>

    <script>
        const menuButton = document.getElementById('menu-button');
        const closeButton = document.getElementById('close-menu');
        const menuItems = document.getElementById('menu-items');
        const menuOverlay = document.getElementById('menu-overlay');

        function openMenu() {
            menuItems.classList.add('active');
            menuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            menuItems.classList.remove('active');
            menuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        menuButton.addEventListener('click', openMenu);
        closeButton.addEventListener('click', closeMenu);
        menuOverlay.addEventListener('click', closeMenu);
    </script>
</body>

</html>