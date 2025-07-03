<?php

abstract class CheckBox extends UIElement {

    var $options = array();
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->options = $options;
    }
    
    function render(){
        $html = "";
        foreach($this->options as $key => $option){
            $label = $option;
            if(!is_numeric($key)){
                $label = $key;
            }
            $checked = "";
            if(count($this->value) > 0){
                foreach($this->value as $value){
                    if($value == $option){
                        $checked = " checked";
                        break;
                    }
                }
            }
            $html .= "<input type='checkbox' {$this->renderAttr()} name='{$this->id}[]' value='{$option}' $checked/>{$label}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        }
        return $html;
    }
    
}


?>
