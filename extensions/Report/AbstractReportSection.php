<?php

abstract class AbstractReportSection {
    
    var $id;
    var $parent;
    var $instructions;
    var $permissions;
    var $name;
    var $sec;
    var $items;
    var $attributes;
    var $selected;
    var $renderPDF;
    var $previewOnly;
    var $private;
    var $pageBreak;
    var $number;
    var $tooltip;
    var $disabled;
    var $reportCallback;
    var $projectId;
    var $personId;
    
    // Creates a new AbstractReportSection
    function AbstractReportSection(){
        $this->id = "";
        $this->instructions = "";
        $this->name = "";
        $this->tooltip = "";
        $this->sec = SEC_NONE;
        $this->items = array();
        $this->attributes = array();
        $this->disabled = false;
        $this->selected = false;
        $this->renderPDF = true;
        $this->previewOnly = false;
        $this->private = false;
        $this->pageBreak = true;
        $this->number = array();
        $this->personId = 0;
        $this->projectId = 0;
        $this->reportCallback = new ReportItemCallback($this);
    }
    
    function setAttribute($key, $value){
        $this->attributes[$key] = $value;
    }
    
    function getAttr($attr, $default="", $varSubstitute=true){
        if($varSubstitute){
            $value = (isset($this->attributes[$attr])) ? $this->varSubstitute($this->attributes[$attr]) : $default;
        }
        else{
            $value = (isset($this->attributes[$attr])) ? $this->attributes[$attr] : $default;
        }
        return "$value";
    }
    
    function getParent(){
        return $this->parent;
    }
    
    function getLimit(){
        if($this->getParent()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $limit = 0;
        foreach($this->items as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $limit += $item->getLimit();
                }
            }
            else if ($item instanceof TextareaReportItem){
                if($item->getLimit() > 0){
                    $limit += $item->getLimit();
                }
            }
        }
        return $limit;
    }
    
    function getNChars(){
        if($this->getParent()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nChars = 0;
        foreach($this->items as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $nChars += $item->getNChars();
                }
            }
            else if ($item instanceof TextareaReportItem){
                if($item->getLimit() > 0){
                    $nChars += $item->getNChars();
                }
            }
        }
        return $nChars;
    }
    
    function getActualNChars(){
        if($this->getParent()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nChars = 0;
        foreach($this->items as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $nChars += $item->getActualNChars();
                }
            }
            else if ($item instanceof TextareaReportItem){
                if($item->getLimit() > 0){
                    $nChars += $item->getActualNChars();
                }
            }
        }
        return $nChars;
    }
    
    // Returns the percent of the number of chars used in all the limited textareas
    function getPercentChars(){
        $limit = $this->getLimit();
        $nChars = $this->getNChars();
        return number_format(($nChars/max(1, $limit))*100, 2);
    }
    
    function getExceedingFields(){
        if($this->getParent()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nFields = 0;
        foreach($this->items as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $nFields += $item->getExceedingFields();
                }
            }
            else if ($item instanceof TextareaReportItem){
                if($item->getLimit() > 0){
                    if($item->getActualNChars() > $item->getLimit()){
                        $nFields++;
                    }
                }
            }
        }
        return $nFields;
    }
    
    function getEmptyFields(){
        if($this->getParent()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nFields = 0;
        foreach($this->items as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $nFields += $item->getEmptyFields();
                }
            }
            else if ($item instanceof TextareaReportItem){
                if($item->getLimit() > 0){
                    if($item->getActualNChars() == 0){
                        $nFields++;
                    }
                }
            }
        }
        return $nFields;
    }
    
    // Sets the ID of the section
    function setId($id){
        $this->id = $id;
    }
    
    function setProjectId($projectId){
        $this->projectId = $projectId;
    }
    
    function setPersonId($personId){
        $this->personId = $personId;
    }
    
    // Sets the Instructions for this AbstractReportSection
    function setInstructions($instructions){
        $this->instructions = $instructions;
    }
    
    // Returns the instructions for this AbstractReportSection
    function getInstructions(){
        return $this->instructions;
    }
    
    // Sets the parent AbstractReport for this AbstractReportSection
    function setParent($report){
        $this->parent = $report;
    }
    
    // Sets the Name of this AbstractReportSection
    function setName($name){
        $this->name = $name;
    }
    
    // Sets the tooltip 
    function setTooltip($tooltip){
        $this->tooltip = $tooltip;
    }

    // Sets the disabled 
    function setDisabled($disabled){
        $this->disabled = $disabled;
    }
    
    // Sets the Blob Section of this AbstractReportSection
    function setBlobSection($sec){
        $this->sec = $sec;
    }
    
    // Sets whether or not this AbstractReportSection should be rendered when generating a pdf
    function setRenderPDF($renderPDF){
        $this->renderPDF = $renderPDF;
    }
    
    // Sets whether or not this AbstractReportSection should be rendered only when seeing the report preview
    function setPreviewOnly($preview){
        $this->previewOnly = $preview;
    }
    
    // Sets whether or not this AbstractReportSection should be private or not.
    function setPrivate($private){
        $this->private = $private;
    }
    
    function setNumber($number){
        $this->number = explode(',', $number);
    }
    
    // Sets whether or not this AbstractReportSection should have a page-break after the section when generating a pdf
    function setPageBreak($pageBreak){
        $this->pageBreak = $pageBreak;
    }
    
    // Adds a ReportItem to this AbstractReportSection
    function addReportItem($item, $position=null){
        $item->setParent($this);
        if($position == null){
            $this->items[] = $item;
        }
        else{
            array_splice($this->items, $position, 0, $item);
        }
        $item->setPersonId($this->parent->person->getId());
    }
    
    // Deleted the given ReportItem from this ReportSection
    function deleteReportItem($item){
        foreach($this->items as $key => $it){
            if($item->id == $it->id){
                unset($this->items[$key]);
                return;
            }
        }
    }
    
    // Returns the ReportItem with the given id, or null if it does not exist
    function getReportItemById($itemId){
        foreach($this->items as $item){
            if($item->id == $itemId){
                return $item;
            }
        }
        return null;
    }
    
    // Returns whether or not this section has the given $perm or not
    function checkPermission($perm){
        if($perm == 'w' && !DBFunctions::DBWritable()){
            return false;
        }
        $permissions = $this->getParent()->getSectionPermissions($this);
        return isset($permissions[$perm]);
    }
    
    // Renders the tab for this AbstractReportSection
    function renderTab(){
        global $wgOut, $wgServer, $wgScriptPath;
        $selected = "";
        if($this->selected){
            $selected = " selectedReportTab";
        }
        $project = "";
        if($this->getParent()->project != null){
            $project = "&project={$this->getParent()->project->getName()}";
        }
        $number = "";
        if(count($this->number) > 0){
            $numbers = array();
            foreach($this->number as $n){
                $numbers[] = AbstractReport::rome($n);
            }
            $number = implode(', ', $numbers).'. ';
        }
        $year = "";
        if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
            $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
        }
        $disabled = "";
        if($this->disabled){
            $disabled = "disabled_lnk";
        }
        $wgOut->addHTML("<a title='{$this->tooltip}' class='reportTab$selected tooltip {$disabled}' id='".str_replace(" ", "", $this->name)."' href='$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getParent()->xmlName}{$project}&section=".urlencode($this->name)."{$year}'>{$this->name}</a>\n");
    }
    
    function render(){
        global $wgOut;
        if(!$this->checkPermission('r')){
            // User cannot view section
            $wgOut->addHTML("<div><div id='reportHeader'>Permission Error</div><hr /><div id='reportBody'>You are not permitted to view this section</div></div>");
            return;
        }
        $projectName = "";
        $number = "";
        if($this->getParent()->project != null){
            $projectName = ": ".$this->getParent()->project->getName();
        }
        if(count($this->number) > 0){
            $numbers = array();
            foreach($this->number as $n){
                $numbers[] = AbstractReport::rome($n);
            }
            $number = implode(', ', $numbers).'. ';
        }
        $wgOut->addHTML("<div><div id='reportHeader'>{$number}{$this->name}{$projectName}</div>
        <hr />
        <div id='reportBody'>");
        if($this->getParent()->project != null && $this->getParent()->project->isDeleted()){
            $project = $this->getParent()->project;
            $date = new DateTime($project->getEffectiveDate());
            $datestr = date_format($date, 'F d, Y');
            $wgOut->addHTML("<div class='purpleInfo notQuitable'>This is a final report for the project <a target='_blank' href='{$project->getUrl()}'>{$project->getName()}</a>.  The project will be inactive, effective $datestr.</div>");
        }
        //Render all the ReportItems's in the section    
        foreach ($this->items as $item){
            if(!$this->getParent()->topProjectOnly || ($this->getParent()->topProjectOnly && !$item->private)){
                $item->render();
            }
        }

        //Close up the Section and render
        $wgOut->addHTML("</div></div>");
        if(!$this->checkPermission('w')){
            $wgOut->addHTML("<script type='text/javascript'>
                $('#reportMain textarea').prop('disabled', 'disabled');
                $('#reportMain input').prop('disabled', 'disabled');
                $('#reportMain button').prop('disabled', 'disabled');
            </script>");
        }
    }
    
    function renderForPDF(){
        global $wgOut;
        $number = "";
        if(count($this->number) > 0){
            $numbers = array();
            foreach($this->number as $n){
                $numbers[] = AbstractReport::rome($n);
            }
            $number = implode(', ', $numbers).'. ';
        }
        $wgOut->addHTML("<center><h1>{$number}{$this->varSubstitute($this->name)}</h1></center>");
        if($this->previewOnly){
            $wgOut->addHTML("<span style='color:#FF0000;'>(This section is not part of the document that will be reviewed by the Research Management Committee (RMC). If there is information here that you want to be considered as part of your evaluation, it should be included in a previous section. Provide the full details here. This section will be provided to your project leaders to assist with their project reporting.)</span>");
        }
        
        //Render all the ReportItems's in the section    
        foreach ($this->items as $item){
            if(!$this->getParent()->topProjectOnly || ($this->getParent()->topProjectOnly && !$item->private)){
                $item->renderForPDF();
            }
        }
    }
    
    function varSubstitute($cdata){
        $matches = array();
        preg_match_all('/{\$(.+?)}/', $cdata, $matches);
        
        foreach($matches[1] as $k => $m){
            if(isset(ReportItemCallback::$callbacks[$m])){
                $v = call_user_func(array($this->reportCallback, ReportItemCallback::$callbacks[$m]));
                $regex = '/{\$'.$m.'}/';
                $cdata = preg_replace($regex, $v, $cdata);
            }
        }
        
        return $cdata;
    }
    
}

?>
