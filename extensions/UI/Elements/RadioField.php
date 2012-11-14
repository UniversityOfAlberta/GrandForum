<?php

class RadioField extends UIElement {

    var $options = array();
    
    function RadioField($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->options = $options;
    }
    
    function render(){
        $html = "";
        foreach($this->options as $option){
            $checked = "";
            if($this->value == $option){
                $checked = " checked";
            }
            $html .= "<input type='radio' name='{$this->id}' value='{$option}' $checked/>{$option}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        }
        return $html;
    }
    
}


?>
