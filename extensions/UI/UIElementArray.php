<?php

class UIElementArray extends UIElement {
    
    var $elements;
    
    function __construct($id){
        parent::__construct($id, $id, "", VALIDATE_NOTHING);
        $this->id = $id;
        $this->elements = array();
    }
    
    /**
     * Prepends the UIElement to the beginning of this UIElementArray
     * @param UIElement $element The element to append
     * @return UIElementArray this UIElementArray
     */
    function prepend($element){
        $newArray = array();
        $newArray[] = $element;
        $this->elements = array_merge($newArray, $this->elements);
        $element->parent = $this;
        return $this;
    }
    
    /**
     * Appends the UIElement to the end of this UIElementArray
     * @param UIElement $element The element to append
     * @return UIElementArray this UIElementArray
     */
    function append($element){
        $this->elements[] = $element;
        $element->parent = $this;
        return $this;
    }
    
    /**
     * Removes the UIElement with the given id
     * @param string $elementId The id of the UIElement to remove
     */
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
    
    /**
     * Inserts a UIElement before the UIElement with the given id
     * @param UIElement $element The UIElement to insert
     * @param string $beforeId The id of the UIElement to add before
     * @return UIElementArray this UIElementArray
     */
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
    
    /**
     * Inserts a UIElement after the UIElement with the given id
     * @param UIElement $element The UIElement to insert
     * @param string $beforeId The id of the UIElement to add after
     * @return UIElementArray this UIElementArray
     */
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
    
    /**
     * Returns the UIElement with the given id
     * If there are two elements with the same id, the first one is returned
     * @param string $id The id of the UIElement
     * @return UIElement The UIElement with the given id
     */
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
    
    /**
     * Returns the UIElement with the given name
     * If there are two elements with the same name, the first one is returned
     * @param string $name The name of the UIElement
     * @return UIElement The UIElement with the given name
     */
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
        $result = true;
        foreach($this->elements as $element){
            $result = ($element->validate()) && $result;
        }
        return $result;
    }
}

?>
