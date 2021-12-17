<?php

class CustomElement extends UIElement {

    var $html = array();
    
    function __construct($id, $name, $value, $html, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->html = $html;
    }
    
    function render(){
        return $this->html;
    }
    
}

?>
