<?php
abstract class SerializedCache extends Cache{

	function SerializedCache($fileName, $directory = ""){
		parent::Cache($directory.sha1($fileName));
	}
	
	function getCache(){
		$xml = "";
		//if(true){
		if(!file_exists("extensions/Cache/cache/{$this->fileName}") || (time() - filemtime("extensions/Cache/cache/{$this->fileName}")) > 0){
			// Miss
			$xml = serialize($this->run());
			$zp = gzopen("extensions/Cache/cache/{$this->fileName}", "w9");
			gzwrite($zp, $xml);
			gzclose($zp);
			//echo "MISS";
		}
		else {
			$xml = implode("", gzfile("extensions/Cache/cache/{$this->fileName}"));
		}
		$xml = unserialize($xml);
		return $xml;
	}
}
?>
