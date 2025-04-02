<?php
require_once('../config/Config.php');

return array(
    "paths" => array(
        "migrations" => "./migrations"
    ),
    "environments" => array(
        "default_migration_table" => "phinxlog",
        "default_environment" => "dev",
        "dev" => array(
            "adapter" => $config->getValue('dbType'),
            "host" => $config->getValue('dbServer'),
            "name" => $config->getValue('dbName'),
            "user" => $config->getValue('dbUser'),
            "pass" => $config->getValue('dbPassword'),
            "port" => 3306
        )
    )
);
?>
