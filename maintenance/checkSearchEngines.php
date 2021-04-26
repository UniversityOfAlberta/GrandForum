<?php
    require_once('commandLine.inc');
    
    $useragents = array("Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.72 Safari/537.36",
                        "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:87.0) Gecko/20100101 Firefox/87.0",
                        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4464.0 Safari/537.36 Edg/91.0.852.0",
                        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.128 Safari/537.36 OPR/75.0.3969.218");
    
    $wgUser = User::newFromId(1);
    $googleBlocked = false;
    $bingBlocked = false;
    $yahooBlocked = false;
    
    function initCurl($url){
        global $useragents;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept' => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
                                                   'Accept-Encoding' => "gzip, deflate, br",
                                                   'Accept-Language' => "en-CA,en-US;q=0.7,en;q=0.3",
                                                   'Cache-Control' => "max-age=0",
                                                   'Connection' => "keep-alive"));
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt"); 
        curl_setopt($ch, CURLOPT_USERAGENT, $useragents[rand(0,count($useragents)-1)]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        return $ch;
    }
    
    function google($news){
        global $googleBlocked;
        if($googleBlocked){ return null; } // Blocked, don't try again
        $ch = initCurl("https://www.google.com/search?q=site:{$news->getUrl()}");
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpcode != 200) { $googleBlocked = true; return null; }
        return (strstr($response, "did not match any documents") === false);
    }
    
    function bing($news){
        global $bingBlocked;
        if($bingBlocked){ return null; } // Blocked, don't try again
        $ch = initCurl("https://www.bing.com/search?q=url:{$news->getUrl()}");
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpcode != 200){ $bingBlocked = true; return null; }
        return (preg_match("/([0-9]+ results)/", $response) > 0);
    }
    
    function yahoo($news){
        global $yahooBlocked;
        if($yahooBlocked){ return null; } // Blocked, don't try again
        $ch = initCurl("https://search.yahoo.com/search?p=url:{$news->getUrl()}");
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpcode != 200){ $yahooBlocked = true; return null; }
        return (strstr($response, "We did not find results") === false);
    }
    
    $allNews = UofANews::getAllNews();
    $searchEngines = (file_exists(__DIR__."/searchEngines.json")) ? json_decode(file_get_contents(__DIR__."/searchEngines.json"), true) : array();
    foreach($allNews as $news){
        if(strstr($news->getUrl(), "folio") !== false){
            echo $news->getUrl()."\n";
            $changed = false;
            // Google
            echo "\tGoogle\t...\t";
            if(!isset($searchEngines[$news->getUrl()]['google']) || !$searchEngines[$news->getUrl()]['google']){
                $searchEngines[$news->getUrl()]['google'] = google($news);
                $changed = ($changed || $searchEngines[$news->getUrl()]['google'] !== null);
            }
            echo ($searchEngines[$news->getUrl()]['google']) ? "Found\n" : (($searchEngines[$news->getUrl()]['google'] === false) ? "Not Found\n" : "Error\n");
            
            // Bing
            echo "\tBing\t...\t";
            if(!isset($searchEngines[$news->getUrl()]['bing']) || !$searchEngines[$news->getUrl()]['bing']){
                $searchEngines[$news->getUrl()]['bing'] = bing($news);
                $changed = ($changed || $searchEngines[$news->getUrl()]['bing'] !== null);
            }
            echo ($searchEngines[$news->getUrl()]['bing']) ? "Found\n" : (($searchEngines[$news->getUrl()]['bing'] === false) ? "Not Found\n" : "Error\n");
            
            // Yahoo
            echo "\tYahoo\t...\t";
            if(!isset($searchEngines[$news->getUrl()]['yahoo']) || !$searchEngines[$news->getUrl()]['yahoo']){
                $searchEngines[$news->getUrl()]['yahoo'] = yahoo($news);
                $changed = ($changed || $searchEngines[$news->getUrl()]['yahoo'] !== null);
            }
            echo ($searchEngines[$news->getUrl()]['yahoo']) ? "Found\n" : (($searchEngines[$news->getUrl()]['yahoo'] === false) ? "Not Found\n" : "Error\n");
            
            // Save json
            file_put_contents(__DIR__."/searchEngines.json", json_encode($searchEngines));
            if($changed){
                sleep(rand(45, 60)); // Random sleep time to help prevent being blocked
            }
        }
    }
    
?>
