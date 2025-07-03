<?php

class FormTable extends UIElementArray {
    
    function __construct($id){
        parent::__construct($id);
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
