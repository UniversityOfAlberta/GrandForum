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
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        $wgOut->addScript(
                "<script type='text/javascript'>
                $(document).ready(function(){
                    $('.grantAccordion').accordion({autoHeight: false, collapsible: true, active:false});
                });
                </script>"
            );
	$this->html ="
	    <div class='grantAccordion'>
                <h3><a href='#'>UoA Grants 3.0</a></h3>
                <div>
                {$this->generateUofAGrantTable()}
                </div>
	    </div>
            <div class='grantAccordion'>
                <h3><a href='#'>NSERC</a></h3>
                <div>
                {$this->generateGrantTable()}
                </div>
            </div>";
        return $this->html;
     }

    function generateGrantTable(){
	if(!$this->visibility['isMe']){
		return "";
	}
        $contributions = $this->person->getContributions();
	   $string = "<table id='contributions_table' frame='box' rules='all'>
			<thead><tr><th style='white-space:nowrap;'>Name</th>
			<th style='white-space:nowrap;'>Partner</th>
			<th style='white-space:nowrap;'>Start Date</th>
			<th style='white-space:nowrap;'>End Date</th>
			<th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";
	foreach($contributions as $contribution){
	$partners = $contribution->getPartners();
	if($contribution->project_id != ""){
	     continue;
	}
	    $string .= "<tr><td><a href='{$contribution->getURL()}'>{$contribution->getName()}</a></td>
				<td></td>
				<td style='white-space:nowrap;'>".time2date($contribution->getStartDate(), "Y-m-d")."</td>
				<td style='white-space:nowrap;'>".time2date($contribution->getEndDate(), "Y-m-d")."</td>
				 <td align=right>$".number_format($contribution->getTotal())."</td></tr>";}
    	$string .= "</table></tbody><script type='text/javascript'>
			$('#contributions_table').dataTable();
	</script>";
	return $string;
    }
    function generateUofAGrantTable(){
        if(!$this->visibility['isMe']){
                return "";
        }
        $contributions = $this->person->getContributions();
           $string = "<table id='contributions_table2' frame='box' rules='all'>
                        <thead><tr><th style='white-space:nowrap;'>Name</th>
                        <th style='white-space:nowrap;'>Agency</th>
                        <th style='white-space:nowrap;'>Start Date</th>
                        <th style='white-space:nowrap;'>End Date</th>
                        <th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";
        foreach($contributions as $contribution){
            $partners = $contribution->getPartners();
            $organizations = array();
            foreach($partners as $partner){
                $organizations[] = $partner->getOrganization();
            }
            if($contribution->project_id == ""){
                 continue;
            }
            $string .= "<tr><td><a href='{$contribution->getURL()}'>{$contribution->getName()}</a></td>
                                <td>".implode(",", $organizations)."</td>
                                <td style='white-space:nowrap;'>".time2date($contribution->getStartDate(), "Y-m-d")."</td>
                                <td style='white-space:nowrap;'>".time2date($contribution->getEndDate(), "Y-m-d")."</td>
                                 <td align=right>$".number_format($contribution->getTotal())."</td></tr>";}
        $string .= "</table></tbody><script type='text/javascript'>
                        $('#contributions_table2').dataTable();
        </script>";
        return $string;
    }


}    
?>
