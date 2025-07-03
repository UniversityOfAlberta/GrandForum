<?php

class MultiSelectBox extends UIElement {

    var $options = array();
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->options = $options;
        $this->attr('size', '6');
    }
    
    function render(){
        $html = "<select name='{$this->id}[]' {$this->renderAttr()} multiple='multiple'>";
        foreach($this->options as $key => $option){
            $selected = "";
            if(in_array($option, $this->value) || in_array($key, $this->value)){
                $selected = " selected";
            }
            $html .= "<option $selected value='{$key}'>{$key} - {$option}</option>";
        }
        return $html."</select>";
    }
    
}

?>
