<?php

class FormTable extends UIElementArray {
    
    function FormTable($id){
        parent::UIElementArray($id);
    }
    
    function render(){
        $html = "<table {$this->renderAttr()}>";
        foreach($this->elements as $element){
            $html .= $element->render();
        }
        return $html."</table>";
    }
}

?>
