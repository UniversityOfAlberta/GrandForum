<?php

class MultiSelectBox extends UIElement {

    var $options = array();
    var $display_format = null;

    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING, $display_format=null){
        parent::__construct($id, $name, $value, $validations);
        $this->options = $options;
        $this->attr('size', '6');
        $this->display_format = $display_format;
    }
    
    function render(){
        $html = "<select name='{$this->id}[]' {$this->renderAttr()} multiple='multiple'>";
        foreach($this->options as $key => $option){
            $selected = "";
            if(in_array($option, $this->value) || in_array($key, $this->value)){
                $selected = " selected";
            }
            if ($this->display_format === 'simple') {
                $html .= "<option $selected value='{$option}'>{$option}</option>";
            } else {
                $html .= "<option $selected value='{$key}'>{$key} - {$option}</option>";
            }        }
        return $html."</select>";
    }
    
}

?>
