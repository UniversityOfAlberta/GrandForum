<?php

abstract class RadioBox extends UIElement {

    var $options = array();
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->options = $options;
    }
    
    function render(){
        $html = "";
        foreach($this->options as $option){
            $checked = "";
            if($this->value == $option){
                $checked = " checked";
            }
            $html .= "<input type='radio' {$this->renderAttr()} name='{$this->id}' value='{$option}' $checked/>{$option}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        }
        return $html;
    }
    
}


?>
