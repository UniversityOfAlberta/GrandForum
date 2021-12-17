<?php

class EmptyElement extends UIElement {
    
    function __construct(){
        parent::__construct('', '', '', VALIDATE_NOTHING);
    }
    
    function render(){
        return "";
    }
}

?>
