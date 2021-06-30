<?php

class Label extends UIElement {
    
    function Label($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
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
