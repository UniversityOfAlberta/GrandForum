<?php

class ComboBox extends SelectBox {

    var $options = array();
    
    function ComboBox($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::SelectBox($id, $name, $value, $options, $validations);
    }
    
    function render(){
        $html = $this->renderSelect();
        $html .= "<script type='text/javascript'>
            $('select[name={$this->id}]').combobox();
        </script>";
        return $html;
    }
    
}

?>
