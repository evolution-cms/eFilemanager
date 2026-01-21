<?php namespace EvolutionCMS\eFilemanager;

use EvolutionCMS\ServiceProvider;

class eFilemanagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->loadPluginsFrom(dirname(__DIR__) . '/plugins/');
    }

    public function boot()
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/eFilemanagerCheck.php', 'cms.settings');
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/eFilemanagerSettings.php', 'cms.settings.eFilemanager');

        $this->publishes([
            dirname(__DIR__) . '/config/eFilemanagerSettings.php' => config_path('cms/settings/eFilemanager.php', true),
        ], 'efilemanager-config');

        $this->publishes([
            dirname(__DIR__) . '/config/lfm.php' => config_path('lfm.php', true),
        ], 'efilemanager-lfm-config');

        $this->publishes([
            dirname(__DIR__) . '/public/manager/media/browser/efilemanager/browse.php' => public_path('manager/media/browser/efilemanager/browse.php'),
        ], 'efilemanager-bridge');

        $this->publishes([
            dirname(__DIR__) . '/views/laravel-filemanager/index.blade.php' => base_path('views/vendor/laravel-filemanager/index.blade.php'),
        ], 'efilemanager-lfm-view');

        $lfmPublic = base_path('vendor/unisharp/laravel-filemanager/public');
        if (is_dir($lfmPublic)) {
            $this->publishes([
                $lfmPublic => public_path('assets/vendor/laravel-filemanager'),
            ], 'efilemanager-lfm-assets');
        }
    }
}
