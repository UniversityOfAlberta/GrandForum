<?php

    require_once('commandLine.inc');

    $wgUser = User::newFromId(1);
    
    $rssAlerts = new RSSAlerts();
    $rssAlerts->handleImport();

    $articles = array();
    $nis = Person::getAllPeople(NI);
    foreach($nis as $ni){
        $contents = "";
        $result = 0;
        $gsUrl = urlencode("https://scholar.google.com/scholar?hl=en&as_sdt=2007&q=\"{$ni->getFirstName()}+{$ni->getLastName()}\"&scisbd=1");
        $contents = file_get_contents("{$config->getValue("gscholar-rss")}{$gsUrl}");
        echo $gsUrl." ... ";
        if($contents != ""){
            $parsed = $rssAlerts->parseRSS($contents, null, $ni);
            if($parsed === false){
                $errors[] = $ni;
                echo "FAILED\n";
            }
            else{
                $articles = array_merge($articles, $parsed);
                echo "DONE\n";
            }
        }
        else{
            $errors[] = $ni;
            echo "FAILED\n";
        }
        sleep(rand(45, 60)); // Random sleep time to help prevent being blocked
    }
    if(count($errors) > 0){
        $wgMessage->addError("<b>".count($errors)."</b> RSS feeds could not be read");
    }
    
?>
