<?php

define("CACHE_FOLDER", "extensions/Cache/cache/");

require_once("SerializedCache.php");

abstract class Cache {

	var $fileName;
	var $compress;
	
	function Cache($fileName, $directory = ""){
		$this->fileName = $directory.$fileName;
		$this->compress = true;
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
