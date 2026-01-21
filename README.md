# eFilemanager

UniSharp Laravel Filemanager integration for Evolution CMS.

## Requirements
- PHP 8.3+
- Evolution CMS 3.5.2+

## Install
From the `core` directory:

php artisan package:installrequire evolution-cms/efilemanager "*"

## Publish config and assets
Publish everything:

php artisan vendor:publish --provider="EvolutionCMS\\eFilemanager\\eFilemanagerServiceProvider"

Or publish by tag:

php artisan vendor:publish --provider="EvolutionCMS\\eFilemanager\\eFilemanagerServiceProvider" --tag=efilemanager-config
php artisan vendor:publish --provider="EvolutionCMS\\eFilemanager\\eFilemanagerServiceProvider" --tag=efilemanager-lfm-config
php artisan vendor:publish --provider="EvolutionCMS\\eFilemanager\\eFilemanagerServiceProvider" --tag=efilemanager-lfm-assets
php artisan vendor:publish --provider="EvolutionCMS\\eFilemanager\\eFilemanagerServiceProvider" --tag=efilemanager-bridge
php artisan vendor:publish --provider="EvolutionCMS\\eFilemanager\\eFilemanagerServiceProvider" --tag=efilemanager-lfm-view

## Default file browser
When enabled, eFilemanager will set `which_browser` to `efilemanager` once if the current value is `mcpuk`.
This is controlled by `auto_set_default` in `core/custom/config/cms/settings/eFilemanager.php`.

## Config
- Evo settings: `core/custom/config/cms/settings/eFilemanager.php`
- LFM config: `core/custom/config/lfm.php`

Key settings:
- `enable`: enable/disable eFilemanager integration.
- `allow_mcpuk_fallback`: allow legacy fallback for TinyMCE < 5 only.
- `permissions`: per-type browse/manage permissions.
- `acl.allow_manage`: optional toggle to restrict upload/delete/rename.

## Notes
- LFM runs on `/{url_prefix}` (default `filemanager`).
- The manager browser wrapper lives at `manager/media/browser/efilemanager/browse.php`.
