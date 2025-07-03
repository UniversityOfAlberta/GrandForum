<?php

class VerticalRadioBox extends RadioBox {
    
    var $forceKey = false;
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $options, $validations);
    }
    
    function render(){
        $html = "";
        foreach($this->options as $key => $option){
            $checked = "";
            if((!is_array($this->value) && ($this->value == str_replace("'", "&#39;", $key) || $this->value == str_replace("'", "&#39;", $option))) ||
               (is_array($this->value) && (in_array(str_replace("'", "&#39;", $key), $this->value) || in_array(str_replace("'", "&#39;", $option), $this->value)))){
                $checked = " checked";
            }
            $value = $option;
            if(is_string($key) || $this->forceKey){
                $value = $key;
            }
            $html .= "<input {$this->renderAttr()} type='radio' name='{$this->id}' value='{$value}' style='vertical-align: middle;' $checked/>&nbsp;<span style='vertical-align: middle;'>{$option}</span><br />";
        }
        return $html;
    }
    
}


?>
