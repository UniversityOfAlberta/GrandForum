<?php

class CalendarField extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->attr('size', 8);
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        return "<input type='text' {$this->renderAttr()} name='{$this->id}' value='{$this->value}' />
                <script type='text/javascript'>
		            $('input[name={$this->id}]').datepicker(
		                {
		                 dateFormat: 'yy-mm-dd',
		                 changeMonth: true,
                         changeYear: true,
                         showOn: 'both',
                         buttonImage: '{$wgServer}{$wgScriptPath}/skins/calendar.gif',
                         buttonImageOnly: true
		                });
                    $('input[name={$this->id}]').keydown(function(){
                        return false;
                    });
		        </script>";
    }
    
}


?>
