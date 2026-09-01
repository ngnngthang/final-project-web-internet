<?php

use App\Core\Request;
use App\Core\Router;

require __DIR__ . '/../app/autoload.php';

session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new Router();
require __DIR__ . '/../routes/api.php';

$request = new Request();
$response = $router->dispatch($request);
$response->send();
