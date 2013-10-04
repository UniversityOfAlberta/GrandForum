<?php

class VerticalRadioBox2 extends RadioBox {
    
    function VerticalRadioBox2($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::RadioBox($id, $name, $value, $options, $validations);
    }
    
    function render(){
        $html = "";
        foreach($this->options as $option_id => $option_lbl){
            $checked = "";
            if($this->value == $option_id){
                $checked = " checked";
            }
            $html .= "<input {$this->renderAttr()} type='radio' name='{$this->id}' value='{$option_id}' $checked/>{$option_lbl}<br />";
        }
        return $html;
    }
    
}


?>
