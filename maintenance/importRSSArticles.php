<?php

    require_once('commandLine.inc');

    $wgUser = User::newFromId(1);
    
    $rssAlerts = new RSSAlerts();
    $rssAlerts->handleImport();

    $nis = Person::getAllPeople(NI);
    foreach($nis as $ni){
        $contents = "";
        $result = 0;
        $gsUrl = "https://scholar.google.com/scholar?hl=en&as_sdt=2007&q=\"{$ni->getFirstName()}+{$ni->getLastName()}\"&scisbd=1";
        echo $gsUrl." ... ";
        $gsUrl = urlencode($gsUrl);
        $contents = file_get_contents("{$config->getValue("gscholar-rss")}{$gsUrl}");
        if($contents != ""){
            $articles = $rssAlerts->parseRSS($contents, null, $ni);
            if($articles === false){
                echo "FAILED\n";
            }
            else{
                foreach($articles as $article){
                    $article->create();
                }
                echo "DONE\n";
            }
        }
        else{
            echo "FAILED\n";
        }
        sleep(rand(120, 150)); // Random sleep time to help prevent being blocked
    }
    
?>
