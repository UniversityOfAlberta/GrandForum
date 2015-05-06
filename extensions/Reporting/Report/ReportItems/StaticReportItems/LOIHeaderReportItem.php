<?php

class LOIHeaderReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        
        $revision = $this->getAttr("revision", '1');

        $loi = null;
        $loi = LOI::newFromId($this->projectId);
        $loi_name = $loi->getName();
        $full_name = $loi->getFullName();
        $description = $loi->getDescription();
        $type = $loi->getType();
       
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


        $html =<<<EOF
        	<h2>{$loi_name}</h2>
        	<table style="border: 1px solid #CCCCCC; padding: 5px;">
        	<tr><th align="left">Full Name</th><td>{$full_name}</td></tr>
            <tr><th align="left">Type:</th><td>{$type}</td></tr>
            <tr><th align="left">Lead:</th><td>{$lead}</td></tr>
            <tr><th align="left">Champion:</th><td>{$champ}</td></tr>
           	<tr><th align="left">Primary Challenge:</th><td>{$primary_challenge}</td></tr>
EOF;
        if($revision == 1){
            $html .="<tr><th align='left'>Secondary Challenge:</th><td>{$secondary_challenge}</td></tr>";
        }

        $html .=<<<EOF
            <tr><th align="left">Description:</th><td></td></tr>
            <tr><td align="left" colspan="2">{$description}</td></tr>
            </table>
            <br />
EOF;

	    $wgOut->addHTML($html);
	}
	
	function renderForPDF(){
	    $this->render();
	}
}

?>
