<?php

class FormTableRow extends UIElementArray {
    
    function render(){
        $html = "\n<tr>";
        foreach($this->elements as $element){
            $html .= "<td>".$element->render()."</td>";
        }
        return $html."</tr>";
    }
}

?>
