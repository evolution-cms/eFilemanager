<?php return [
    'enable' => true,
    'auto_set_default' => true,
    'auto_set_default_flag' => 'efilemanager_default_set',
    'allow_mcpuk_fallback' => false,
    'url_strategy' => 'relative',
    'allow_signed_urls' => false,
    'permissions' => [
        'browse_images' => 'assets_images',
        'browse_files' => 'assets_files',
        'manage_images' => 'assets_images',
        'manage_files' => 'assets_files',
    ],
    'acl' => [
        'allow_browse' => true,
        'allow_manage' => true,
    ],
];
