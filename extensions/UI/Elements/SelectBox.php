<?php

class SelectBox extends UIElement {

    var $options = array();
    var $forceKey = false;
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->options = $options;
    }
    
    function renderSelect(){
        $html = "<select {$this->renderAttr()} name='{$this->id}' id='{$this->id}'>";
        $selectedFound = false;
        foreach($this->options as $key => $option){
            $selected = "";
            if((!is_array($this->value) && ($this->value == str_replace("'", "&#39;", $key) || $this->value == str_replace("'", "&#39;", $option))) ||
               (is_array($this->value) && (in_array(str_replace("'", "&#39;", $key), $this->value) || in_array(str_replace("'", "&#39;", $option), $this->value)))){
                $selected = " selected";
                $selectedFound = true;
            }
            $value = $option;
            if(is_string($key) || $this->forceKey){
                $value = $key;
            }
            $value = sanitizeInput($value);
            $html .= "<option value='".str_replace("'", "&#39;", $value)."' $selected>{$option}</option>";
        }
        if(!is_array($this->value)){
            if(!$selectedFound && $this->value != "" && !in_array($this->value, $this->options)){
                $value = sanitizeInput($this->value);
                $html .= "<option value='".str_replace("'", "&#39;", $value)."' selected>{$value}</option>";
            }
        }
        else{
            foreach($this->value as $value){
                if(!$selectedFound && $value != "" && !in_array($value, $this->options)){
                    $value = sanitizeInput($value);
                    $html .= "<option value='".str_replace("'", "&#39;", $value)."' selected>{$value}</option>";
                }
            }
        }
        $html .= "</select>";
        return $html;
    }
    
    function render(){
        return $this->renderSelect();
    }
    
}

?>
