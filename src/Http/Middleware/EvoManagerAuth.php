<?php

namespace EvolutionCMS\eFilemanager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EvoManagerAuth
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = config('cms.settings.eFilemanager', []);
        $enabled = (bool)($settings['enable'] ?? true);

        if (!$enabled) {
            return $this->deny(404, 'File manager is disabled.');
        }

        if (!$this->hasManagerSession()) {
            return $this->deny(403, 'No manager session.');
        }

        $acl = $settings['acl'] ?? [];
        $isManage = $this->isManageAction($request);
        if ($isManage && array_key_exists('allow_manage', $acl) && !$acl['allow_manage']) {
            return $this->deny(403);
        }
        if (!$isManage && array_key_exists('allow_browse', $acl) && !$acl['allow_browse']) {
            return $this->deny(403);
        }

        $typeKey = $this->resolveType($request);
        $permissions = $settings['permissions'] ?? [];
        $permission = $this->resolvePermission($permissions, $typeKey, $isManage);

        $evo = $this->getEvo();
        if ($permission && $evo && !$evo->hasPermission('file_manager') && !$evo->hasPermission($permission)) {
            return $this->deny(403, 'No permission.');
        }

        return $next($request);
    }

    private function resolveType(Request $request): string
    {
        $type = strtolower((string)$request->input('type', 'file'));
        $type = rtrim($type, 's');
        if ($type === 'image') {
            return 'images';
        }

        return 'files';
    }

    private function resolvePermission(array $permissions, string $typeKey, bool $isManage): ?string
    {
        if ($typeKey === 'images') {
            if ($isManage) {
                return $permissions['manage_images'] ?? $permissions['browse_images'] ?? null;
            }

            return $permissions['browse_images'] ?? null;
        }

        if ($isManage) {
            return $permissions['manage_files'] ?? $permissions['browse_files'] ?? null;
        }

        return $permissions['browse_files'] ?? null;
    }

    private function isManageAction(Request $request): bool
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        $action = $routeName ? substr($routeName, strrpos($routeName, '.') + 1) : '';

        $manageActions = [
            'upload',
            'move',
            'doMove',
            'getAddfolder',
            'getRename',
            'getResize',
            'getResizeImage',
            'getNewResizeImage',
            'getCrop',
            'getCropImage',
            'getNewCropImage',
            'getDelete',
            'doresize',
            'doresizenew',
            'domove',
            'newfolder',
            'rename',
            'resize',
            'crop',
            'delete',
        ];

        if (in_array($action, $manageActions, true)) {
            return true;
        }

        $path = $request->path();
        foreach (['upload', 'delete', 'rename', 'move', 'resize', 'crop', 'newfolder'] as $segment) {
            if (strpos($path, $segment) !== false) {
                return true;
            }
        }

        return false;
    }

    private function deny(int $status, string $message = 'Access denied.'): Response
    {
        if ($status === 404) {
            $message = 'Not Found';
        }
        return new Response($message, $status);
    }

    private function hasManagerSession(): bool
    {
        if (isset($_SESSION['mgrValidated']) && $_SESSION['mgrValidated']) {
            return true;
        }

        $evo = $this->getEvo();
        if ($evo && method_exists($evo, 'isLoggedIn')) {
            return (bool)$evo->isLoggedIn('mgr');
        }

        return false;
    }

    private function getEvo()
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
