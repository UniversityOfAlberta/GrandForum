<?php

class UIElementArray extends UIElement {
    
    var $elements;
    
    function UIElementArray($id){
        $this->id = $id;
        $this->elements = array();
    }
    
    // Prepends $element to the beginning this UIElementArray
    function prepend($element){
        $newArray = array();
        $newArray[] = $element;
        $this->elements = array_merge($newArray, $this->elements);
        $element->parent = $this;
        return $this;
    }
    
    // Appends $element to the end of this UIElementArray
    function append($element){
        $this->elements[] = $element;
        $element->parent = $this;
        return $this;
    }
    
    // Removes the UIElement with the id $elementId
    function remove($elementId=""){
        if($elementId == "" && $this->parent()){
            $this->parent()->remove($this->id);
        }
        $newArray = array();
        foreach($this->elements as $element){
            if($element->id != $elementId){
                $newArray[] = $element;
            }
            else{
                $element->parent = null;
            }
        }
        $this->elements = $newArray;
    }
    
    // Inserts $element before the UIElement with the id $beforeId
    function insertBefore($element, $beforeId){
        $newElements = array();
        foreach($this->elements as $el){
            if($el->id == $beforeId){
                $newElements[] = $element;
                $element->parent = $this;
            }
            $newElements[] = $el;
        }
        $this->elements = $newElements;
        return $this;
    }
    
    // Inserts $element after the UIElement with the id $afterId
    function insertAfter($element, $afterId){
        $newElements = array();
        foreach($this->elements as $el){
            $newElements[] = $el;
            if($el->id == $afterId){
                $newElements[] = $element;
                $element->parent = $this;
            }
        }
        $this->elements = $newElements;
        return $this;
    }
    
    // Returns the element with the id $id
    // If there are two elements with the same id, the first one is returned
    function getElementById($id){
        foreach($this->elements as $element){
            if($element->id == $id){
                return $element;
            }
            else if($element instanceof UIElementArray){
                $el = $element->getElementById($id);
                if($el != null){
                    return $el;
                }
            }
        }
        return null;
    }
    
    // Returns the element with the name $name
    // If there are two elements with the same name, the first one is returned
    function getElementByName($name){
        foreach($this->elements as $element){
            if($element->name == $name){
                return $element;
            }
            else if($element instanceof UIElementArray){
                $el = $element->getElementByName($name);
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
