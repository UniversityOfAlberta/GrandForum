<?php

class FormContainer extends UIElementArray {
    
    function __construct($id){
        parent::__construct($id);
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
