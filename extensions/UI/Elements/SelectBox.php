<?php

class SelectBox extends UIElement {

    var $options = array();
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->options = $options;
    }
    
    function renderSelect(){
        $html = "<select {$this->renderAttr()} name='{$this->id}' id='{$this->id}'>";
        $selectedFound = false;
        foreach($this->options as $key => $option){
            $selected = "";
            if($this->value == str_replace("'", "&#39;", $key) || $this->value == str_replace("'", "&#39;", $option)){
                $selected = " selected";
                $selectedFound = true;
            }
            $value = $option;
            if(is_string($key)){
                $value = $key;
            }
            $value = sanitizeInput($value);
            $html .= "<option value='".str_replace("'", "&#39;", $value)."' $selected>{$option}</option>";
        }
        if(!$selectedFound && $this->value != "" && !in_array($this->value, $this->options)){
            $value = sanitizeInput($this->value);
            $html .= "<option value='".str_replace("'", "&#39;", $value)."' selected>{$value}</option>";
        }
        $html .= "</select>";
        return $html;
    }
    
    function render(){
        return $this->renderSelect();
    }
    
}

?>
