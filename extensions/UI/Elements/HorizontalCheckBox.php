<?php

class HorizontalCheckBox extends CheckBox {
    
    function HorizontalCheckBox($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
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
            $html .= "<input type='checkbox' {$this->renderAttr()} name='{$this->id}[]' value='{$option}' $checked/>{$option}&nbsp;&nbsp;&nbsp;&nbsp;";
        }
        return $html;
    }
    
}


?>
