<?php

class PersonContributionsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonContributionsTab($person, $visibility){
        parent::AbstractTab("Contributions");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
	if(!$this->visibility['isMe']){
		return "";
	}
        $contributions = $this->person->getContributions();
	   $this->html .= "<table id='contributions_table' frame='box' rules='all'>
			<thead><tr><th>name</th>
			<th>partner</th>
			<th>start date</th>
			<th>end date</th>
			<th>cash</th>
			<th>in kind</th>
			<th>total</th></tr></thead><tbody>";
	foreach($contributions as $contribution){
	$partners = $contribution->getPartners();
	    $this->html .= "<tr><td><a href='{$contribution->getURL()}'>{$contribution->getName()}</a></td>
				<td>{$partners[0]->getOrganization()}</td>
				<td>".time2date($contribution->getStartDate(), "Y-m-d")."</td>
				<td>".time2date($contribution->getEndDate(), "Y-m-d")."</td>
				<td align=right>$".number_format($contribution->getCash())."</td>
				<td align=right>$".number_format($contribution->getKind())."</td>
				 <td align=right>$".number_format($contribution->getTotal())."</td></tr>";}
    	$this->html .= "</table></tbody><script type='text/javascript'>
			$('#contributions_table').dataTable();
	</script>";
    }
}    
?>
