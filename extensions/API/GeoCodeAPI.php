<?php

class GeoCodeAPI extends API{

    function __construct(){
        
    }

    function processParams($params){

    }

	function doAction($noEcho=false){
        if(isset($_GET['address'])){
            if(Cache::exists("geocode_{$_GET['address']}")){
                $result = Cache::fetch("geocode_{$_GET['address']}");
            }
            else{
                $params = array('locate' => $_GET['address'], 
                                'geoit' => 'XML',
                                'json' => '1');
                $url = "https://geocoder.ca/?" . http_build_query($params);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                Cache::store("geocode_{$_GET['address']}", $result);
            }
            header('Content-Type: application/json');
            echo $result;
            exit;
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
