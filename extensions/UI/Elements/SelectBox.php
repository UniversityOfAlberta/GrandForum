<?php

class SelectBox extends UIElement {

    var $options = array();
    
    function SelectBox($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->options = $options;
    }
    
    function renderSelect(){
        $html = "<select {$this->renderAttr()} name='{$this->id}'>";
        foreach($this->options as $key => $option){
            $selected = "";
            if($this->value == $option){
                $selected = " selected";
            }
            $value = $option;
            if(is_string($key)){
                $value = $key;
            }
            $html .= "<option value='".str_replace("'", "&#39;", $value)."' $selected>{$option}</option>";
        }
        $html .= "</select>";
        return $html;
    }
    
    function render(){
        return $this->renderSelect();
    }
    
}

?>
