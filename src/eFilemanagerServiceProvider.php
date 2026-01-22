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

        $managerPath = $this->resolveManagerPath();
        $this->publishes([
            dirname(__DIR__) . '/public/manager/media/browser/efilemanager/browse.php' => $managerPath . '/media/browser/efilemanager/browse.php',
            dirname(__DIR__) . '/public/manager/media/browser/efilemanager/browser.php' => $managerPath . '/media/browser/efilemanager/browser.php',
        ], 'efilemanager-bridge');

        $this->publishes([
            dirname(__DIR__) . '/views/laravel-filemanager/index.blade.php' => base_path('views/vendor/laravel-filemanager/index.blade.php'),
        ], 'efilemanager-lfm-view');

        $langRoot = $this->resolveLangVendorPath('laravel-filemanager');
        $langSource = dirname(__DIR__) . '/lang/vendor/laravel-filemanager';
        if (is_dir($langSource)) {
            $langFiles = $this->collectPublishFiles($langSource, $langRoot);
            if ($langFiles !== []) {
                $this->publishes($langFiles, 'efilemanager-lfm-lang');
            }
        }

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

    protected function resolveManagerPath(): string
    {
        $managerPath = $this->resolveManagerPathFromConfig();
        if ($managerPath !== null) {
            return $managerPath;
        }

        $managerUrl = $this->resolveManagerUrlFromConfig();
        if ($managerUrl !== null) {
            return $managerUrl;
        }

        return public_path('manager');
    }

    protected function resolveManagerPathFromConfig(): ?string
    {
        $candidates = [];
        if (function_exists('config')) {
            $candidates[] = config('cms.manager_path');
            $candidates[] = config('cms.settings.manager_path');
        }

        if (defined('MODX_MANAGER_PATH')) {
            $candidates[] = MODX_MANAGER_PATH;
        }

        $evo = $this->resolveEvo();
        if ($evo && method_exists($evo, 'getConfig')) {
            $candidates[] = $evo->getConfig('manager_path');
        }

        foreach ($candidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }
            $candidate = trim($candidate);
            if ($candidate === '') {
                continue;
            }

            if ($this->looksLikeUrl($candidate)) {
                continue;
            }

            return $this->normalizeManagerFilesystemPath($candidate);
        }

        return null;
    }

    protected function resolveManagerUrlFromConfig(): ?string
    {
        $candidates = [];
        if (function_exists('config')) {
            $candidates[] = config('cms.manager_url');
            $candidates[] = config('cms.settings.manager_url');
        }

        if (defined('MODX_MANAGER_URL')) {
            $candidates[] = MODX_MANAGER_URL;
        }

        $evo = $this->resolveEvo();
        if ($evo && method_exists($evo, 'getConfig')) {
            $candidates[] = $evo->getConfig('manager_url');
        }

        foreach ($candidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }
            $candidate = trim($candidate);
            if ($candidate === '') {
                continue;
            }

            $path = $this->extractPathFromUrl($candidate);
            if ($path === '') {
                continue;
            }

            return $this->normalizeManagerWebPath($path);
        }

        return null;
    }

    protected function normalizeManagerFilesystemPath(string $path): string
    {
        $path = rtrim($path, "/\\");
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        $path = trim($path, "/\\");
        if ($path === '') {
            $path = 'manager';
        }

        return public_path($path);
    }

    protected function normalizeManagerWebPath(string $path): string
    {
        $path = trim($path, "/\\");
        if ($path === '') {
            $path = 'manager';
        }

        return public_path($path);
    }

    protected function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if ($path[0] === '/' || $path[0] === '\\') {
            return true;
        }

        return (bool)preg_match('/^[A-Za-z]:\\\\/', $path);
    }

    protected function looksLikeUrl(string $value): bool
    {
        return str_contains($value, '://') || str_starts_with($value, '//');
    }

    protected function extractPathFromUrl(string $value): string
    {
        if ($this->looksLikeUrl($value)) {
            return (string)parse_url($value, PHP_URL_PATH);
        }

        return $value;
    }

    protected function resolveLangVendorPath(string $namespace): string
    {
        if (function_exists('lang_path')) {
            return lang_path('vendor/' . $namespace);
        }

        return base_path('lang/vendor/' . $namespace);
    }

    protected function resolveEvo()
    {
        if (function_exists('evo')) {
            return evo();
        }

        if (function_exists('EvolutionCMS')) {
            return EvolutionCMS();
        }

        return null;
    }
}
