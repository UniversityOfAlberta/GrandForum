<?php

abstract class AbstractTab {

    var $html;
    var $id;
    var $name;

    function __construct($name){
        $this->id = str_replace("'", "", str_replace("/", "-", str_replace(" ", "-", strtolower($name))));
        $this->name = $name;
        $this->html = "";
    }
    
    function tabSelect(){
        // Do nothing by default
        return "";
    }
    
    function canGeneratePDF(){
        return false;
    }
    
    function generatePDFBody(){
        $this->generateBody();
    }
    
    abstract function generateBody();
}

?>
