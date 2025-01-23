<?php

class CalendarField extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->attr('size', 12);
    }
    
    function render(){
        return "<input type='text' {$this->renderAttr()} name='{$this->id}' value='{$this->value}' />
                <script type='text/javascript'>
		            $('input[name={$this->id}]').datepicker(
		                {dateFormat: 'yy-mm-dd',
		                 yearRange: 'c-50:c+10',
		                 changeMonth: true,
                         changeYear: true,
                         showOn: 'both',
                         buttonImage: wgServer + wgScriptPath + '/skins/calendar.gif',
                         buttonImageOnly: true,
                         onChangeMonthYear: function (year, month, inst) {
                            var curDate = $(this).datepicker('getDate');
                            if (curDate == null)
                                return;
                            if (curDate.getYear() != year || curDate.getMonth() != month - 1) {
                                curDate.setYear(year);
                                curDate.setMonth(month - 1);
                                while(curDate.getMonth() != month -1){
                                    curDate.setDate(curDate.getDate() - 1);
                                }
                                $(this).datepicker('setDate', curDate);
                                $(this).trigger('change');
                            }
                        }
		                });
                    $('input[name={$this->id}]').keydown(function(){
                        return false;
                    });
		        </script>";
    }
    
}


?>
