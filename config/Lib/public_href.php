<?php
declare(strict_types=1);

/**
 * Lien vers une page sous la racine Web du projet (ex. index.php, View/FrontOffice/client.php).
 *
 * @param string $appWebRoot Prefixe URL du dossier app (ex. /kool) depuis kool_web_path_prefixes()
 */
function kool_public_href(string $appWebRoot, string $page): string {
    $page = ltrim($page, '/');
    if ($appWebRoot === '' || $appWebRoot === '/') {
        return '/' . $page;
    }

    return rtrim($appWebRoot, '/') . '/' . $page;
}
