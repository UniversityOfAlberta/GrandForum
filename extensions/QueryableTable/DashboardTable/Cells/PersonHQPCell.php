<?php

class PersonHQPCell extends DashboardCell {
    
    var $start;
    var $end;
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "HQP";
        $start = "0000-00-00";
        $end = "2100-00-00";
        if(count($params) == 1){
            $params[2] = $params[0];
        }
        else{
            if(isset($params[0])){
                // Start
                $start = $params[0];
                $this->start = $start;
            }
            if(isset($params[1])){
                // End
                $end = $params[1];
                $this->end = $end;
            }
        }
        if(isset($params[2])){
            $project = Project::newFromName($params[2]);
            if($project != null && $project->getName() != null){
                $this->obj = $project;
                $person = $table->obj;
                $hqps = $person->getHQPDuring($start, $end);
                $values = array();
                foreach($hqps as $hqp){
                    if($hqp->isMemberOfDuring($project, $start, $end)){
                        $uni = $hqp->getUniversity();
                        $position = ($uni['position'] != "") ? $uni['position'] : "Other";
                        $values[$position][] = $hqp->getId();
                    }
                }
                $this->setValues($values);
                $this->sortByPosition();
            }
        }
        else{
            $person = $table->obj;
            $hqps = $person->getHQPDuring($start, $end);
            $values = array();
            $tmp = array();
            foreach($hqps as $hqp){
                $university = $hqp->getUniversity();
                $position = $university['position'];
                $tmp[] = $hqp->getId();
            }
            
            foreach($tmp as $hqpId){
                $values['All'][] = $hqpId;
            }
            $this->setValues($values);
            $this->sortByPosition();
        }
    }
    
    function sortByPosition(){
        $newValues = array();
        foreach($this->values as $type => $values){
            foreach($values as $item){
                $hqp = Person::newFromId($item);
                $university = $hqp->getUniversity();
                $position = $university['position'];
                switch($position){
                    case "PostDoc":
                        $newValues[0][$type][1][] = $item;
                        break;
                    case "PhD Student":
                        $newValues[1][$type][1][] = $item;
                        break;
                    case "Masters Student":
                        $newValues[2][$type][1][] = $item;
                        break;
                    case "Undergraduate":
                        $newValues[3][$type][1][] = $item;
                        break;
                    case "Technician":
                        $newValues[4][$type][1][] = $item;
                        break;
                    default:
                    case "Other":
                        $newValues[5][$type][1][] = $item;
                        break;
                }
            }
        }
        $values = array();
        ksort($newValues);
        foreach($newValues as $value){
            foreach($value as $type => $items){
                ksort($items);
                foreach($items as $t){
                    foreach($t as $item)
                    $values[$type][] = $item;
                }
            }
        }
        $this->setValues($values);
    }
    
    function rasterize(){
        return array(PERSON_HQP, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function getHeaders(){
        return array("Projects", "Full Name", "Title", "University");
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        $table = "<table width='100%'>\n";
        foreach($this->values as $type => $values){
            $details = "";
            if(!isset($_GET['generatePDF']) && !isset($_GET['evalPDF'])){
                $details = $this->initDetailsTable($type, $this->getHeaders());
                foreach($values as $item){
                    $details .= "<tr>".$this->detailsRow($item)."</tr>\n";
                }
                $details .= "</tbody></table><br /><br />\n";
                $details .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:AddMember\");' value='Add HQP' />\n";
                $details .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:ManagePeople\");' value='Manage People' />\n";
            }
            $table .= $this->dashboardRow($type, $details);
        }
        if(count($this->values) == 0){
            $table .= "<tr><td style='text-align:right;border-width:0px;'>0</td></tr>\n";
        }
        $table .= "</table>\n";
        
        return "$table";
    }
    
    function detailsRow($item){
        global $wgServer, $wgScriptPath;
        $hqp = Person::newFromId($item);
        $uni = $hqp->getUniversity();
        if($this->start != null && $this->end != null){
            $projects = $hqp->getProjectsDuring($this->start, $this->end);
        }
        else{
            $projects = $hqp->getProjects();
        }
        $projectNames = array();
        if(is_array($projects)){
            foreach($projects as $project){
                if(!$project->isSubProject()){
                    $projectNames[] = "<a href='{$project->getUrl()}' target='_blank'>{$project->getName()}</a>";
                }
            }
        }
        $style = "";
        $inactive = "";
        if($hqp->isRole(INACTIVE)){
            $style = "background:#FEB8B8;color:#D50013;";
            $inactive = " (Inactive)";
        }

	    $posString = "";
	    if($uni['position'] != ""){
	        $posString = "{$uni['position']}";
	    }
	    
	    $uniString = "";
	    if($uni['university'] != ""){
	        $uniString = "{$uni['university']}";
	    }
	    $movedOnString = "";
	    $when = $hqp->getDegreeReceivedDate();
	    if($when != "0000-00-00 00:00:00"){
	        $thesis = $hqp->getThesis(false);
	        $movedOn = $hqp->getMovedOn();
	        $where = $movedOn['where'];
	        $when = date("M d, Y", strtotime($when));
	        if($where != ""){
	            $movedOnString .= "Moved To {$where}";
	        }
	        if($thesis != null){
	            if($where != ""){
	                $movedOnString .= " / ";
	            }
	            $movedOnString .= "Graduated";
	        }
	        if($movedOnString != ""){
	            $movedOnString .= " on {$when}";
	        }
	    }
	    $projString = "";
	    if($movedOnString != "" && count($projectNames) > 0){
	        $projString .= " / ";
	    }
	    $projString .= implode(", ", $projectNames);
        $details = "<td style='$style;'><span class='pdfnodisplay'>".implode(", ", $projectNames)."</span></td><td style='$style;'><a href='{$hqp->getUrl()}' target='_blank'>{$hqp->getReversedName()}</a>$inactive<span class='pdfOnly'>; </span></td><td style='$style;'><span class='pdfnodisplay'>{$posString}</span></td><td style='$style;'>{$uniString}<div class='pdfOnly' style='width:100%;text-align:right;'><i>{$movedOnString}{$projString}</i></div></td>";
        return $details;
    }
}

?>
