<?php

class ComboBox extends SelectBox {

    var $options = array();
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        if(isset($_POST[str_replace("[]", "", $id)])){
            $post = $_POST[str_replace("[]", "", $id)];
            if(is_array($post)){
                $_POST[str_replace("[]", "", $id)] = array();
                foreach($post as $key => $p){
                    if($key % 2 == 1){
                        $_POST[str_replace("[]", "", $id)][] = $p;
                    }
                }
            }
        }
        parent::__construct($id, $name, $value, $options, $validations);
    }
    
    function render(){
        $html = "<span>".$this->renderSelect()."</span>";
        $html .= "<script type='text/javascript'>
            $('select[name=\"{$this->id}\"]').combobox();
        </script>";
        return $html;
    }
    
}

?>
