<?php

class VerticalRadioBox extends RadioBox {
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $options, $validations);
    }
    
    function render(){
        $html = "";
        foreach($this->options as $option){
            $checked = "";
            if($this->value == $option){
                $checked = " checked";
            }
            $html .= "<input {$this->renderAttr()} type='radio' name='{$this->id}' value='{$option}' $checked/>{$option}<br />";
        }
        return $html;
    }
    
}


?>
