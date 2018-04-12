<?php

class EULAReportSection extends EditableReportSection {
    
    // Saves all the blobs in this EditableReportSection
    function saveBlobs(){
        if(!$this->checkPermission('w')){
            return array();
        }
        $errors = array();
        foreach($this->items as $item){
            $errors = array_merge($errors, $item->save());
        }
        return $errors;
    }
    
    function render(){
        global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $config;
        if(!$this->checkPermission('r')){
            // User cannot view section
            $wgOut->addHTML("<div><div id='reportHeader'>Permission Error</div><hr /><div id='reportBody'>You are not permitted to view this section</div></div>");
            return;
        }
        $action = $wgTitle->getFullUrl()."?report=".urlencode($this->getParent()->xmlName)."&section=".urlencode($this->name)."&showSection";
        if($this->getParent()->project != null){
            $action .= "&project=".urlencode($this->getParent()->project->getName());
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
                            <div id='reportHeader'>{$number}{$this->title}</div>
                             <hr />
                             <div id='reportBody'>");
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
                             <hr />
                             <div id='reportFooter'>
                                <input type='submit' value='Next' name='submit' $disabled />&nbsp;<span class='autosaveSpan'></span><img id='submit_throbber' style='display:none;vertical-align:-20%;' src='../skins/Throbber.gif' />
                             </div>
                         </form></div>\n");
        $wgOut->addHTML("<script type='text/javascript'>
            $('input[name=submit]').click(function(){
                _.defer(function(){
                    $('a.reportTab.selectedReportTab').next().click();
                });
            });
        </script>");
    }
    
}

?>
