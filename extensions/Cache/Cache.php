<?php

abstract class Cache {

	var $fileName;
	var $compress;
	
	function Cache($fileName, $directory = ""){
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
	    global $wgSitename;
	    if(function_exists('apc_fetch')){
            return apc_fetch($wgSitename.$key);
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
		if(!file_exists("extensions/Cache/cache/{$this->fileName}")){
			// Miss
			$xml = $this->run();
			$zp = gzopen("extensions/Cache/cache/{$this->fileName}", "w9");
			gzwrite($zp, $xml);
			gzclose($zp);
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
