<?php
$url = isset($_GET['url']) ? $_GET['url'] : '';
if (!$url) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

$cache_dir = dirname(__FILE__) . '/cache-images';
if (!is_dir($cache_dir)) {
    @mkdir($cache_dir, 0755, true);
}

$remote = 'https://farmapazvenezuela.com/' . ltrim($url, '/');
$cache_key = md5($url);
$cache_file = $cache_dir . '/' . $cache_key . '.img';

if (file_exists($cache_file) && time() - filemtime($cache_file) < 86400) {
    $data = file_get_contents($cache_file);
} else {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $remote,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; FarmapazBot/1.0)',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$data) {
        header('HTTP/1.1 404 Not Found');
        header('X-Proxy-Status: fetch_failed');
        exit;
    }
    @file_put_contents($cache_file, $data);
}

$ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
$mime = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'svg' => 'image/svg+xml',
];
header('Content-Type: ' . ($mime[$ext] ?? 'image/jpeg'));
header('Cache-Control: public, max-age=86400');
header('X-Proxy-Status: cached');
echo $data;
