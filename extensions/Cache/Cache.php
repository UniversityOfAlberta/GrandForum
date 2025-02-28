<?php

abstract class Cache {

	var $fileName;
	var $compress;
	
	function __construct($fileName, $directory = ""){
		$this->fileName = $directory.$fileName;
		$this->compress = true;
	}
	
	static function store($key, $data, $time=172800){
	    global $wgSitename;
	    if(function_exists('apcu_store')){
            apcu_store($wgSitename.$key, $data, $time);
        }
	}
	
	static function fetch($key){
	    global $wgSitename;
	    if(function_exists('apcu_fetch')){
            return apcu_fetch($wgSitename.$key);
        }
        return "";
	}
	
	static function delete($key, $prefix=false){
	    global $wgSitename;
	    if(function_exists('apcu_delete') && class_exists('APCUIterator')){
	        if($prefix){
	            $it = new APCUIterator('/^'.str_replace(")", '\)', str_replace("(", '\(', $wgSitename)).$key.'/', APC_ITER_KEY);
	            foreach($it as $k){
	                apcu_delete($k['key']);
	            }
	        }
	        else{
                apcu_delete($wgSitename.$key);
            }
        }
	}
	
	static function exists($key){
	    global $wgSitename;
	    if(function_exists('apcu_exists')){
            return apcu_exists($wgSitename.$key);
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
