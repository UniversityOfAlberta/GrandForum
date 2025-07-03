<?php

class TextareaField extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->attr('style', 'height:100px;width:400px;');
    }
    
    function render(){
        return "<textarea {$this->renderAttr()} name='{$this->id}'>{$this->value}</textarea>";
    }
    
}


?>
