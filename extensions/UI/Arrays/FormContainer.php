<?php

class FormContainer extends UIElementArray {
    
    function render(){
        $html = "<div>";
        foreach($this->elements as $element){
            $html .= $element->render();
        }
        return $html."</div>";
    }
}

?>
