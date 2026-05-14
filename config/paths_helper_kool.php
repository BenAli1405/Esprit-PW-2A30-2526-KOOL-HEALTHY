<?php
declare(strict_types=1);

/**
 * Prefixes Web absolus pour View/FrontOffice (API + assets + pages client/vendeur).
 *
 * @return array{appWebRoot: string, frontOfficeBase: string, assetsBase: string}
 */
function kool_web_path_prefixes(): array {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $scriptDir = dirname($scriptName);
    if ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '') {
        $scriptDir = '';
    } else {
        $scriptDir = rtrim($scriptDir, '/');
    }

    $foSuffix = '/View/FrontOffice';
    if ($scriptDir !== '' && str_ends_with($scriptDir, $foSuffix)) {
        $appWebRoot = substr($scriptDir, 0, -strlen($foSuffix));
    } elseif ($scriptDir !== '' && str_contains($scriptDir, '/View/')) {
        $pos = strpos($scriptDir, '/View/');
        $appWebRoot = $pos > 0 ? substr($scriptDir, 0, $pos) : '';
    } else {
        $appWebRoot = $scriptDir;
    }

    if ($appWebRoot === '/' || $appWebRoot === '.') {
        $appWebRoot = '';
    }

    $frontOfficeBase = ($appWebRoot === '' ? '' : $appWebRoot) . '/View/FrontOffice/';
    if ($frontOfficeBase === '' || $frontOfficeBase[0] !== '/') {
        $frontOfficeBase = '/' . ltrim($frontOfficeBase, '/');
    }
    $frontOfficeBase = rtrim($frontOfficeBase, '/') . '/';
    $assetsBase = $frontOfficeBase . 'assets/';

    return [
        'appWebRoot' => $appWebRoot,
        'frontOfficeBase' => $frontOfficeBase,
        'assetsBase' => $assetsBase,
    ];
}
