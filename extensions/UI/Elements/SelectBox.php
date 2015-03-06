<?php

class SelectBox extends UIElement {

    var $options = array();
    
    function SelectBox($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->options = $options;
    }
    
    function renderSelect(){
        $html = "<select {$this->renderAttr()} name='{$this->id}'>";
        $selectedFound = false;
        foreach($this->options as $key => $option){
            $selected = "";
            if($this->value == str_replace("'", "&#39;", $key)){
                $selected = " selected";
                $selectedFound = true;
            }
            $value = $option;
            if(is_string($key)){
                $value = $key;
            }
            $html .= "<option value='".str_replace("'", "&#39;", $value)."' $selected>{$option}</option>";
        }
        if(!$selectedFound && $this->value != ""){
            $html .= "<option value='".str_replace("'", "&#39;", $this->value)."' selected>{$this->value}</option>";
        }
        $html .= "</select>";
        return $html;
    }
    
    function render(){
        return $this->renderSelect();
    }
    
}

?>
