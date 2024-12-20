<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/controllers/ArticleController.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get the request body
$data = json_decode(file_get_contents('php://input'), true);
$article_id = $data['article_id'] ?? 0;

if (!$article_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Article ID is required']);
    exit;
}

// Process the like
$articleController = new ArticleController();
$result = $articleController->toggleLike($article_id, $_SESSION['user_id']);

if ($result) {
    // Get updated like count
    $likes = $articleController->getLikeCount($article_id);
    echo json_encode([
        'success' => true,
        'likes' => $likes
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to process like']);
}
