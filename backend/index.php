<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once './config/Database.php';

$database = new Database();
$conn = $database->connect();

// Simple routing
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'GET') {
    echo json_encode(['message' => 'Welcome to PHP Backend API']);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}
