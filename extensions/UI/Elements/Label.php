<?php

class Label extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->attr('class', 'label tooltip');
        $this->colon = ":";
    }
    
    function render(){
        $redStar = "";
        if($this->isValidationSet(VALIDATE_NOT_NULL)){
            $this->attr('class', $this->attr('class') . ' required');
        }
        return "<div id='{$this->id}' {$this->renderAttr()} title='{$this->value}'>{$this->name}{$this->colon}</div>";
    }
}

?>
