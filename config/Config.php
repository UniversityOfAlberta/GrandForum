<?php

    $config = new ForumConfig();
    $GLOBALS['config'] = $config;

    require_once("default_config.php");
    require_once("config.php");
    
    $config->define();
    
    class ForumConfig {
    
        var $config = array();
        var $constants = array();
        
        function setValue($key, $value){
            $this->config[$key] = $value;
        }
        
        function getValue($key){
            return $this->config[$key];
        }
        
        function setConst($key, $value){
            $this->constants[$key] = $value;
        }
        
        function getConst($key){
            return $this->constants[$key];
        }
        
        function define(){
            foreach($this->constants as $key => $value){
                define($key, $value);
            }
        }
    }
    
?>
