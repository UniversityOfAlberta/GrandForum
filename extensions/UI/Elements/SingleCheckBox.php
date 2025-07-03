<?php

class SingleCheckBox extends CheckBox {
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $options, $validations);
    }
    
    function render(){
        $html = "";
        foreach($this->options as $key => $option){
            $label = (!is_numeric($key)) ? $key : $option;
            $checked = ($this->value == $option) ? " checked" : "";
            $html .= "<input {$this->renderAttr()} id='{$this->id}_{$option}' type='checkbox' name='{$this->id}' value='{$option}' $checked/>{$label}<br />";
            break; // Should only do one
        }
        return $html;
    }
    
}


?>
