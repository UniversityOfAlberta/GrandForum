<?php

class EmptyElement extends UIElement {
    
    function EmptyElement(){
        parent::UIElement('', '', '', VALIDATE_NOTHING);
    }
    
    function render(){
        return "";
    }
}

?>
