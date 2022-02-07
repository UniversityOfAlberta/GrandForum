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
            $('#reportBody').after(\"<div id='reportMessages'></div>\");
            $('input[name=submit][value=Next]').before(\"<input type='submit' value='Previous' name='submit' style='width:100px;' $disabled />&nbsp;&nbsp;\");
            $('input[name=submit][value=Previous]').click(function(){
                _.defer(function(){
                    $('a.reportTab.selectedReportTab').prev().click();
                });
            });
            if($('a.reportTab.selectedReportTab').prev('a').length == 0){
                $('input[name=submit][value=Previous]').prop('disabled', true);
            }
            if($('a.reportTab.selectedReportTab').next('a').length == 0){
                $('input[name=submit][value=Next]').val('Submit');
                $('input[name=submit][value=Submit]').click(function(){
                    if(typeof submitInterval != 'undefined'){
                        clearInterval(submitInterval);
                    }
                    submitInterval = setInterval(function(){
                        if(!$('#submit_throbber').is(':visible')){
                            clearSuccess('#reportMessages');
                            addSuccess('Thank-you for submitting', false, '#reportMessages');
                            clearInterval(submitInterval);
                        }
                    }, 100);
                });
            }
        </script>");
    }
    
}

?>
