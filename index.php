<?php

session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlogPress - Your Modern Blogging Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    boxShadow: {
                        'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
                    },
                    colors: {
                        'brand-blue': '#4285F4',
                        'brand-gray': '#5F6368'
                    },
                    typography: {
                        DEFAULT: {
                            css: {
                                maxWidth: 'none',
                                color: '#333',
                                a: {
                                    color: '#4285F4',
                                    '&:hover': {
                                        color: '#1967D2',
                                    },
                                },
                            },
                        },
                    },
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <header class="bg-white shadow-soft border-b border-gray-100">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-brand-blue">BlogPress</h1>
                <nav class="hidden md:flex space-x-4">
                    <a href="?page=home" class="text-brand-gray hover:text-brand-blue transition px-3 py-2 rounded-lg hover:bg-blue-50">Home</a>
                    <a href="?page=articles" class="text-brand-gray hover:text-brand-blue transition px-3 py-2 rounded-lg hover:bg-blue-50">Articles</a>
                    <a href="?page=analytics" class="text-brand-gray hover:text-brand-blue transition px-3 py-2 rounded-lg hover:bg-blue-50">Analytics</a>
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                <?php if (!$isLoggedIn): ?>
                    <a href="?page=login" class="text-brand-gray hover:text-brand-blue transition px-4 py-2 rounded-full border border-transparent hover:border-blue-100 hover:bg-blue-50">Login</a>
                    <a href="?page=signup" class="bg-brand-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition shadow-sm">Sign Up</a>
                <?php else: ?>
                    <a href="?page=dashboard" class="text-brand-gray hover:text-brand-blue transition px-4 py-2 rounded-full">Dashboard</a>
                    <a href="?page=logout" class="bg-brand-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition shadow-sm">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </header>


    <main class="container mx-auto px-4 py-16">
        <?php

        switch ($page) {
            case 'home':
                include 'pages/home.php';
                break;
            case 'articles':
                include 'pages/articles.php';
                break;
            case 'analytics':
                include 'pages/analytics.php';
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
            case 'logout':
                session_destroy();
                header("Location: index.php");
                exit();
            case 'create_article':
                include 'pages/create_article.php';
                break;
            case 'edit_article':
                include 'pages/edit_article.php';
                break;
            case 'article':
                include 'pages/article.php';
                break;
            default:
                include 'pages/home.php';
        }
        ?>
    </main>

    <footer class="bg-gray-100 py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="text-brand-gray">&copy; <?php echo date('Y'); ?> Nizar Beriane. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>


