<?php
// index.php — 统一路由入口

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

// 路由表：URL路径 => 对应的PHP文件
$routes = [
    ''            => 'yspios.php',   // 根路径默认走 yspios
    'yspios'      => 'yspios.php',   // /yspios
    'api'         => 'api.php',      // /api
    'admin'       => 'admin.php',    // /admin
];

if (isset($routes[$path]) && file_exists(__DIR__ . '/' . $routes[$path])) {
    include __DIR__ . '/' . $routes[$path];
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
