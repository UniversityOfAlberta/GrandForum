<?php

class FormTableRow extends UIElementArray {
    
    function __construct($id){
        parent::__construct($id);
    }
    
    function render(){
        $html = "\n<tr {$this->renderAttr()}>";
        foreach($this->elements as $element){
            if($element instanceof Label){
                $html .= "<td style='vertical-align:top;' colspan='{$element->colspan}'>".$element->render()."</td>";
            }
            else{
                $html .= "<td class='value'>".$element->render()."</td>";
            }
        }
        return $html."</tr>";
    }
}

?>
