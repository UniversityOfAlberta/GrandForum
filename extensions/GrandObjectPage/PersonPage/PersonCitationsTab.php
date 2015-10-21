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
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        $wgOut->addScript(
                "<script type='text/javascript'>
                $(document).ready(function(){
                    $('#citationAccordion').accordion({autoHeight: false, collapsible: true});
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


        $metric = $this->person->getMetric();
	$acm_stats = $this->getAcmStats($metric);
        $scopus_stats = $this->getScopusStats($metric);
	$gs_stats = $this->getGsStats();
	$this->html ="
	    <div id='citationAccordion'>
                <h3><a href='#'>ACM Statistics</a></h3>
                <div>
                {$acm_stats}
                </div>
                <h3><a href='#'>Scopus Statistics</a></h3>
                <div>
		{$scopus_stats}
                </div>
		<h3><a href='#'>Google Scholar Statistics</a></h3>
		<div id='gs_stats'>
		{$gs_stats}
		</div>
            </div>";
        return $this->html;
    }


    function getAcmStats($metric){
	$html = "";
	if($metric != "" && $metric->acm_publication_count != 0){
	    $html .= "<ul>";
	    $html .= "<li><strong>Start Date:</strong> ".time2date($metric->acm_start_date)."</li>";  
            $html .= "<li><strong>End Date:</strong> ".time2date($metric->acm_end_date)."</li>";
            $html .= "<li><strong>Publication Count:</strong> {$metric->acm_publication_count}</li>";
            $html .= "<li><strong>Average Citation Per Article:</strong> {$metric->acm_avg_citations_per_article}</li>";
            $html .= "<li><strong>Total Citation Count:</strong> {$metric->acm_citation_count}</li>";
            $html .= "<li><strong>Average Downloads Per article:</strong> {$metric->acm_avg_download_per_article}</li>";
            $html .= "<li><strong>Available Downloads:</strong> {$metric->acm_available_download}</li>";
            $html .= "<li><strong>Cumulative Downloads Count:</strong> {$metric->acm_download_cumulative}</li>";
            $html .= "<li><strong>Downloads in Past 6 weeks:</strong> {$metric->acm_download_6_weeks}</li>";
            $html .= "<li><strong>Downloads in Past Year:</strong> {$metric->acm_download_1_year}</li>";
            $html .= "<i>(These statistics were last updated: ".time2date($metric->change_date).")</i>";
	    $html .= "</ul>";
	}
	else{
	    $html .= "<strong>No ACM Statistics Available</strong>";
	}
	return $html;
    }

    function getScopusStats($metric){
        $html = "";
        if($metric != "" && $metric->sciverse_doc_count != 0){
            $html .= "<ul>";
            $html .= "<li><strong>Publication Count:</strong> {$metric->sciverse_doc_count}</li>";
            $html .= "<li><strong>H-Index:</strong> {$metric->sciverse_hindex}</li>";
            $html .= "<li><strong>Total Citation Count:</strong> {$metric->sciverse_citation_count}</li>";
            $html .= "<li><strong>Cited By Count:</strong> {$metric->sciverse_cited_by_count}</li>";
            $html .= "<li><strong>Coauthor Count:</strong> {$metric->sciverse_coauthor_count}</li>";
            $html .= "<i>(These statistics were last updated: ".time2date($metric->change_date).")</i>";
            $html .= "</ul>";
        }
        else{
            $html .= "<strong>No Scopus Statistics Available</strong>";
        }
        return $html;
    }

    function getGsStats(){
     	$metric = $this->person->getGsMetric();
	$html = "";
	if($metric != ""){
            $array = $metric->getGsCitations();
	    $html .= "<ul>";
            $html .= "<li><strong>H-Index (All Time):</strong> {$metric->hindex}</li>";
            $html .= "<li><strong>H-Index (Last 5 years):</strong> {$metric->hindex_5_years}</li>";
            $html .= "<li><strong>i10-Index (All Time):</strong> {$metric->i10_index}</li>";
            $html .= "<li><strong>i10-Index (Last 5 Years):</strong> {$metric->i10_index_5_years}</li>";
            $html .= "<li><strong>Citation Count (All Time):</strong> {$metric->citation_count}</li>";
            $html .= "<li><strong>Citation Count (Last 5 Years):</strong> {$metric->getRecentCitationCount()}</li>";
	    $html .= "</ul>";
	    $bar = new Bar($array);
	    $html .= $bar->show();
	    $html .= "<i>(These statistics were last updated: ".time2date($metric->change_date).")</i>";

	}
	else{ $html .= "<strong>No Google Scholar Statistics Available</strong>";}
	return $html;
    }
}
?>
