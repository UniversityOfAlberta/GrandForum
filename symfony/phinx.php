<?php
require_once('../config/ForumConfig.php');

return array(
    "paths" => array(
        "migrations" => "./migrations"
    ),
    "environments" => array(
        "default_migration_table" => "phinxlog",
        "default_database" => "dev",
        "dev" => array(
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "adapter" => $config->getValue('dbType'),
            "host" => $config->getValue('dbServer'),
            "name" => $config->getValue('dbName'),
            "user" => $config->getValue('dbUser'),
            "pass" => $config->getValue('dbPassword'),
            "port" => 8889 //3306
        )
    )
);
?>
