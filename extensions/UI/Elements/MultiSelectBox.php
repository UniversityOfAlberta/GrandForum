<?php

class MultiSelectBox extends UIElement {

    var $options = array();
    
    function MultiSelectBox($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->options = $options;
    }
    
    function render(){
        $html = "<select name='{$this->id}' size='6' multiple='multiple'>";
        foreach($this->options as $option){
            $selected = "";
            if($this->value == $option){
                $selected = " selected";
            }
            $html .= "<option $selected>{$option}</option>";
        }
        return $html."</select>";
    }
    
}

?>
