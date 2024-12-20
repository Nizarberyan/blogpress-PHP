<?php
// pages/login.php
require_once __DIR__ . '/../src/controllers/AuthController.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $authController = new AuthController();

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($authController->login($username, $password)) {
        header("Location: index.php?page=dashboard");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-soft">
    <h2 class="text-2xl font-bold mb-6 text-center text-brand-blue">Login</h2>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-4">
            <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
            <input type="text" name="username" required
                class="shadow-soft appearance-none border rounded-full w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:border-brand-blue">
        </div>

        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input type="password" name="password" required
                class="shadow-soft appearance-none border rounded-full w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:border-brand-blue">
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-brand-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">
                Sign In
            </button>
            <a href="?page=signup" class="text-brand-blue hover:text-blue-800 transition">
                Create Account
            </a>
        </div>
    </form>
</div>