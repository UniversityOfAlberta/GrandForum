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
                    $('#grantAccordion').accordion({autoHeight: false, collapsible: true});
                    $('.ui-accordion .ui-accordion-header a.accordion_hdr_lnk').click(function() {
                      window.location = $(this).attr('href');
                      return false;
                   });
                });


                </script>"
            );
            $wgOut->addHTML(
                "<style type='text/css'>
                    .ui-accordion .ui-accordion-header a{
                        display: inline !important;
                    }
                    .ui-accordion .ui-accordion-header a.accordion_hdr_lnk{
                        color: blue !important;
                        padding-left: 0 !important;
                    }
                    .ui-accordion .ui-accordion-header a.accordion_hdr_lnk:hover{
                        text-decoration: underline;
                    }
                </style>"
            );

	$this->html ="
	    <div id='grantAccordion'>
                <h3><a href='#'>UoA Grants 3.0</a></h3>
                <div>
                {$this->generateUofAGrantTable()}
                </div>
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
				<td>{$partners[0]->getOrganization()}</td>
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
                        <th style='white-space:nowrap;'>Sponsor</th>
                        <th style='white-space:nowrap;'>Start Date</th>
                        <th style='white-space:nowrap;'>End Date</th>
                        <th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";
        foreach($contributions as $contribution){
        $partners = $contribution->getPartners();
        if($contribution->project_id == ""){
             continue;
        }
            $string .= "<tr><td><a href='{$contribution->getURL()}'>{$contribution->getName()}</a></td>
                                <td>{$partners[0]->getOrganization()}</td>
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
