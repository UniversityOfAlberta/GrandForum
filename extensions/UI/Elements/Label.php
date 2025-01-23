<?php

class Label extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->attr('class', 'label tooltip');
    }
    
    function render(){
        $redStar = "";
        if($this->isValidationSet(VALIDATE_NOT_NULL)){
            $redStar = "<span style='color:red;'>*</span>";
        }
        return "<div id='{$this->id}' {$this->renderAttr()} title='{$this->value}'>{$this->name}:<sup>{$redStar}</sup></div>";
    }
}

?>
