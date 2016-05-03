<?php

class HorizontalRadioBox extends RadioBox {
    
    function HorizontalRadioBox($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::RadioBox($id, $name, $value, $options, $validations);
    }
    
    function render(){
        $html = "";
	$i=0;
        foreach($this->options as $option){
            $checked = "";
            if($this->value == $option){
                $checked = " checked";
            }
            $html .= "<input type='radio' {$this->renderAttr()} id='{$this->id}$i' name='{$this->id}' value='{$option}' $checked/>{$option}&nbsp;&nbsp;&nbsp;&nbsp;";
	    $i++;
        }
        return $html;
    }
    
}


?>
