<?php

class TextLabel extends Label {
    
    function TextLabel($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::Label($id, $name, $value, $validations);
        $this->attr('class', 'tooltip');
        $this->colon = "";
    }

}

?>
