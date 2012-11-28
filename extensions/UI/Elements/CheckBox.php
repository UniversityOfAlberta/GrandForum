<?php

abstract class CheckBox extends UIElement {

    var $options = array();
    
    function CheckBox($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->options = $options;
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
            $html .= "<input type='checkbox' {$this->renderAttr()} name='{$this->id}[]' value='{$option}' $checked/>{$option}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        }
        return $html;
    }
    
}


?>
