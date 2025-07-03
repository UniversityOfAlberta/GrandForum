<?php

class VerticalCheckBox extends CheckBox {
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $options, $validations);
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
            $html .= "<input {$this->renderAttr()} id='{$this->id}_{$option}' style='vertical-align:middle;' type='checkbox' name='{$this->id}[]' value='{$option}' $checked/><span style='vertical-align:middle; margin-left:0.5em;'>{$label}</span><br />";
        }
        return $html;
    }
    
}


?>
