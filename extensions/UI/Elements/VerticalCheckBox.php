<?php

class VerticalCheckBox extends CheckBox {
    
    function VerticalCheckBox($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::CheckBox($id, $name, $value, $options, $validations);
    }
    
    function render(){
        $html = "";
        foreach($this->options as $option){
            $checked = "";
            if(count($this->value) > 0){
                foreach($this->value as $value){
                    if($value == $option){
                        $checked = " checked";
                        break;
                    }
                }
            }
            $html .= "<input {$this->renderAttr()} id='{$this->id}_{$option}' type='checkbox' name='{$this->id}[]' value='{$option}' $checked/>{$option}<br />";
        }
        return $html;
    }
    
}


?>
