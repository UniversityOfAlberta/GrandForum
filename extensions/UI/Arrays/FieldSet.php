<?php

class FieldSet extends UIElementArray {
    
    var $legend;
    
    function FieldSet($id, $legend){
        parent::UIElementArray($id);
        $this->legend = $legend;
        $this->attr('style', 'display:inline;min-width:500px;');
    }
    
    function render(){
        $html = "<fieldset {$this->renderAttr()}><legend>{$this->legend}</legend>";
        foreach($this->elements as $element){
            $html .= $element->render();
        }
        return $html."</fieldset>";
    }
}

?>
