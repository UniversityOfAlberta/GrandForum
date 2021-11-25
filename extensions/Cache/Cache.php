<?php

define("CACHE_FOLDER", "extensions/Cache/cache/");

require_once("SerializedCache.php");

$wgObjectCaches['apc_shared'] = array(
    'class' => 'APCSharedCache'
);

abstract class Cache {

    static $hitCount = 0;

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

/**
 * This is a wrapper for APC's shared memory functions
 *
 * @ingroup Cache
 */
class APCSharedCache extends BagOStuff {
	/**
	 * @param $key string
	 * @param $casToken[optional] int
	 * @return mixed
	 */
	public function get( $key, &$casToken = null ) {
	    global $wgSitename;
	    $key = $wgSitename.$key;
		$val = apc_fetch( $key );

		$casToken = $val;

		if ( is_string( $val ) ) {
			if ( $this->isInteger( $val ) ) {
				$val = intval( $val );
			} else {
				$val = unserialize( $val );
			}
		}

		return $val;
	}

	/**
	 * @param $key string
	 * @param $value mixed
	 * @param $exptime int
	 * @return bool
	 */
	public function set( $key, $value, $exptime = 0 ) {
	    global $wgSitename;
	    $key = $wgSitename.$key;
		if ( !$this->isInteger( $value ) ) {
			$value = serialize( $value );
		}

		apc_store( $key, $value, $exptime );

		return true;
	}

	/**
	 * @param $casToken mixed
	 * @param $key string
	 * @param $value mixed
	 * @param $exptime int
	 * @return bool
	 */
	public function cas( $casToken, $key, $value, $exptime = 0 ) {
		// APC's CAS functions only work on integers
		throw new MWException( "CAS is not implemented in " . __CLASS__ );
	}

	/**
	 * @param $key string
	 * @param $time int
	 * @return bool
	 */
	public function delete( $key, $time = 0 ) {
	    global $wgSitename;
	    $key = $wgSitename.$key;
		apc_delete( $key );

		return true;
	}

	/**
	 * @param $key string
	 * @param $callback closure Callback method to be executed
	 * @param int $exptime Either an interval in seconds or a unix timestamp for expiry
	 * @param int $attempts The amount of times to attempt a merge in case of failure
	 * @return bool success
	 */
	public function merge( $key, closure $callback, $exptime = 0, $attempts = 10 ) {
	    global $wgSitename;
	    $key = $wgSitename.$key;
		return $this->mergeViaLock( $key, $callback, $exptime, $attempts );
	}

	public function incr( $key, $value = 1 ) {
	    global $wgSitename;
	    $key = $wgSitename.$key;
		return apc_inc( $key, $value );
	}

	public function decr( $key, $value = 1 ) {
	    global $wgSitename;
	    $key = $wgSitename.$key;
		return apc_dec( $key, $value );
	}
}

?>
