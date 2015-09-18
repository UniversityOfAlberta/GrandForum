<?php

class PersonContributionsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonContributionsTab($person, $visibility){
        parent::AbstractTab("Grants");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
	if(!$this->visibility['isMe']){
		return "";
	}
        $contributions = $this->person->getContributions();
	   $this->html .= "<table id='contributions_table' frame='box' rules='all'>
			<thead><tr><th style='white-space:nowrap;'>Name</th>
			<th style='white-space:nowrap;'>Partner</th>
			<th style='white-space:nowrap;'>Start Date</th>
			<th style='white-space:nowrap;'>End Date</th>
			<th style='white-space:nowrap;'>Cash</th>
			<th style='white-space:nowrap;'>In Kind</th>
			<th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";
	foreach($contributions as $contribution){
	$partners = $contribution->getPartners();
	    $this->html .= "<tr><td><a href='{$contribution->getURL()}'>{$contribution->getName()}</a></td>
				<td>{$partners[0]->getOrganization()}</td>
				<td style='white-space:nowrap;'>".time2date($contribution->getStartDate(), "Y-m-d")."</td>
				<td style='white-space:nowrap;'>".time2date($contribution->getEndDate(), "Y-m-d")."</td>
				<td align=right>$".number_format($contribution->getCash())."</td>
				<td align=right>$".number_format($contribution->getKind())."</td>
				 <td align=right>$".number_format($contribution->getTotal())."</td></tr>";}
    	$this->html .= "</table></tbody><script type='text/javascript'>
			$('#contributions_table').dataTable();
	</script>";
    }
}    
?>
