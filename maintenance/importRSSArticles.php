<?php

    require_once('commandLine.inc');
    
    ini_set('default_socket_timeout', 900);

    $wgUser = User::newFromId(1);
    
    $rssAlerts = new RSSAlerts();
    $rssAlerts->handleImport();

    $nis = Person::getAllPeople(NI);
    foreach($nis as $ni){
        $contents = "";
        $result = 0;
        $gsUrl = "\"{$ni->getFirstName()} {$ni->getLastName()}\"";
        echo "{$gsUrl}";
        $parts = parse_url($ni->getGoogleScholar());
        $single = false;
        if($parts !== false){
            parse_str($parts['query'], $query);
            if(isset($query['user'])){
                $single = true;
                $gsUrl = $query['user'];
                echo " ({$gsUrl})";
            }
        }
        echo ": ... ";
        $gsUrl = urlencode($gsUrl);
        $contents = ($single) ? file_get_contents("{$config->getValue("gscholar-rss")}author/{$gsUrl}") 
                              : file_get_contents("{$config->getValue("gscholar-rss")}search/{$gsUrl}");
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
        //sleep(rand(120, 150)); // Random sleep time to help prevent being blocked
    }
    
?>
