<?php

class PersonSopTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonSopTab($person, $visibility){
        parent::AbstractEditableTab("SoP");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
    	$person = $this->person;
        $gsms = GsmsData::newFromUserId($person->id);
    	$url = $person->getSopPdfUrl();

        $sop = $person->getSop();
        $admissionStatus = ($sop != null) ? $sop->getFinalAdmit() : "Not Submitted";
        $this->html .= "<div name='container' style='display:flex;'>";
        if($me->isRoleAtLeast("Faculty")){
            $this->html .= "<div style='margin: 6px;padding: 0px 12px 15px 12px;'><h3>Application Decision</h3>$admissionStatus</div>";
            if ($admissionStatus == "Admit") {
                $supers = $gsms->getAssignedSupervisors();
                $supers = @$supers['q5'];
                $this->html .= "<div style='margin: 6px;padding: 0px 12px 15px 12px;'><h3>Assigned Supervisor(s)</h3>";
                if (count($supers) == 0) {
                    $this->html .= "<span style='color:#a5a5a5;'>Supervisors not yet assigned</span>";
                } else {
                    $this->html .= "<ul>";
                    foreach($supers as $sup) {
                        $this->html .= "<li>$sup</li>";
                    }
                    $this->html .= "</ul>";
                }
                $this->html .= "</div>";

                $this->html .= "<div style='margin: 6px;padding: 0px 12px 15px 12px;'><h3>Funding Provided</h3>";
                $funding = $gsms->getFunding();
                if ($funding == "") {
                    $this->html .= "<span style='color:#a5a5a5;'>Decision not yet made</span>";
                } else {
                    $this->html .= $funding;
                }
                $this->html .= "</div>";
            }
        }
        $this->html .= "</div>";

    	if(!($url === false)){
            $this->html .= "<div style='min-width:825px; max-width: 1000px; width:66%; height:500px;'><iframe style='width:100%; height:100%;' frameborder='0' src='$url'></iframe></div>";
    	} 

        return $this->html;
    }

    function generateEditBody(){
    }

    function handleEdit(){
    }
    
    function canEdit(){
	   return false;
    }
    
}
?>
