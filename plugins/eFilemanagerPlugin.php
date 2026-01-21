<?php

use EvolutionCMS\Models\SystemSetting;

if (!function_exists('eFilemanager_log')) {
    function eFilemanager_log(string $message, int $type = 2): void
    {
        if (function_exists('evo')) {
            evo()->logEvent(0, $type, $message, 'eFilemanager');
        }
    }
}

if (!function_exists('eFilemanager_settings')) {
    function eFilemanager_settings(): array
    {
        $settings = config('cms.settings.eFilemanager', []);
        return is_array($settings) ? $settings : [];
    }
}

if (!function_exists('eFilemanager_is_enabled')) {
    function eFilemanager_is_enabled(): bool
    {
        $settings = eFilemanager_settings();
        return (bool)($settings['enable'] ?? false);
    }
}

if (!function_exists('eFilemanager_auto_set_default')) {
    function eFilemanager_auto_set_default(): void
    {
        if (!eFilemanager_is_enabled() || !function_exists('evo')) {
            return;
        }

        if (!evo()->isLoggedIn('mgr')) {
            return;
        }

        $settings = eFilemanager_settings();
        if (array_key_exists('auto_set_default', $settings) && !$settings['auto_set_default']) {
            return;
        }

        $flagKey = $settings['auto_set_default_flag'] ?? 'efilemanager_default_set';

        try {
            $flag = SystemSetting::query()
                ->where('setting_name', $flagKey)
                ->value('setting_value');

            if ((string)$flag === '1') {
                return;
            }

            $current = evo()->getConfig('which_browser');
            if (!$current || $current === 'mcpuk') {
                SystemSetting::updateOrCreate(
                    ['setting_name' => 'which_browser'],
                    ['setting_value' => 'efilemanager']
                );
            }

            SystemSetting::updateOrCreate(
                ['setting_name' => $flagKey],
                ['setting_value' => '1']
            );
        } catch (Throwable $e) {
            eFilemanager_log('Failed to set default file browser: ' . $e->getMessage());
        }
    }
}

Event::listen('evolution.OnManagerPageInit', function () {
    eFilemanager_auto_set_default();
});
