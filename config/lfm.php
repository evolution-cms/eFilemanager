<?php

return [
    'use_package_routes'       => true,

    'middlewares'              => [
        'web',
        EvolutionCMS\eFilemanager\Http\Middleware\EvoManagerAuth::class,
    ],

    'url_prefix'               => 'filemanager',

    'allow_private_folder'     => true,

    'private_folder_name'      => UniSharp\LaravelFilemanager\Handlers\ConfigHandler::class,

    'allow_shared_folder'      => true,

    'shared_folder_name'       => 'shares',

    'folder_categories'        => [
        'file'  => [
            'folder_name'  => 'files',
            'startup_view' => 'list',
            'max_size'     => 50000,
            'thumb' => true,
            'thumb_width' => 80,
            'thumb_height' => 80,
            'valid_mime'   => [
                'image/jpeg',
                'image/pjpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
                'application/x-zip-compressed',
                'text/plain',
            ],
        ],
        'image' => [
            'folder_name'  => 'photos',
            'startup_view' => 'grid',
            'max_size'     => 50000,
            'thumb' => true,
            'thumb_width' => 80,
            'thumb_height' => 80,
            'valid_mime'   => [
                'image/jpeg',
                'image/pjpeg',
                'image/png',
                'image/gif',
                'image/webp',
            ],
        ],
    ],

    'paginator' => [
        'perPage' => 30,
    ],

    'disk'                     => 'public',

    'temporary_url_duration'   => 30,

    'rename_file'              => false,

    'rename_duplicates'        => false,

    'alphanumeric_filename'    => false,

    'alphanumeric_directory'   => false,

    'convert_to_alphanumeric'  => false,

    'should_validate_size'     => false,

    'should_validate_mime'     => true,

    'over_write_on_duplicate'  => false,

    'disallowed_mimetypes' => [
        'text/x-php',
        'application/x-php',
        'text/html',
        'application/javascript',
        'text/javascript',
    ],

    'disallowed_extensions' => ['php', 'html', 'htm', 'js'],

    'item_columns' => ['name', 'url', 'time', 'icon', 'is_file', 'is_image', 'thumb_url'],

    'is_reverse_view' => false,

    'should_create_thumbnails' => true,

    'thumb_folder_name'        => 'thumbs',

    'raster_mimetypes'         => [
        'image/jpeg',
        'image/pjpeg',
        'image/png',
    ],

    'thumb_img_width'          => 200,

    'thumb_img_height'         => 200,

    'file_type_array'          => [
        'pdf'  => 'Adobe Acrobat',
        'doc'  => 'Microsoft Word',
        'docx' => 'Microsoft Word',
        'xls'  => 'Microsoft Excel',
        'xlsx' => 'Microsoft Excel',
        'zip'  => 'Archive',
        'gif'  => 'GIF Image',
        'jpg'  => 'JPEG Image',
        'jpeg' => 'JPEG Image',
        'png'  => 'PNG Image',
        'webp' => 'WebP Image',
        'ppt'  => 'Microsoft PowerPoint',
        'pptx' => 'Microsoft PowerPoint',
    ],

    'php_ini_overrides'        => [
        'memory_limit' => '256M',
    ],

    'intervention_driver' => 'gd',
];
