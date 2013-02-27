<?php

$wgHooks['UnknownAction'][''] = 'AcademiaMapProxy::proxy';

class AcademiaMapProxy {

    static function prepend_proxy($matches){
        global $wgServer, $wgScriptPath;
        if(count(preg_grep('/twitter\.com/', $matches)) > 0 || 
           count(preg_grep('/twimg/', $matches)) > 0 || 
           count(preg_grep('/=_.*_=/', $matches)) > 0){
            return $matches[1] . $matches[2] . $matches[3];
        }
        $proxyUrl = $wgServer.$wgScriptPath."/index.php?action=academiaMapProxy&url=";
        $url = 'http://academiamap.com/';

        $prepend = $matches[2] ? $matches[2] : $url;
        
        $prepend = $proxyUrl. urlencode($prepend);

        return $matches[1] . $prepend . $matches[3];
    }

    static function proxy($action){
        global $wgServer, $wgScriptPath;
        if($action == 'academiaMapProxy'){
            if(substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
                ob_start("ob_gzhandler");
            }
            else{
                ob_start();
            }
            session_write_close();
            $cache = new AcademiaMapProxyCache();
            $contents = base64_decode($cache->getCache());
            
            if(strstr($_GET['url'], '.css') !== false){
                header('Content-Type: text/css');
                $contents = str_replace("background-image:url('images/", "background-image:url('".$wgServer.$wgScriptPath."/index.php?action=academiaMapProxy&url=".urlencode("http://academiamap.com/images/"), $contents);
            }
            else if(strstr($_GET['url'], '.js') !== false || strstr($_GET['url'], 'jsapi') !== false){
                $contents = preg_replace("/getJSON\(([^,]*)\,/", "getJSON('".$wgServer.$wgScriptPath."/index.php?action=academiaMapProxy&url=' + escape('http://academiamap.com/' + $1),", $contents);
                $contents = str_replace("ajax-loader.gif", $wgServer.$wgScriptPath."/index.php?action=academiaMapProxy&url=".urlencode("http://academiamap.com/ajax-loader.gif"), $contents);
                $contents = str_replace("default.jpg", $wgServer.$wgScriptPath."/index.php?action=academiaMapProxy&url=".urlencode("http://academiamap.com/default.jpg"), $contents);
                header('Content-Type: text/javascript');
            }
            else if(strstr($_GET['url'], '.png') !== false){
                header('Content-Type: image/png');
            }
            else if(strstr($_GET['url'], '.jpeg') !== false || strstr($_GET['url'], '.jpg') !== false){
                header('Content-Type: image/jpeg');
            }
            else if(strstr($_GET['url'], '.gif') !== false){
                header('Content-Type: image/gif');
            }
            else if(strstr($_GET['url'], 'content.php') !== false){
                header('Content-Type: application/json');
            }
            else{
                $contents = preg_replace_callback(
                    '|(src=[\'"]?)(https?://)?([^\'"\s]+[\'"]?)|i',
                    'AcademiaMapProxy::prepend_proxy',
                    $contents
                );
                
                $contents = preg_replace_callback(
                    '|(href=[\'"]?)(https?://)?([^\'"\s]+[\'"]?)|i',
                    'AcademiaMapProxy::prepend_proxy',
                    $contents
                );
                $contents = str_replace("background-image:url('images/", "background-image:url('".$wgServer.$wgScriptPath."/index.php?action=academiaMapProxy&url=".urlencode("http://academiamap.com/images/"), $contents);
                
                $contents = str_replace("</head>", "<script type='text/javascript'>
                    $(document).ready(function(){
	                    $('body > table tr').first().remove();
	                    $('body > table tr').first().remove();
	                    $('body').css('background', 'white')
	                             .css('margin', 0);
	                    $('body > table').css('box-shadow', 'none')
	                                     .css('margin', '0 0 0 0');
	                    window.parent.updateIframeHeight($('body > table').height());
	                    setInterval(function(){
	                        window.parent.updateIframeHeight($('body > table').height());
	                    }, 100);
                    });
                </script></head>", $contents);
            }
            echo $contents;
            exit;
        }
        return true;
    }
}

class AcademiaMapProxyCache extends SerializedCache{
    
    function AcademiaMapProxyCache(){
        parent::SerializedCache($_GET['url'], "academiaMap");
    }
    
    function run(){
        $curl = curl_init(); 
        curl_setopt($curl, CURLOPT_URL, $_GET['url']);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
        $contents = curl_exec($curl);
        curl_close($curl);
        return base64_encode($contents);
    }
}

?>
