<?php

class CustomElement extends UIElement {

    var $html = array();
    
    function CustomElement($id, $name, $value, $html, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->html = $html;
    }
    
    function render(){
        return $this->html;
    }
    
}

?>