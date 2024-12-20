<?php
require_once __DIR__ . '/../src/utils/MarkdownParser.php';

header('Content-Type: application/json');


$data = json_decode(file_get_contents('php://input'), true);
$content = $data['content'] ?? '';

$parser = MarkdownParser::getInstance();
$html = $parser->parse($content);


echo json_encode(['html' => $html]); 