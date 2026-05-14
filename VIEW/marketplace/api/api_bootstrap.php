<?php
/**
 * api_bootstrap.php - Bootstrap pour les scripts API Marketplace (sans Composer).
 * __DIR__ = integweb/VIEW/marketplace/api
 */
declare(strict_types=1);

// Racine d'integweb : remonter 3 niveaux (api -> marketplace -> VIEW -> integweb)
define('INTEGWEB_ROOT', dirname(__DIR__, 3));

function kool_config(): array {
    static $cfg;
    if ($cfg === null) {
        $cfg = require INTEGWEB_ROOT . '/config/kool_config.php';
    }
    return $cfg;
}
