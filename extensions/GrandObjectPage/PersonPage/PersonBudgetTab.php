<?php

class PersonBudgetTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonBudgetTab($person, $visibility){
        parent::AbstractTab("Budget");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        global $wgUser, $wgServer, $wgScriptPath;
        $year= YEAR-1;
	    $uid = $this->person->getId();
		$blob_type=BLOB_EXCEL;
		$rptype = RP_RESEARCHER;
    	$section = RES_ALLOC_BUDGET;
    	$item = 0;
    	$subitem = 0;
		$rep_addr = ReportBlob::create_address($rptype,$section,$item,$subitem);
		$budget_blob = new ReportBlob($blob_type, $year, $uid, 0);
        if(isset($_FILES['budget']) && file_exists($_FILES['budget']['tmp_name'])){
            $budget = file_get_contents($_FILES['budget']['tmp_name']);
            $budget_blob->store($budget, $rep_addr);
        }
    }
    
    function canEdit(){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
        return ($this->visibility['isMe'] || $me->isRoleAtLeast(MANAGER));
    }

    function generateBody(){
        $this->showbudget($this->person, $this->visibility);
        return $this->html;
    }
    
    function generateEditBody(){
	    global $wgUser, $wgServer, $wgScriptPath;
		// Allow user to download the budget template
		$me = Person::newFromId($wgUser->getId());
	    $this->html .= "<div>";
	    $this->html .= "<h2>Download Budget Template</h2>
	                    This budget is for the allocated, or accepted budget for ".YEAR."-".(YEAR+1).".
                        <ul>
                            <li><a href='$wgServer$wgScriptPath/data/GRAND Researcher Budget Allocated (2013-14).xls'>".YEAR."-".(YEAR+1)." Budget Template</a></li>
                        </ul>";
	
        $this->html .= "<h2>Budget Upload</h2>
              <input type='file' name='budget' />";
	
	    $this->html .= "</div>";            
	    $budget = $this->person->getAllocatedBudget(YEAR-1);
	
	    // Show a preview of the budget
	    $this->html .= "<h2>".YEAR." Budget Preview</h2>";
	    if($budget !== null){
	        $this->html .= $budget->copy()->filterCols(V_PROJ, array(""))->render();
	    }
	    else{
	        $this->html .= "You have not yet uploaded a budget";
	    }
    }
    
    /*
     * Displays the budget for this user
     */
    function showBudget($person, $visibility){
        global $wgOut, $wgUser, $projectPhaseDates;
        $me = Person::newFromId($wgUser->getId());
        if($this->visibility['isMe'] || $me->isRoleAtLeast(MANAGER)){
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#budgetAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
            $this->html .= "<div id='budgetAccordion'>";
            for($i=YEAR; $i >= (substr($projectPhaseDates[1], 0, 4)+1); $i--){
                $this->html .= "<h3><a href='#'>".$i."</a></h3><div>";
                $budget = $person->getAllocatedBudget($i-1);
                if($budget != null){
                    $budget = $budget->copy()->filterCols(V_PROJ, array(""));
                    $this->html .= $budget->render();
                }
                else {
                    $this->html .= "You have not uploaded a budget for $i";
                }
                $this->html .= "</div>";
            }
            $this->html .= "</div>";
        }
    }
    
}
?>
