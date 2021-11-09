<?php

class EditableReportSection extends AbstractReportSection {
    
    var $autosave;
    var $reportCharLimits = true;
    var $saveText = "Save";
    
    // Creates a new EditableReportSection()
    function EditableReportSection(){
        $this->AbstractReportSection();
        $this->autosave = true;
    }
    
    // Sets whether or not to use the autosave feature
    function setAutosave($autosave){
        $this->autosave = str_replace("'", "&#39;", $autosave);
    }
    
    // Sets whether or not to report this section's character limits for the business rules
    function setReportCharLimits($reportCharLimits){
        $this->reportCharLimits = $reportCharLimits;
    }
    
    // Sets the text that shows up on the "Save" button
    function setSaveText($saveText){
        $this->saveText = $saveText;
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
        $candidate = (isset($_GET['candidate'])) ? "&candidate=".urlencode($_GET['candidate']) : "";
        $id = (isset($_GET['id'])) ? "&id=".urlencode($_GET['id']) : "";
        $action .= "{$candidate}{$id}";
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
        
        $showProgress = (strtolower($this->getAttr('showProgress', 'false')) == 'true') ? "<span id='reportProgress'><span style='width:{$this->getPercentComplete()}%;background-color: {$config->getValue('highlightColor')};' id='reportProgressBar'></span><span id='reportProgressLabel'><span class='en'>Section Progress</span><span class='fr'>Complétion de la section</span> ({$this->getPercentComplete()}%)</span></span>" : "";
        
        $wgOut->addHTML("<div><form action='$action' autocomplete='off' method='post' name='report' enctype='multipart/form-data'$autosave>
                             <div id='reportHeader'>{$number}{$this->title}{$showProgress}</div>
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
        foreach($allSections as $section){
            if($section->name == "Submit" && $section->checkPermission('r')){
                $saveText = "<br /><small>Once you have saved and reviewed your text you will need to generate/submit your report by going to the <a style='cursor:pointer;' onclick=\"$('a#Submit').click()\">Submit</a> section.</small>";
            }
        }
        $wgOut->addHTML("</div>
                             <hr />
                             <div id='reportFooter'>
                                <button type='submit' value='Save' name='submit' $disabled>{$this->saveText}</button>&nbsp;<span class='autosaveSpan'></span><img id='submit_throbber' style='display:none;vertical-align:-20%;' src='../skins/Throbber.gif' />{$saveText}
                             </div>
                         </form></div>\n");
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
