<?php

class LOIMaterialsReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        //$reportType = $this->getAttr("reportType", 'HQPReport');
        //$useProject = $this->getAttr("project", false);
        //$buttonName = $this->getAttr("buttonName", "Report PDF");
        //$width = $this->getAttr("width", 'auto');
        $loi = null;
        
        $loi = LOI::newFromId($this->projectId);
        
        $person = Person::newFromId($this->personId);
        
		$full_name = $loi->getFullName();
        $description = $loi->getDescription();
        $type = $loi->getType();
        $related_loi = $loi->getRelatedLOI();
        $lead = str_replace('<br />', ', ', $loi->getLead());
        $colead = str_replace('<br />', ', ', $loi->getCoLead());
        $champ = str_replace('<br />', ', ', $loi->getChampion());
        $primary_challenge = str_replace('<br />', ', ', $loi->getPrimaryChallenge());
        $secondary_challenge = str_replace('<br />', ', ', $loi->getSecondaryChallenge());
        $loi_pdf = $loi->getLoiPdf();
        $supplemental_pdf = $loi->getSupplementalPdf();
        
        $item =<<<EOF
            <div>
            <h3>{$full_name}</h3>
            <table style="text-align: left;">
            <tr><th>Type:</th><td>{$type}</td></tr>
            <tr><th>Related LOI:</th><td>{$related_loi}</td></tr>
            <tr><th>Primary Challenge:</th><td>{$primary_challenge}</td></tr>
            <tr><th>Secondary Challenge:</th><td>{$secondary_challenge}</td></tr>
            <tr><th>Lead:</th><td>{$lead}</td></tr>
            <tr><th>Co-Lead:</th><td>{$colead}</td></tr>
            <tr><th>Champion:</th><td>{$champ}</td></tr>
            <tr><th>LOI PDF:</th><td>{$loi_pdf}</td></tr>
            <tr><th>Supplemental PDF:</th><td>{$supplemental_pdf}</td></tr>
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
