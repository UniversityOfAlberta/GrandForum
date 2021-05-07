<?php

    require_once('commandLine.inc');

    $wgUser = User::newFromId(1);
    
    $rssAlerts = new RSSAlerts();
    $rssAlerts->handleImport(true);
    
?>
