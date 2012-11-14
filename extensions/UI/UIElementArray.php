<?php

class UIElementArray {
    
    var $elements;
    
    function UIElementArray(){
        $this->elements = array();
    }
    
    function addElement($element){
        $this->elements[] = $element;
    }
    
    function getElementById($id){
        foreach($this->elements as $element){
            if($element instanceof UIElement && $element->id == $id){
                return $element;
            }
            else if(!($element instanceof UIElement)){
                $el = $element->getElementById($id);
                if($el != null){
                    return $el;
                }
            }
        }
        return null;
    }
    
    function render(){
        $html = "";
        foreach($this->elements as $element){
            $html .= $element->render();
        }
        return $html;
    }
    
    function reset(){
        foreach($this->elements as $element){
            $element->reset();
        }
    }
    
    function validate(){
        $fails = array();
        foreach($this->elements as $element){
            $fails = array_merge($fails, $element->validate());
        }
        return $fails;
    }
}

?>
