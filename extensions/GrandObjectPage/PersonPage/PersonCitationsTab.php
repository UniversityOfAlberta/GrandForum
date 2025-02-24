<?php

class PersonCitationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Bibliometrics");
        $this->person = $person;
        $this->visibility = $visibility;
        $this->tooltip = "Contains Google Scholar citation information for the faculty member.  A 'Google Scholar URL' on the Bio tab must be provided in order to import citation information.";
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        if(!$wgUser->isRegistered()){
            return "";
        }
        $wgOut->addScript(
                "<script type='text/javascript'>
                    $(document).ready(function(){
                    var bibInterval = setInterval(function(){
                        if($('#citationAccordion').is(':visible')){
                            $('#citationAccordion').accordion();
                            clearInterval(bibInterval);
                        }
                    }, 100);
                });
                </script>"
            );
        $metric = $this->person->getGsMetric();
        //$acm_stats = $this->getAcmStats($metric);
        $this->html = "<div id='citationAccordion'>";
        $this->html .= "
                <h3><a href='#'>Google Scholar Statistics</a></h3>
                <div id='gs_stats'>
                    {$this->getGsStats($metric)}
                </div>";
        $this->html .= "
            <h3><a href='#'>Scopus Statistics</a></h3>
            <div id='scopus_stats'>
                {$this->getScopusStats($metric)}
            </div>";
        $this->html .= "</div>";
        if($metric != "" && $metric->change_date != ""){
            $this->html .= "<i>(These statistics were last updated: ".time2date($metric->change_date).")</i>";
        }
        $_POST['id'] = $this->person->getId();
        if($this->person->isMe()){
            $this->html .= "<br /><br /><input type='button' id='GsUpdate' value='Update Bibliometrics'></input>
                <script>
                    $(document).ready(function(){ 
                    $('#GsUpdate').click(function(e){
                    e.preventDefault();
                    $.ajax({type:'POST',
                            url: wgServer+wgScriptPath+'/index.php?action=api.updateGoogleScholarCitations',
                            data: {id:".$this->person->getId()."},
                            success:function(result){
                                document.location = '{$this->person->getUrl()}?tab=bibliometrics';
                            }});
                        });
                    });
                </script>";
        }
        /*$this->html ="
            <div class='citationAccordion'>
                <h3><a href='#'>ACM Statistics</a></h3>
                <div>
                {$acm_stats}
                </div>
        </div>
        <div class='citationAccordion'>
                <h3><a href='#'>Scopus Statistics</a></h3>
                <div>
                    {$scopus_stats}
                </div>
            </div>
            <div class='citationAccordion'>
            <h3><a href='#'>Google Scholar Statistics</a></h3>
            <div id='gs_stats'>
                {$gs_stats}
            </div>
        </div>";*/
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
        if($metric != "" && $metric->scopus_document_count != 0){
            $html .= "<ul>";
            $html .= "<li><strong>Publication Count:</strong> {$metric->scopus_document_count}</li>";
            $html .= "<li><strong>H-Index:</strong> {$metric->scopus_h_index}</li>";
            $html .= "<li><strong>Total Citation Count:</strong> {$metric->scopus_citation_count}</li>";
            $html .= "<li><strong>Cited By Count:</strong> {$metric->scopus_cited_by_count}</li>";
            $html .= "<li><strong>Coauthor Count:</strong> {$metric->scopus_coauthor_count}</li>";
            $html .= "</ul>";
        }
        else{
            $html .= "<strong>No Scopus Statistics Available</strong>";
        }
        return $html;
    }

    function getGsStats($metric){
        global $wgServer, $wgScriptPath, $wgTitle;
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
        }
        else{
            $html .= "<strong>No Google Scholar Statistics Available</strong>";
        }
        return $html;
    }
}
?>
