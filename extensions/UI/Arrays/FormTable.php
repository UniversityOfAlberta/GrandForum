<?php

class FormTable extends UIElementArray {
    
    function render(){
        $html = "<table>";
        foreach($this->elements as $element){
            $html .= $element->render();
        }
        return $html."</table>";
    }
}

?>
