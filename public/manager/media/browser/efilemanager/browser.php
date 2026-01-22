<?php

$type = '';
if (isset($_GET['type'])) {
    $type = trim((string)$_GET['type']);
} elseif (isset($_GET['Type'])) {
    $type = trim((string)$_GET['Type']);
}

$type = strtolower($type);
if ($type === '') {
    $type = 'images';
}
if ($type === 'image') {
    $type = 'images';
}
if ($type === 'file') {
    $type = 'files';
}

$editor = '';
if (isset($_GET['editor'])) {
    $editor = trim((string)$_GET['editor']);
}

$query = 'type=' . rawurlencode($type) . '&callback=EvoFilemanagerCallback';
if ($editor !== '') {
    $query .= '&editor=' . rawurlencode($editor);
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>File Browser</title>
    <script>
        window.location.replace('browse.php?<?php echo $query; ?>');
    </script>
</head>
<body></body>
</html>
