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
        $lead = $loi->getLead();
        $colead = $loi->getCoLead();
        $champ = $loi->getChampion();
        $primary_challenge = $loi->getPrimaryChallenge();
        $secondary_challenge = $loi->getSecondaryChallenge();
        $loi_pdf = $loi->getLoiPdf();
        $supplemental_pdf = $loi->getSupplementalPdf();
        
        $item =<<<EOF
            <div>
            <h3>{$full_name}</h3>
            <p>{$description}</p>
            <table width="100%" cellspacing='1' cellpadding='3' frame='box' rules='all'>
            <tr>
            <th>Type</th>
            <th>Related LOI</th>
            <th>Lead</th>
            <th>Co-Lead</th>
            <th>Champion</th>
            <th>Challenges</th>
            <th>LOI Files</th>
            </tr>
            <tr>
            <td>{$type}</td>
            <td>{$related_loi}</td>
            <td>{$lead}</td>
            <td>{$colead}</td>
            <td>{$champ}</td>
            <td>
            <p>
            <b>Primary:</b><br />
            {$primary_challenge}
            </p>
            <p>
            <b>Secondary:</b><br />
            {$secondary_challenge}
            </p>
            </td>
            <td>
            <b>LOI: {$loi_pdf}</b><br /><br />
            <b>Supplemental: {$supplemental_pdf}</b>
            </td>
            </tr>  
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
