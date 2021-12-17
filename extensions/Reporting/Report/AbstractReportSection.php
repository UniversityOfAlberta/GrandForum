<?php

abstract class AbstractReportSection {
    
    var $id;
    var $parent;
    var $instructions;
    var $permissions;
    var $name;
    var $title;
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
    var $projectId;
    var $personId;
    var $variables = array();
    
    // Creates a new AbstractReportSection
    function __construct(){
        $this->id = "";
        $this->instructions = "";
        $this->name = "";
        $this->title = "";
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
    
    function getItems(){
        return $this->items;
    }
    
    function getLimit(){
        if($this->getParent()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $limit = 0;
        foreach($this->items as $item){
            if($item->deleted){
                continue;
            }
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
            if($item->deleted){
                continue;
            }
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
            if($item->deleted){
                continue;
            }
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
            if($item->deleted){
                continue;
            }
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
            if($item->deleted){
                continue;
            }
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $nFields += $item->getEmptyFields();
                }
            }
            else if ($item instanceof TextareaReportItem){
                //if($item->getLimit() > 0){
                    if($item->getActualNChars() == 0){
                        $nFields++;
                    }
                //}
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
        $name = $this->varSubstitute($name);
        $this->name = $name;
        $this->title = $name;
    }
    
    // Sets the Title of this AbstractReportSection
    function setTitle($title){
        $title = $this->varSubstitute($title);
        $this->title = $title;
    }
    
    // Sets the tooltip 
    function setTooltip($tooltip){
        $tooltip = $this->varSubstitute($tooltip);
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
            array_splice($this->items, $position, 0, array($item));
        }
        
        $item->setPersonId($this->parent->person->getId());
    }
    
    // Deleted the given ReportItem from this ReportSection
    function deleteReportItem($item){
        foreach($this->items as $key => $it){
            if($item->id == $it->id){
                $this->items[$key]->setDeleted(true);
                //unset($this->items[$key]);
                return;
            }
        }
    }
    
    function undeleteReportItem($item){
        foreach($this->items as $key => $it){
            if($item->id == $it->id){
                $this->items[$key]->setDeleted(false);
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
            if($this->getParent()->project instanceof Project){
                if($this->getParent()->project->getName() == ""){
                    $project = "&project=".urlencode($this->getParent()->project->getId());
                }
                else{
                    $project = "&project=".urlencode($this->getParent()->project->getName());
                }
            }
            else if($this->getParent()->project instanceof Theme){
                $project = "&project=".urlencode($this->getParent()->project->getAcronym());
            }
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
        $candidate = (isset($_GET['candidate'])) ? "&candidate=".urlencode($_GET['candidate']) : "";
        $id = (isset($_GET['id'])) ? "&id=".urlencode($_GET['id']) : "";
        $wgOut->addHTML("<a title='{$this->tooltip}' class='reportTab$selected tooltip {$disabled}' id='".str_replace("&", "", str_replace("'", "", str_replace(" ", "", strip_tags($this->name))))."' href='$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getParent()->xmlName}{$project}{$candidate}{$id}&section=".urlencode(strip_tags($this->name))."{$year}'>{$this->name}</a>\n");
    }
    
    function render(){
        global $wgOut;
        if(!$this->checkPermission('r')){
            // User cannot view section
            $wgOut->addHTML("<div><div id='reportHeader'>Permission Error</div><hr /><div id='reportBody'>You are not permitted to view this section</div></div>");
            return;
        }
        $number = "";
        if(count($this->number) > 0){
            $numbers = array();
            foreach($this->number as $n){
                $numbers[] = AbstractReport::rome($n);
            }
            $number = implode(', ', $numbers).'. ';
        }
        $wgOut->addHTML("<div><div id='reportHeader'>{$number}{$this->title}</div>
        <hr />
        <div id='reportBody'>");
        if(!$this->checkPermission('w')){
            $wgOut->addHTML("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#reportMain textarea').prop('disabled', 'disabled');
                    $('#reportMain input').prop('disabled', 'disabled');
                    $('#reportMain button').prop('disabled', 'disabled');
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

        //Close up the Section and render
        $wgOut->addHTML("</div></div>");
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
        $wgOut->addHTML("<center><h1>{$number}{$this->varSubstitute($this->title)}</h1></center>");
        if($this->previewOnly){
            $wgOut->addHTML("<span style='color:#FF0000;'>(This section is not part of the document that will be reviewed by the Research Management Committee (RMC). If there is information here that you want to be considered as part of your evaluation, it should be included in a previous section. Provide the full details here. This section will be provided to your project leaders to assist with their project reporting.)</span>");
        }
        
        //Render all the ReportItems's in the section    
        foreach ($this->items as $item){
            if(!$this->getParent()->topProjectOnly || ($this->getParent()->topProjectOnly && !$item->private)){
                if(!$item->deleted){
                    $item->renderForPDF();
                }
            }
        }
    }
    
    function varSubstitute($cdata){
        $matches = array();
        preg_match_all('/{\$(.+?)}/', $cdata, $matches);
        
        foreach($matches[1] as $k => $m){
            if(isset(ReportItemCallback::$callbacks[$m])){
                $v = ReportItemCallback::call($this, $m);
                $regex = '/{\$'.$m.'}/';
                $cdata = preg_replace($regex, $v, $cdata);
            }
        }
        
        return $cdata;
    }
    
    /**
     * Returns the value of the variable with the given key
     * @param string $key The key of the variable
     * @return string The value of the variable if found
     */
    function getVariable($key){
        if(isset($this->variables[$key])){
            return $this->variables[$key];
        }
        else{
            return $this->getParent()->getVariable($key);
        }
    }
    
    /**
     * Sets the value of the variable with the given key to the given value
     * @param string $key The key of the variable
     * @param string $value The value of the variable
     * @param integer $depth The depth of the function call (should not need to ever pass this)
     * @return boolean Whether or not the variable was found
     */
    function setVariable($key, $value, $depth=0){
        if(isset($this->variables[$key])){
            $this->variables[$key] = $value;
            return true;
        }
        else{
            $found = $this->getParent()->setVariable($key, $value, $depth + 1);
            if(!$found && $depth <= 1){
                $this->variables[$key] = $value;
                return true;
            }
        }
        return false;
    }
    
}

?>
