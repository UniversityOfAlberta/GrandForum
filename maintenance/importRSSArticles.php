<?php

    require_once('commandLine.inc');

    $wgUser = User::newFromId(1);
    
    $rssAlerts = new RSSAlerts();
    $rssAlerts->handleImport();

    $nis = Person::getAllPeople(NI);
    foreach($nis as $ni){
        $contents = "";
        $result = 0;
        $gsUrl = urlencode("https://scholar.google.com/scholar?hl=en&as_sdt=2007&q=\"{$ni->getFirstName()}+{$ni->getLastName()}\"&scisbd=1");
        $contents = file_get_contents("{$config->getValue("gscholar-rss")}{$gsUrl}");
        echo $gsUrl." ... ";
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
        sleep(rand(55, 65)); // Random sleep time to help prevent being blocked
    }
    
?>
