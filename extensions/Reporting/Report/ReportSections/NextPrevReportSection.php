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
            $('button[name=submit][value=Next]').before(\"<button type='submit' value='Previous' name='submit' style='width:155px;' $disabled><en>Previous</en><fr>Précédent</fr></button>&nbsp;&nbsp;\");
            $('button[name=submit][value=Previous]').click(function(){
                _.defer(function(){
                    $('a.reportTab.selectedReportTab').prevAll('a:not(.disabled_lnk)').first().click();
                });
            });
            if($('a.reportTab.selectedReportTab').prevAll('a:not(.disabled_lnk)').length == 0){
                $('button[name=submit][value=Previous]').prop('disabled', true);
            }
            if($('a.reportTab.selectedReportTab').nextAll('a:not(.disabled_lnk)').length == 0){
                $('button[name=submit][value=Next]').val('Submit');
                $('input[name=submit][value=Submit]').mousedown(function(){
                    if(typeof submitInterval != 'undefined'){
                        clearInterval(submitInterval);
                    }
                    submitInterval = setInterval(function(){
                        if(!$('.autosaveSpan img').is(':visible')){
                            clearSuccess('#reportMessages');
                            addSuccess('Thank you for submitting', false, '#reportMessages');
                            clearInterval(submitInterval);
                        }
                    }, 100);
                });
            }
        </script>");
    }
    
}

?>
