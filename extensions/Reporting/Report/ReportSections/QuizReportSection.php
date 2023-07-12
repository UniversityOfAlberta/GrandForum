<?php

class QuizReportSection extends EditableReportSection {
    
    function render(){
        global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $config;
        if(!$this->checkPermission('r')){
            // User cannot view section
            $wgOut->addHTML("<div><div id='reportHeader'>Permission Error</div><hr /><div id='reportBody'>You are not permitted to view this section</div></div>");
            return;
        }
        $action = $wgTitle->getFullUrl()."?report=".urlencode($this->getParent()->xmlName)."&section=".urlencode($this->name)."&showSection";
        if($this->getParent()->project != null){
            if($this->getParent()->project instanceof Project){
                if($this->getParent()->project->getName() == ""){
                    $action .= "&project=".urlencode($this->getParent()->project->getId());
                }
                else{
                    $action .= "&project=".urlencode($this->getParent()->project->getName());
                }
            }
            else if($this->getParent()->project instanceof Theme){
                $action .= "&project=".urlencode($this->getParent()->project->getAcronym());
            }
        }
        $autosave = " class='noautosave'";
        if($this->autosave && $this->checkPermission('w') && DBFunctions::DBWritable()){
            $autosave = " class='autosave'";
        }
        $number = "";
        if(count($this->number) > 0){
            $numbers = array();
            foreach($this->number as $n){
                $numbers[] = AbstractReport::rome($n);
            }
            $number = implode(', ', $numbers).'. ';
        }
        
        $wgOut->addHTML("<div><form action='$action' autocomplete='off' method='post' name='report' enctype='multipart/form-data'$autosave>
                             <div id='reportBody' style='min-height: 500px;'>
                             <div id='reportHeader' style='margin:0.5em 0 0.5em 0; font-size:2em; line-height: 1em; font-weight: bold;'>{$number}{$this->title}</div>");
        if(!$this->checkPermission('w') || !DBFunctions::DBWritable()){
            $wgOut->addHTML("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#reportMain textarea').prop('disabled', 'disabled');
                    $('#reportMain input').prop('disabled', 'disabled');
                    $('#reportMain button').prop('disabled', 'disabled');
                    $('#reportMain select').prop('disabled', 'disabled');
                    $('#reportMain a.custom-combobox-toggle').hide();
                });
            </script>");
        }
        //Render all the ReportItems's in the section    
        foreach ($this->items as $item){
            if(!$this->getParent()->topProjectOnly || ($this->getParent()->topProjectOnly && !$item->private)){
                if(!$item->deleted){
                    $item->render();
                }
            }
        }
        $disabled = "";
        if(!DBFunctions::DBWritable()){
            $disabled = "disabled='disabled'";
        }
        $wgOut->addHTML("</div>
                            <div id='reportFooter'>
                                <button type='submit' value='Next' name='submit' style='width:145px;' $disabled>
                                    <en>Next</en><fr>Suivant</fr>
                                </button>&nbsp;<span class='autosaveSpan'></span><img id='submit_throbber' style='display:none;vertical-align:-20%;' src='../skins/Throbber.gif' />
                             </div>
                             <img class='quiz-footer' src='{$wgServer}{$wgScriptPath}/skins/Quiz.png' />
                         </form></div>\n");
        $wgOut->addHTML("<script type='text/javascript'>
            $('button[name=submit][value=Next]').click(function(){
                _.defer(function(){
                    $('a.reportTab.selectedReportTab').next().click();
                });
            });
            $('#reportBody').after(\"<div id='reportMessages'></div>\");
            $('button[name=submit][value=Next]').before(\"<button type='submit' value='Previous' name='submit' style='width:145px;' $disabled><en>Previous</en><fr>Précédent</fr></button>&nbsp;&nbsp;\");
            $('button[name=submit][value=Previous]').click(function(){
                _.defer(function(){
                    $('a.reportTab.selectedReportTab').prev().click();
                });
            });
            if($('a.reportTab.selectedReportTab').prev('a').length == 0){
                $('button[name=submit][value=Previous]').prop('disabled', true);
            }
            if($('a.reportTab.selectedReportTab').next('a').length == 0){
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
            dc.radio('{$this->id}', 'input[type=radio]');
        </script>");
    }
    
}

?>
