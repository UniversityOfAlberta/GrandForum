<?php

class FormContainer extends UIElementArray {
    
    function FormContainer($id){
        parent::UIElementArray($id);
    }
    
    function render(){
        $html = "<div {$this->renderAttr()}>";
        foreach($this->elements as $element){
            $html .= $element->render();
        }
        return $html."</div>";
    }
}

?>
