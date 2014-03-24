<?php

    require_once("config.php");
    
    class Config {
    
        var $config = array();
        
        function setValue($key, $value){
            $this->config[$key] = $value;
        }
        
        function getValue($key){
            return $this->config[$key];
        }
        
        function define($key, $value){
            define($key, $value);
        }
    }
    
?>
