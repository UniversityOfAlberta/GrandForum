<?php

class CalendarField extends UIElement {
    
    function CalendarField($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->size = 3;
    }
    
    function render(){
        return "<input type='text' size='12' name='{$this->id}' value='{$this->value}' />
                <script type='text/javascript'>
		            $('input[name={$this->id}]').datepicker(
		                {dateFormat: 'yy-mm-dd',
		                 changeMonth: true,
                         changeYear: true
		                });
                    $('input[name={$this->id}]').keydown(function(){
                        return false;
                    });
		        </script>";
    }
    
}


?>
