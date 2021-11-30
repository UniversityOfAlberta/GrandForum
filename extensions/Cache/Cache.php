<?php

abstract class Cache {

    static $hitCount = 0;

	var $fileName;
	var $compress;
	
	function __construct($fileName, $directory = ""){
		$this->fileName = $directory.$fileName;
		$this->compress = true;
	}
	
	static function store($key, $data, $time=86400){
	    global $wgSitename;
	    if(function_exists('apc_store')){
            apc_store($wgSitename.$key, $data, $time);
        }
	}
	
	static function fetch($key){
	    global $wgSitename, $wgOut;
	    if(function_exists('apc_fetch')){
	        if(DBFunctions::$queryDebug){
                $start = microtime(true);
                $peakMemBefore = memory_get_peak_usage(true)/1024/1024;
            }
            $data = apc_fetch($wgSitename.$key);
            if(DBFunctions::$queryDebug){
                self::$hitCount++;
                $end = microtime(true);
                $peakMemAfter = memory_get_peak_usage(true)/1024/1024;
                $diff = number_format(($end - $start)*1000, 5);
                $debugLine = "<!-- Cache ".self::$hitCount.": ($diff ms / ".count($data)." / Before:{$peakMemBefore}MiB / After:{$peakMemAfter}MiB) $key -->\n";
		        $wgOut->addHTML($debugLine);
		    }
            return $data;
        }
        return "";
	}
	
	static function delete($key, $prefix=false){
	    global $wgSitename;
	    if(function_exists('apc_delete') && class_exists('APCIterator')){
	        if($prefix){
	            $it = new APCIterator('user', '/^'.str_replace(")", '\)', str_replace("(", '\(', $wgSitename)).$key.'/', APC_ITER_KEY);
	            foreach($it as $k){
	                apc_delete($k['key']);
	            }
	        }
	        else{
                apc_delete($wgSitename.$key);
            }
        }
	}
	
	static function exists($key){
	    global $wgSitename;
	    if(function_exists('apc_exists')){
            return apc_exists($wgSitename.$key);
        }
        return false;
	}

	function getCache(){
		$xml = "";
		//if(true){
		if(!file_exists("extensions/Cache/cache/{$this->fileName}")){
			// Miss
			$xml = $this->run();
			$zp = gzopen("extensions/Cache/cache/{$this->fileName}", "w9");
			gzwrite($zp, $xml);
			gzclose($zp);
			//echo "MISS";
		}
		else {
			$xml = implode("", gzfile("extensions/Cache/cache/{$this->fileName}"));
			if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
				ob_start("ob_gzhandler");
			}
		}
		return $xml;
	}
	
	abstract function run();
	
	function getFileSize(){
		if(file_exists("extensions/Cache/cache/{$this->fileName}")){
			$xml = implode("", gzfile("extensions/Cache/cache/{$this->fileName}"));
			return strlen($xml);
		}
		else return 0;
	}
}

?>
