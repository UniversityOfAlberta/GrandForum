<?php

class NextPrevReportSection extends EULAReportSection {
    
    function render(){
        global $wgOut;
        parent::render();
        $disabled = "";
        if(!DBFunctions::DBWritable()){
            $disabled = "disabled='disabled'";
        }
        $wgOut->addHTML("<script type='text/javascript'>
            $('input[name=submit][value=Next]').before(\"<input type='submit' value='Previous' name='submit' style='width:100px;' $disabled />&nbsp;&nbsp;\");
            $('input[name=submit][value=Previous]').click(function(){
                _.defer(function(){
                    $('a.reportTab.selectedReportTab').prev().click();
                });
            });
            if($('a.reportTab.selectedReportTab').prev().length == 0){
                $('input[name=submit][value=Previous]').prop('disabled', true);
            }
            if($('a.reportTab.selectedReportTab').next().length == 0){
                $('input[name=submit][value=Next]').prop('disabled', true);
            }
        </script>");
    }
    
}

?>
