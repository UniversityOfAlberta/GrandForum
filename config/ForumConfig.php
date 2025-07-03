<?php

    if(!defined('TESTING')){
        if(file_exists("../test.tmp")){
            define("TESTING", true);
        }
        else{
            define("TESTING", false);
        }
    }
    $config = new ForumConfig();
    $GLOBALS['config'] = $config;
    $config->default = true;
    require("default_config.php");
    $config->default = false;
    require("config.php");
    
    $config->define();
    
    class ForumConfig {
    
        var $default = false;
        var $config = array();
        var $constants = array();
        
        function setValue($key, $value){
            if(!TESTING || $this->default || strpos($key, "db") === 0 || $key == "path" || $key == "server"){
                $this->config[$key] = $value;
            }
        }
        
        function hasValue($key){
            return (isset($this->config[$key]));
        }
        
        function getValue($key, $subKey=null, $nullIfNoKey=false){
            if($subKey == null){
                return @$this->config[$key];
            }
            else if(is_array($this->config[$key]) && isset($this->config[$key][$subKey])){
                return $this->config[$key][$subKey];
            }
            else if(isset($this->config[$key]) && !$nullIfNoKey){
                return $this->config[$key];
            }
            else{
                return "";
            }
        }
        
        function setConst($key, $value){
            if(!TESTING || $this->default){
                $this->constants[$key] = $value;
            }
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
