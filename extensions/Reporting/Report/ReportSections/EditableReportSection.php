<?php

class EditableReportSection extends AbstractReportSection {
    
    var $autosave;
    var $reportCharLimits = true;
    
    // Creates a new EditableReportSection()
    function EditableReportSection(){
        $this->AbstractReportSection();
        $this->autosave = true;
    }
    
    // Sets whether or not to use the autosave feature
    function setAutosave($autosave){
        $this->autosave = $autosave;
    }
    
    // Sets whether or not to report this section's character limits for the business rules
    function setReportCharLimits($reportCharLimits){
        $this->reportCharLimits = $reportCharLimits;
    }
    
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
                            <div id='reportHeader'>{$number}{$this->title}<span id='reportProgress'><span style='width:{$this->getPercentComplete()}%;background-color: {$config->getValue('highlightColor')};' id='reportProgressBar'></span><span id='reportProgressLabel'>Progress ({$this->getPercentComplete()}%)</span></span></div>
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
        $allSections = $this->getParent()->sections;
        $saveText = "";
        $wgOut->addHTML("</div>
                             <hr />
                             <div id='reportFooter'>
                                <input type='submit' value='Save' name='submit' $disabled />&nbsp;<span class='autosaveSpan'></span><img id='submit_throbber' style='display:none;vertical-align:-20%;' src='../skins/Throbber.gif' />{$saveText}
                             </div>
                         </form></div>\n");
    }
    
    function getIncompleteItems(){
        $items = array();
        foreach($this->items as $item){
            if(!$item->deleted){
                $it = $item->getIncompleteItems();
                if($it != null){
                    if(is_array($it)){
                        $items = array_merge($items, $it);
                    }
                    else{
                        $items[] = $it;
                    }
                }
            }
        }
        return $items;
    }
    
    // Returns the percentage of completion for this section
    function getPercentComplete(){
        if($this->getParent()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nComplete = $this->getNComplete();
        $nFields = $this->getNFields();
        if($nFields == 0){
            return 100;
        }
        return ceil(($nComplete/max(1, $nFields))*100);
    }
    
    function getNComplete(){
        if($this->getParent()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nComplete = 0;
        foreach($this->items as $item){
            if(!$item->deleted){
                $nComplete += $item->getNComplete();
            }
        }
        return $nComplete;
    }
    
    function getNFields(){
        if($this->getParent()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nFields = 0;
        foreach($this->items as $item){
            if(!$item->deleted){
                $nFields += $item->getNfields();
            }
        }
        return $nFields;
    }
    
    function getNTextareas(){
        if($this->getParent()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nTextareas = 0;
        foreach($this->items as $item){
            if($item->deleted){
                continue;
            }
            if($item instanceof ReportItemSet){
                $nTextareas += $item->getNTextareas();
            }
            else if($item instanceof TextareaReportItem){
                $nTextareas += 1;
            }
        }
        return $nTextareas;
    }
}

?>
