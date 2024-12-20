<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlogPress - Your Modern Blogging Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .mobile-menu {
            display: none;
        }

        @media (max-width: 768px) {
            .desktop-nav {
                display: none;
            }

            .mobile-menu {
                display: block;
            }

            .mobile-menu-items {
                display: none;
                position: absolute;
                top: 64px;
                left: 0;
                right: 0;
                background: white;
                border-bottom: 1px solid #eee;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .mobile-menu-items.active {
                display: block;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <header class="bg-white shadow-soft border-b border-gray-100 relative">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-brand-blue">BlogPress</h1>
                    <!-- Desktop Navigation -->
                    <nav class="desktop-nav space-x-4">
                        <a href="?page=home" class="text-brand-gray hover:text-brand-blue transition px-3 py-2 rounded-lg hover:bg-blue-50">Home</a>
                        <a href="?page=articles" class="text-brand-gray hover:text-brand-blue transition px-3 py-2 rounded-lg hover:bg-blue-50">Articles</a>
                        <?php if ($isLoggedIn): ?>
                            <a href="?page=analytics" class="text-brand-gray hover:text-brand-blue transition px-3 py-2 rounded-lg hover:bg-blue-50">Analytics</a>
                        <?php endif; ?>
                    </nav>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="mobile-menu p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Desktop Auth Buttons -->
                <div class="desktop-nav flex items-center space-x-4">
                    <?php if (!$isLoggedIn): ?>
                        <a href="?page=login" class="text-brand-gray hover:text-brand-blue transition px-4 py-2 rounded-full border border-transparent hover:border-blue-100 hover:bg-blue-50">Login</a>
                        <a href="?page=signup" class="bg-brand-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition shadow-sm">Sign Up</a>
                    <?php else: ?>
                        <a href="?page=dashboard" class="text-brand-gray hover:text-brand-blue transition px-4 py-2 rounded-full">Dashboard</a>
                        <a href="?page=logout" class="bg-brand-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition shadow-sm">Logout</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Menu Items -->
            <div id="mobile-menu-items" class="mobile-menu-items">
                <div class="px-4 py-2 space-y-2">
                    <a href="?page=home" class="block px-3 py-2 rounded-md text-brand-gray hover:text-brand-blue hover:bg-blue-50">Home</a>
                    <a href="?page=articles" class="block px-3 py-2 rounded-md text-brand-gray hover:text-brand-blue hover:bg-blue-50">Articles</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="?page=analytics" class="block px-3 py-2 rounded-md text-brand-gray hover:text-brand-blue hover:bg-blue-50">Analytics</a>
                        <a href="?page=dashboard" class="block px-3 py-2 rounded-md text-brand-gray hover:text-brand-blue hover:bg-blue-50">Dashboard</a>
                        <a href="?page=logout" class="block px-3 py-2 rounded-md text-brand-gray hover:text-brand-blue hover:bg-blue-50">Logout</a>
                    <?php else: ?>
                        <a href="?page=login" class="block px-3 py-2 rounded-md text-brand-gray hover:text-brand-blue hover:bg-blue-50">Login</a>
                        <a href="?page=signup" class="block px-3 py-2 rounded-md text-brand-gray hover:text-brand-blue hover:bg-blue-50">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenuItems = document.getElementById('mobile-menu-items');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenuItems.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (event) => {
            if (!mobileMenuButton.contains(event.target) && !mobileMenuItems.contains(event.target)) {
                mobileMenuItems.classList.remove('active');
            }
        });
    </script>
</body>

</html>