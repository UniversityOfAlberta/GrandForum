<?php

class SelectBox extends UIElement {

    var $options = array();
    
    function SelectBox($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->options = $options;
    }
    
    function render(){
        $html = "<select {$this->renderAttr()} name='{$this->id}'>";
        foreach($this->options as $option){
            $selected = "";
            if($this->value == $option){
                $selected = " selected";
            }
            $html .= "<option $selected>{$option}</option>";
        }
        return $html."</select>";
    }
    
}

?>
