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
	    if(self::$useCache){
            apcu_store(static::prefix().$key, $data, $time);
        }
	}
	
	static function fetch($key){
	    if(self::$useCache){
            return apcu_fetch(static::prefix().$key);
        }
        return "";
	}
	
	static function delete($key, $prefix=false){
	    if(self::$useCache){
	        if($prefix){
	            $it = new APCUIterator('/^'.str_replace(")", '\)', str_replace("(", '\(', $wgSitename)).$key.'/', APC_ITER_KEY);
	            foreach($it as $k){
	                apcu_delete($k['key']);
	            }
	        }
	        else{
                apcu_delete(static::prefix().$key);
            }
        }
	}
	
	static function exists($key){
	    if(self::$useCache){
            return apcu_exists(static::prefix().$key);
        }
        return false;
	}
	
	static function prefix(){
	    global $wgSitename;
	    return $wgSitename.'_';
	}
	
	abstract function run();
}

abstract class DBCache extends Cache {
    
    static function prefix(){
        global $config;
	    return $config->getValue('dbName').'_';
	}
    
}

Cache::init();

?>
