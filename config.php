<?php

class config
{
    public static function getSettings()
    {
        return [
            'appName' => 'Kool Healthy',
            'defaultPage' => 'backoffice'
        ];
    }

    public static function getDbConfig()
    {
        return [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'database' => 'kool_healthy',
        ];
    }
}
