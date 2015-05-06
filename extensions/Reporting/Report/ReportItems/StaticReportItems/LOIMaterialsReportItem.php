<?php

class LOIMaterialsReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        //$reportType = $this->getAttr("reportType", 'HQPReport');
        //$useProject = $this->getAttr("project", false);
        //$buttonName = $this->getAttr("buttonName", "Report PDF");
        //$width = $this->getAttr("width", 'auto');

        $revision = $this->getAttr("revision", '1');
        $loi = null;
        
        $loi = LOI::newFromId($this->projectId);
        
        $person = Person::newFromId($this->personId);
        
		$full_name = $loi->getFullName();
        $description = $loi->getDescription();
        $type = $loi->getType();
        $related_loi = $loi->getRelatedLOI();
        
        if($revision == 1){
            $lead = str_replace('<br />', ', ', $loi->getLead());
        }
        else{
            $lead_person = Person::newFromNameLike($loi->lead);
            if($lead_person->getId()){
                $lead = "<a href='".$lead_person->getUrl()."'>".$lead_person->getNameForForms() ."</a>";
                if($lead_person->getUni()){
                    $lead .= "<br />".$lead_person->getUni();
                }
            }
            else{
                $lead = $loi->lead;
            }
        }

        $champ = str_replace('<br />', ', ', $loi->getChampion());
        $primary_challenge = str_replace('<br />', ', ', $loi->getPrimaryChallenge());
        $secondary_challenge = str_replace('<br />', ', ', $loi->getSecondaryChallenge());
        $loi_pdf = $loi->getLoiPdf();
        $supplemental_pdf = $loi->getSupplementalPdf();
        
        $rel_lbl = ($revision == 2)? "Initial LOI Submission(s)" : "Related LOI(s)";

        $item =<<<EOF
            <div>
            <h3>{$full_name}</h3>
            <table style="text-align: left;">
            <tr><th>Type:</th><td>{$type}</td></tr>
            <tr><th>{$rel_lbl}:</th><td>{$related_loi}</td></tr>
            <tr><th>Primary Challenge:</th><td>{$primary_challenge}</td></tr>
EOF;
        if($revision == 1){
            $item .="<tr><th>Secondary Challenge:</th><td>{$secondary_challenge}</td></tr>";
        }

        $item .=<<<EOF
            <tr><th>Lead:</th><td>{$lead}</td></tr>
            <tr><th>Champion:</th><td>{$champ}</td></tr>
            <tr><th>LOI PDF:</th><td>{$loi_pdf}</td></tr>
EOF;
        if($revision == 1){
            $item .= "<tr><th>Supplemental PDF:</th><td>{$supplemental_pdf}</td></tr>";
        }
        $item .=<<<EOF
            <tr><th>Description:</th><td></td></tr>
            <tr><td colspan="2">{$description}</td></tr>
            </table>
            </div>
EOF;

	    $wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML("");
	}
}

?>
