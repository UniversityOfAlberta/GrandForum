<?php

abstract class AbstractTab {

    var $html;
    var $id;
    var $name;

    function AbstractTab($name){
        $this->id = str_replace("'", "", str_replace(" ", "-", strtolower($name)));
        $this->name = $name;
        $this->html = "";
    }
    
    function tabSelect(){
        // Do nothing by default
        return "";
    }
    
    abstract function generateBody();
}

?>
