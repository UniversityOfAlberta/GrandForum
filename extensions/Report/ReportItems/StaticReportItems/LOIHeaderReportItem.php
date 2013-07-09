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
        	<table style="text-align: left; border: 1px solid #CCCCCC;">
        	<tr><th>Full Name</th><td>{$full_name}</td></tr>
            <tr><th>Type:</th><td>{$type}</td></tr>
            <tr><th>Lead:</th><td>{$lead}</td></tr>
            <tr><th>Co-Lead:</th><td>{$colead}</td></tr>
            <tr><th>Champion:</th><td>{$champ}</td></tr>
           	<tr><th>Primary Challenge:</th><td>{$primary_challenge}</td></tr>
            <tr><th>Secondary Challenge:</th><td>{$secondary_challenge}</td></tr>
            <tr><th>Description:</th><td></td></tr>
            <tr><td colspan="2">{$description}</td></tr>
            </table>
EOF;

	    $wgOut->addHTML($html);
	}
	
	function renderForPDF(){
	    $this->render();
	}
}

?>
