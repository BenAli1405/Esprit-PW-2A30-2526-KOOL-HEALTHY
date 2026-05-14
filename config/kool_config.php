<?php
/**
 * kool_config.php - Configuration pour le module Marketplace (intégré depuis kool)
 * Retourne un tableau de configuration, compatible avec les fichiers API marketplace.
 */
$defaults = [
    'app_base_url'          => 'http://localhost:8080/integweb',
    'stripe_secret_key'     => '',
    'stripe_publishable_key'=> '',
    'stripe_webhook_secret' => '',
    'stripe_currency'       => 'eur',
    'gemini_api_key'        => 'AIzaSyBvhNzjzdwTvmBLapriPAlW7AU-jtHWyyg',
    'gemini_model'          => 'gemini-2.5-flash-lite',
    'mail_from'             => 'noreply@localhost',
    'mail_from_name'        => 'Kool Healthy',
    'smtp_host'             => '',
    'smtp_port'             => 587,
    'smtp_user'             => '',
    'smtp_pass'             => '',
    'smtp_secure'           => 'tls',
    'mail_log_path'         => __DIR__ . '/storage/logs/mail.log',
    'weather_latitude'      => 36.8065,
    'weather_longitude'     => 10.1815,
    'weather_timezone'      => 'Africa/Tunis',
    'weather_city_label'    => 'Grand Tunis',
];

// Surcharge locale optionnelle (config.local.php dans le dossier kool d'origine)
$localPath = dirname(__DIR__) . '/../kool/config/config.local.php';
$local = [];
if (is_readable($localPath)) {
    $loaded = require $localPath;
    if (is_array($loaded)) {
        $local = $loaded;
    }
}

return array_merge($defaults, $local);
