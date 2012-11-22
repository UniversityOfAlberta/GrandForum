<?php

class FieldSet extends UIElementArray {
    
    var $legend;
    
    function FieldSet($id, $legend){
        parent::UIElementArray($id);
        $this->legend = $legend;
    }
    
    function render(){
        $html = "<fieldset style='display:inline;min-width:500px;'><legend>{$this->legend}</legend>";
        foreach($this->elements as $element){
            $html .= $element->render();
        }
        return $html."</fieldset>";
    }
}

?>
