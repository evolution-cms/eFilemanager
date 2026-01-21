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
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/lfm.php', 'lfm');

        if ($this->app->runningInConsole()) {
            $this->publishResources();
        }

        $this->app->booted(function () {
            if ($this->app->runningInConsole()) {
                $this->flattenPublishDirectories();
            }
        });
    }

    protected function publishResources(): void
    {
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
        $lfmFiles = $this->collectPublishFiles($lfmPublic, public_path('assets/vendor/laravel-filemanager'));
        if ($lfmFiles !== []) {
            $this->publishes($lfmFiles, 'efilemanager-lfm-assets');
        }
    }

    protected function collectPublishFiles(string $sourceDir, string $targetDir): array
    {
        if (!is_dir($sourceDir)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS)
        );

        $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR);
        $targetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $path = $file->getPathname();
            $relative = substr($path, strlen($sourceDir) + 1);
            $files[$path] = $targetDir . DIRECTORY_SEPARATOR . $relative;
        }

        return $files;
    }

    protected function flattenPublishDirectories(): void
    {
        if (!class_exists(\Illuminate\Support\ServiceProvider::class)) {
            return;
        }

        $reflection = new \ReflectionClass(\Illuminate\Support\ServiceProvider::class);
        $publishesProperty = $reflection->getProperty('publishes');
        $publishesProperty->setAccessible(true);
        $publishGroupsProperty = $reflection->getProperty('publishGroups');
        $publishGroupsProperty->setAccessible(true);

        $publishes = $publishesProperty->getValue();
        $publishGroups = $publishGroupsProperty->getValue();

        foreach ($publishes as $provider => $paths) {
            $publishes[$provider] = $this->expandPublishPaths($paths);
        }

        foreach ($publishGroups as $group => $paths) {
            $publishGroups[$group] = $this->expandPublishPaths($paths);
        }

        $publishesProperty->setValue(null, $publishes);
        $publishGroupsProperty->setValue(null, $publishGroups);
    }

    protected function expandPublishPaths(array $paths): array
    {
        $expanded = [];

        foreach ($paths as $from => $to) {
            if (is_dir($from)) {
                $files = $this->collectPublishFiles($from, $to);
                if ($files !== []) {
                    $expanded = array_merge($expanded, $files);
                    continue;
                }
            }
            $expanded[$from] = $to;
        }

        return $expanded;
    }
}
