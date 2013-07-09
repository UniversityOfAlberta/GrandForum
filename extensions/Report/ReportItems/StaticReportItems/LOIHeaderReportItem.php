<?php

class LOIHeaderReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        
        $loi = null;
        $loi = LOI::newFromId($this->projectId);
        $loi_name = $loi->getName();
        $full_name = $loi->getFullName();
        $description = $loi->getDescription();
        $type = $loi->getType();
        //$related_loi = $loi->getRelatedLOI();
        $lead = str_replace('<br />', ', ', $loi->getLead());
        $colead = str_replace('<br />', ', ', $loi->getCoLead());
        $champ = str_replace('<br />', ', ', $loi->getChampion());
        $primary_challenge = str_replace('<br />', ', ', $loi->getPrimaryChallenge());
        $secondary_challenge = str_replace('<br />', ', ', $loi->getSecondaryChallenge());

        $html =<<<EOF
        	<h2>{$loi_name}</h2>
        	<table style="border: 1px solid #CCCCCC; padding: 5px;">
        	<tr><th align="left">Full Name</th><td>{$full_name}</td></tr>
            <tr><th align="left">Type:</th><td>{$type}</td></tr>
            <tr><th align="left">Lead:</th><td>{$lead}</td></tr>
            <tr><th align="left">Co-Lead:</th><td>{$colead}</td></tr>
            <tr><th align="left">Champion:</th><td>{$champ}</td></tr>
           	<tr><th align="left">Primary Challenge:</th><td>{$primary_challenge}</td></tr>
            <tr><th align="left">Secondary Challenge:</th><td>{$secondary_challenge}</td></tr>
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
