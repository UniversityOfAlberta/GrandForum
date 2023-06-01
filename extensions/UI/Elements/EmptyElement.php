<?php

class EmptyElement extends UIElement {
    
    function __construct(){
        parent::__construct('', '', '', VALIDATE_NOTHING);
    }
    
    function render(){
        return "<span style='user-select: none;'>&nbsp;</span>";
    }
}

?>
