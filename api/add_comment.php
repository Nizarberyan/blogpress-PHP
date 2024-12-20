<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/controllers/CommentController.php';

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
$content = trim($data['content'] ?? '');

// Validate input
if (!$article_id || empty($content)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Process the comment
$commentController = new CommentController();
$result = $commentController->createComment($article_id, $_SESSION['user_id'], $content);

if ($result) {
    // Get the updated comments
    $comments = $commentController->getComments($article_id, 1);
    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to add comment']);
}
