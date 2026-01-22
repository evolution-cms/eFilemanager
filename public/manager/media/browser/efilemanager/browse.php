<?php

define('IN_MANAGER_MODE', true);
define('MODX_API_MODE', true);

$coreRoot = dirname(__DIR__, 4);
include_once($coreRoot . '/index.php');

if (!isset($_SESSION['mgrValidated'])) {
    echo '<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the EVO Content Manager instead of accessing this file directly.';
    exit;
}

$evo = null;
if (function_exists('EvolutionCMS')) {
    $evo = EvolutionCMS();
} elseif (function_exists('evo')) {
    $evo = evo();
}

if ($evo && method_exists($evo, 'setContext')) {
    $evo->setContext('mgr');
}

$settings = function_exists('config') ? config('cms.settings.eFilemanager', []) : [];
if (is_array($settings) && array_key_exists('enable', $settings) && !$settings['enable']) {
    http_response_code(404);
    echo 'File manager is disabled.';
    exit;
}
$urlStrategy = 'relative';
if (is_array($settings) && isset($settings['url_strategy'])) {
    $urlStrategy = (string)$settings['url_strategy'];
}
if ($urlStrategy !== 'absolute') {
    $urlStrategy = 'relative';
}

$typeParam = isset($_GET['type']) ? strtolower((string)$_GET['type']) : '';
if ($typeParam === '' && isset($_GET['Type'])) {
    $typeParam = strtolower((string)$_GET['Type']);
}
$lfmType = ($typeParam === 'images' || $typeParam === 'image') ? 'Images' : 'Files';

$callbackName = '';
if (!empty($_GET['callback'])) {
    $callbackName = preg_replace('/[^A-Za-z0-9_]/', '', (string)$_GET['callback']);
}

if ($evo && $typeParam !== '') {
    $hasFileManager = (bool)$evo->hasPermission('file_manager', 'mgr');
    $hasImages = (bool)$evo->hasPermission('assets_images', 'mgr');
    $hasFiles = (bool)$evo->hasPermission('assets_files', 'mgr');

    if (($typeParam === 'images' || $typeParam === 'image') && !$hasFileManager && !$hasImages) {
        http_response_code(403);
        echo 'No permission for images.';
        exit;
    }

    if (($typeParam === 'files' || $typeParam === 'file') && !$hasFileManager && !$hasFiles) {
        http_response_code(403);
        echo 'No permission for files.';
        exit;
    }
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

if ($callbackName !== '') {
    $params['callback'] = $callbackName;
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

$siteUrl = '';
if (defined('MODX_SITE_URL')) {
    $siteUrl = MODX_SITE_URL;
} elseif ($evo && method_exists($evo, 'getConfig')) {
    $siteUrl = (string)$evo->getConfig('site_url');
}
$siteUrl = rtrim((string)$siteUrl, '/');

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File Manager</title>
<?php if ($callbackName !== ''): ?>
    <script>
        var eFilemanagerUrlStrategy = <?php echo json_encode($urlStrategy); ?>;
        var eFilemanagerSiteUrl = <?php echo json_encode($siteUrl); ?>;
        window.<?php echo $callbackName; ?> = function (items) {
            var url = '';
            if (typeof items === 'string') {
                url = items;
            } else if (Array.isArray(items) && items.length > 0) {
                url = items[0] && (items[0].url || items[0].path || items[0].file) ? (items[0].url || items[0].path || items[0].file) : '';
            } else if (items && items.url) {
                url = items.url;
            }

            if (eFilemanagerUrlStrategy === 'relative' && url) {
                if (eFilemanagerSiteUrl && url.indexOf(eFilemanagerSiteUrl) === 0) {
                    url = url.slice(eFilemanagerSiteUrl.length);
                }
                if (window.location && window.location.origin && url.indexOf(window.location.origin) === 0) {
                    url = url.slice(window.location.origin.length);
                }
                if (url && url.charAt(0) !== '/') {
                    url = '/' + url;
                }
            }

            if (url && window.opener && typeof window.opener.SetUrl === 'function') {
                window.opener.SetUrl(url);
            }

            if (window.close) {
                window.close();
            }
        };
    </script>
<?php endif; ?>
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
