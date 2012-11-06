<?php

class PersonNoProjectReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function getTableHTML(){
	    $reportType = $this->getAttr('reportType', 'HQPReport');
        $person = Person::newFromId($this->personId);
        $project = $this->getReport()->project;
        
        $count = 0;
        foreach($person->getProjects() as $p){
            if($project->getId() == $p->getId()){
                continue;
            }
            $count++;
        }
        
        if($count == 0){
            return "<span id='{$person->getId()}_{$project->getId()}_noProject'><b>Yes</b></span>
            <script type='text/javascript'>
                $('#{$person->getId()}_{$project->getId()}_noProject').parent().addClass('inlineWarning');
            </script>";
        }
        return "<span id='{$person->getId()}_{$project->getId()}_noProject'><b>No</b></span>";
	}
}

?>
