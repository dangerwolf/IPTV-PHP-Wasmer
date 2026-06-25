<?php
// index.php — 统一路由入口

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

$routes = [
    ''         => 'yspios.php',
    'yspios'   => 'yspios.php',
    'channels' => null,  // 特殊处理
];

// 频道列表 API
if ($path === 'channels') {
    require_once __DIR__ . '/channels.php';

    $type = $_GET['type'] ?? null;

    // 生成播放链接列表
    $result = [];
    foreach ($channels as $key => $val) {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

        $item = [
            'id'        => $key,
            'name'      => $val[3],
            'quality'   => $val[2],
            'play_url'  => "{$scheme}://{$_SERVER['HTTP_HOST']}/?id={$key}",
        ];

        // 如果传了 type=cctv，只返回央视；type=local 只返回地方台
        if ($type === 'cctv' && strpos($key, 'cctv') !== 0 && strpos($key, 'cgtn') !== 0) {
            continue;
        }
        if ($type === 'local' && (strpos($key, 'cctv') === 0 || strpos($key, 'cgtn') === 0)) {
            continue;
        }
        if ($type === 'premium' && $val[2] !== 'shd') {
            continue;
        }

        $result[] = $item;
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode([
        'total'    => count($result),
        'channels' => $result,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// 其他路由
if (isset($routes[$path]) && $routes[$path] !== null && file_exists(__DIR__ . '/' . $routes[$path])) {
    include __DIR__ . '/' . $routes[$path];
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
