<?php

define('IN_MANAGER_MODE', true);
define('MODX_API_MODE', true);

$coreRoot = dirname(__DIR__, 4);
include_once($coreRoot . '/index.php');

if (!function_exists('evo') || !evo()->isLoggedIn('mgr')) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

$settings = function_exists('config') ? config('cms.settings.eFilemanager', []) : [];
if (is_array($settings) && array_key_exists('enable', $settings) && !$settings['enable']) {
    http_response_code(404);
    echo 'File manager is disabled.';
    exit;
}

$type = isset($_GET['type']) ? (string)$_GET['type'] : 'files';
$type = strtolower($type);
$lfmType = ($type === 'images' || $type === 'image') ? 'Images' : 'Files';

if ($lfmType === 'Images' && !evo()->hasPermission('file_manager') && !evo()->hasPermission('assets_images')) {
    http_response_code(403);
    echo 'No permission for images.';
    exit;
}

if ($lfmType === 'Files' && !evo()->hasPermission('file_manager') && !evo()->hasPermission('assets_files')) {
    http_response_code(403);
    echo 'No permission for files.';
    exit;
}

$prefix = 'filemanager';
if (function_exists('config')) {
    $prefix = (string)config('lfm.url_prefix', $prefix);
}

$baseUrl = defined('MODX_BASE_URL') ? MODX_BASE_URL : '/';
$baseUrl = '/' . ltrim($baseUrl, '/');
$baseUrl = rtrim($baseUrl, '/');

$lfmUrl = $baseUrl . '/' . ltrim($prefix, '/');

$params = [
    'type' => $lfmType,
];

if (!empty($_GET['editor'])) {
    $params['editor'] = $_GET['editor'];
}

if (function_exists('public_path')) {
    $scriptPath = public_path('assets/vendor/laravel-filemanager/js/script.js');
    if (is_file($scriptPath)) {
        $params['v'] = (string)filemtime($scriptPath);
    }
}

$lfmUrl .= '?' . http_build_query($params);

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File Manager</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        iframe {
            border: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
<iframe src="<?php echo htmlspecialchars($lfmUrl, ENT_QUOTES, 'UTF-8'); ?>" allowfullscreen></iframe>
</body>
</html>
