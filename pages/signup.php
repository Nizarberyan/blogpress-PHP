<?php
// pages/signup.php
require_once dirname(__DIR__) . '/src/controllers/AuthController.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $authController = new AuthController();

    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        if ($authController->register($username, $email, $password)) {
            header("Location: index.php?page=login");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-soft">
    <h2 class="text-2xl font-bold mb-6 text-center text-brand-blue">Create Account</h2>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-4">
            <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
            <input type="text" name="username" required
                class="shadow-soft appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-blue-100">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" name="email" required
                class="shadow-soft appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-blue-100">
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input type="password" name="password" required
                class="shadow-soft appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-blue-100">
        </div>

        <div class="mb-6">
            <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
            <input type="password" name="confirm_password" required
                class="shadow-soft appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-blue-100">
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-brand-blue text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                Sign Up
            </button>
            <a href="?page=login" class="text-brand-blue hover:text-blue-800 transition">
                Already have an account?
            </a>
        </div>
    </form>
</div>