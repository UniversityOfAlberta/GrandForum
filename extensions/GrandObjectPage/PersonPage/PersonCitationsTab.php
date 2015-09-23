<?php

class PersonCitationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonCitationsTab($person, $visibility){
        parent::AbstractTab("Citations");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        $this->html.= $this->generateMetricTable();
   	$this->html.= $this->generateCitationTable($this->person,$this->visibility); 
    }

    function generateMetricTable(){
	$metric = $this->person->getMetric();
	if($metric == ""){
	    return "<center><table><tr><td><b>No Metrics Available</b></td></tr></table></center><br>";
	}
	if($metric->acm_publication_count == 0){
	            $htmlstring = "<table>
                       <tr><td align='left'><b>ACM Data Start Date:</b></td><td align='right'>--</td></tr>
                       <tr><td align='left'><b>ACM Data End Date:</b></td><td align='right'>--</td></tr>
                       <tr><td align='left'><b>ACM Publication Count:</b></td><td align='right'>--</td></tr>
                       <tr><td align='left'><b>ACM Average Citation Per Article:</b></td><td align='right'>--</td></tr>
                       <tr><td align='left'><b>ACM Total Citation Count:</b></td><td align='right'>--</td></tr>
                       <tr><td align='left'><b>ACM Average Downloads Per Article:</b></td><td align='right'>--</td></tr>
                       <tr><td align='left'><b>ACM Available Downloads:</b></td><td align='right'>--</td></tr>
                       <tr><td align='left'><b>ACM Cumulative Downloads Count:</b></td><td align='right'>--</td></tr>
                       <tr><td align='left'><b>ACM Downloads in Past 6 weeks:</b></td><td align='right'>--</td></tr>
                       <tr><td align='left'><b>ACM Downloads in Past Year:</b></td><td align='right'>--</td></tr>
                       <tr><td align='left'><b>Sciverse Coauthor Count:</b></td><td align='right'>{$metric->sciverse_coauthor_count}</td></tr>
                       <tr><td align='left'><b>Sciverse H-Index:</b></td><td align='right'>{$metric->sciverse_hindex}</td></tr>
                       <tr><td align='left'><b>Sciverse Total Citation Count:</b></td><td align='right'>{$metric->sciverse_citation_count}</td></tr>
                       <tr><td align='left'><b>Sciverse Cited By Count:</b></td><td align='right'>{$metric->sciverse_cited_by_count}</td></tr>
                       <tr><td align='left'><b>Sciverse Publication Count:</b></td><td align='right'>{$metric->sciverse_doc_count}</td></tr>
                       <tr><td align='left'><b>Date last updated:</b></td><td align='right'>".time2date($metric->change_date)."</td></tr>
                        </table>";

	}
	else{
	$htmlstring = "<table>
		       <tr><td align='left'><b>ACM Data Start Date:</b></td><td align='right'>".time2date($metric->acm_start_date)."</td></tr>
                       <tr><td align='left'><b>ACM Data End Date:</b></td><td align='right'>".time2date($metric->acm_end_date)."</td></tr>
                       <tr><td align='left'><b>ACM Publication Count:</b></td><td align='right'>{$metric->acm_publication_count}</td></tr>
                       <tr><td align='left'><b>ACM Average Citation Per Article:</b></td><td align='right'>{$metric->acm_avg_citations_per_article}</td></tr>
                       <tr><td align='left'><b>ACM Total Citation Count:</b></td><td align='right'>{$metric->acm_citation_count}</td></tr>
                       <tr><td align='left'><b>ACM Average Downloads Per Article:</b></td><td align='right'>{$metric->acm_avg_download_per_article}</td></tr>
                       <tr><td align='left'><b>ACM Available Downloads:</b></td><td align='right'>{$metric->acm_available_download}</td></tr>
                       <tr><td align='left'><b>ACM Cumulative Downloads Count:</b></td><td align='right'>{$metric->acm_download_cumulative}</td></tr>
                       <tr><td align='left'><b>ACM Downloads in Past 6 weeks:</b></td><td align='right'>{$metric->acm_download_6_weeks}</td></tr>
                       <tr><td align='left'><b>ACM Downloads in Past Year:</b></td><td align='right'>{$metric->acm_download_1_year}</td></tr>
                       <tr><td align='left'><b>Sciverse Coauthor Count:</b></td><td align='right'>{$metric->sciverse_coauthor_count}</td></tr>
                       <tr><td align='left'><b>Sciverse H-Index:</b></td><td align='right'>{$metric->sciverse_hindex}</td></tr>
                       <tr><td align='left'><b>Sciverse Total Citation Count:</b></td><td align='right'>{$metric->sciverse_citation_count}</td></tr>
                       <tr><td align='left'><b>Sciverse Cited By Count:</b></td><td align='right'>{$metric->sciverse_cited_by_count}</td></tr>
                       <tr><td align='left'><b>Sciverse Publication Count:</b></td><td align='right'>{$metric->sciverse_doc_count}</td></tr>
                       <tr><td align='left'><b>Date last updated:</b></td><td align='right'>".time2date($metric->change_date)."</td></tr>
			</table>";
	}
	return $htmlstring;

    }
    function generateCitationTable($person, $visibility){
	$me = Person::newFromWgUser();
	$products = $person->getPapers("all",false,'both',true,"Public");
	if(count($products)==0){
             $htmlstring = "<table id='citation_table' frame='box' rules='all'>
                        <thead><tr><th style='white-space:nowrap;'>Publication</th>
                        <th style='white-space:nowrap;'>Scopus Citation Count</th>
                        <th style='white-space:nowrap;'>Google Scholar Citation Count</th>
                        <th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";

             $htmlstring .= "</table></tbody><script type='text/javascript'>$('#citation_table').dataTable({
                                                                                                        'order':[[1,'desc']],
                                                                                                        'autoWidth':false
                                                                                                       });</script>";
	     return $htmlstring;
	}
	$string = "";
       	$htmlstring = "<table id='citation_table' frame='box' rules='all'>
                        <thead><tr><th style='white-space:nowrap;'>Publication</th>
                        <th style='white-space:nowrap;'>Scopus Citation Count</th>
                        <th style='white-space:nowrap;'>Google Scholar Citation Count</th>
                        <th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";
	foreach($products as $paper){
	    $htmlstring .="<tr>";
	    $htmlstring .= "<td><a href='{$paper->getUrl()}'>{$paper->getTitle()}</a><span style='display:none'>{$paper->getDescription()}</span></td>";
	    $htmlstring .= "<td>{$paper->getCitationCount("Sciverse Scopus")}</td>";
	    $htmlstring .= "<td>{$paper->getCitationCount("Google Scholar")}</td>";
	    $htmlstring .= "<td>{$paper->getTotalCitationCount()}</td>";
	    $htmlstring .= "</tr>";
	}
	$htmlstring .= "</table></tbody><script type='text/javascript'>$('#citation_table').dataTable({
													'order':[[1,'desc']],
													'autoWidth':false
												       });</script>";
	
	
	return $htmlstring;

    }
}
?>


