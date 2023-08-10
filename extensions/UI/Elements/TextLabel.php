<?php

class TextLabel extends Label {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->attr('class', 'tooltip');
        $this->colon = "";
    }

}

?>
