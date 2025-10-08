<?php

class FieldSet extends UIElementArray {
    
    var $legend;
    
    function __construct($id, $legend=null){
        parent::__construct($id);
        $this->legend = $legend;
        $this->attr('style', 'display:inline;min-width:500px;');
    }
    
    function render(){
        $legendHtml = '';
        if (!empty($this->legend)) {
            $legendHtml = "<legend>{$this->legend}</legend>";
        }
        $html = "<fieldset {$this->renderAttr()}>{$legendHtml}";
        foreach($this->elements as $element){
            $html .= $element->render();
        }
        return $html."</fieldset>";
    }
}

?>
