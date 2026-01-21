<?php

namespace EvolutionCMS\eFilemanager\Handlers;

class EvoConfigHandler
{
    public function userField(): string
    {
        $id = $_SESSION['mgrInternalKey'] ?? null;
        if ($id) {
            return (string)$id;
        }

        if (function_exists('EvolutionCMS')) {
            $evo = EvolutionCMS();
            if ($evo && method_exists($evo, 'getLoginUserID')) {
                $loginId = $evo->getLoginUserID('mgr');
                if ($loginId) {
                    return (string)$loginId;
                }
            }
        }

        if (function_exists('evo')) {
            $evo = evo();
            if ($evo && method_exists($evo, 'getLoginUserID')) {
                $loginId = $evo->getLoginUserID('mgr');
                if ($loginId) {
                    return (string)$loginId;
                }
            }
        }

        return 'manager';
    }
}
