<?php

abstract class Cache {

    static $useCache = false;

	var $fileName;
	var $compress;
	
	static function init(){
	    self::$useCache = (function_exists('apcu_fetch') && PHP_SAPI != 'cli');
	}
	
	function __construct($fileName, $directory = ""){
		$this->fileName = $directory.$fileName;
		$this->compress = true;
	}
	
	static function store($key, $data, $time=432000){
	    global $wgSitename;
	    if(self::$useCache){
            apcu_store($wgSitename.$key, $data, $time);
        }
	}
	
	static function fetch($key){
	    global $wgSitename;
	    if(self::$useCache){
            return apcu_fetch($wgSitename.$key);
        }
        return "";
	}
	
	static function delete($key, $prefix=false){
	    global $wgSitename;
	    if(self::$useCache){
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
	    if(self::$useCache){
            return apcu_exists($wgSitename.$key);
        }
        return false;
	}
	
	abstract function run();
}

Cache::init();

?>
