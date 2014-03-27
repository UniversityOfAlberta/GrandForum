<?php

$reader = new GoogleAlertReader();

$wgHooks['UnknownAction'][] = array($reader, 'getNews');
$wgHooks['ToolboxLinks'][] = 'GoogleAlertReader::createToolboxLinks';

class GoogleAlertReader{

    function getNews($action, $article){
        global $wgOut, $wgScriptPath, $wgLocalTZoffset, $config;
        if($action == "getNews"){
            $wgOut->addLink(array("rel" => "alternate", "type" => "application/rss+xml", "title" => "{$config->getValue('networkName')} News", "href" => "http://www.google.com/alerts/feeds/05256187622886684561/9298404980324971668"));
            $wgOut->setPageTitle("{$config->getValue('networkName')} News");
            $wgOut->addHTML("<a href='http://www.google.com/alerts/feeds/05256187622886684561/9298404980324971668' target='_blank'><img src='$wgScriptPath/extensions/GoogleAlertReader/rss-button.png' alt='subscribe' border='0' style='vertical-align:text-bottom;' /></a> <a href='http://www.google.com/alerts/feeds/05256187622886684561/9298404980324971668' target='_blank'>Subscribe to GRAND NCE News</a>");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://www.google.com/alerts/feeds/05256187622886684561/9298404980324971668");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            $xml = curl_exec($ch);
            $doc = new DOMDocument();
            @$doc->loadXML($xml);
            
            $items = $doc->getElementsByTagName("entry");
            for($i = 0; $i < $items->length; $i++){
                $item = $items->item($i);
                // Title
                $titles = $item->getElementsByTagName("title");
                $title = str_replace("<b>", "", $titles->item(0)->textContent);
                // Date
                $dates = $item->getElementsByTagName("published");
                $date = new DateTime($dates->item(0)->textContent);
                $dateF = $date->format("F d, Y H:i:s");
                // Description
                $descriptions = $item->getElementsByTagName("content");
                $description = str_replace("<b>", "", $descriptions->item(0)->textContent);
                $description = str_replace("<a", "<a target='_blank'", $description);
                
                $wgOut->addHTML("<h2>$title</h2>
                        <b>Published:</b> $dateF<br />
                        <p>$description</p>");
            }
            
            return false;
        }
        return true;
    }
    
    static function createToolboxLinks($toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $toolbox['Other']['links'][] = TabUtils::createToolboxLink("Recent News", "$wgServer$wgScriptPath/index.php?action=getNews");
        return true;
    }

}

?>
