<?php

class PersonMetricsTab extends AbstractTab {

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
        if(!$wgUser->isLoggedIn()){
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
                $.post('index.php?action=api.importMetrics&getGS&getScopus', {id:{$this->person->getId()}});
            </script>"
        );
        $metric = $this->person->getMetrics();
        $this->html = "<div id='citationAccordion'>";
        $this->html .= "
                <h3><a href='#'>Google Scholar Statistics</a></h3>
                <div id='gs_stats'>
                    {$this->getGsStats($metric)}
                </div>";
        if($config->getValue("scopusApi") != ""){
            $this->html .= "
                <h3><a href='#'>Scopus Statistics</a></h3>
                <div id='scopus_stats'>
                    {$this->getScopusStats($metric)}
                </div>";
        }
        $this->html .= "</div>";
        if($metric->change_date != ""){
            $this->html .= "<i>(These statistics were last updated: ".time2date($metric->change_date).")</i>";
        }
        if($this->person->isMe()){
            $this->html .= "<br />
                <br />
                <input type='button' id='updateBibliometrics' value='Update Bibliometrics'></input>
                <p class='small'>These metrics are updated automatically at most once per week, but can be manually updated by clicking the above button.</p>
                <script type='text/javascript'>
                    $(document).ready(function(){
                        $('#updateBibliometrics').click(function(e){
                            e.preventDefault();
                            $.post('index.php?action=api.importMetrics&getGS&getScopus&forceUpdate', {id:{$this->person->getId()}}, function(result){
                                document.location = '{$this->person->getUrl()}?tab=bibliometrics';
                            }).fail(function(){
                                addError('There was an error updating Bibliometrics');
                            });
                        });
                    });
                </script>";
        }
        return $this->html;
    }

    function getScopusStats($metric){
        $html = "";
        if($metric->scopus_document_count != 0){
            $html .= "<ul>";
            $html .= "<li><strong>Publication Count:</strong> {$metric->scopus_document_count}</li>";
            $html .= "<li><strong>H-Index:</strong> {$metric->scopus_h_index}</li>";
            $html .= "<li><strong>Total Citation Count:</strong> {$metric->scopus_citation_count}</li>";
            $html .= "<li><strong>Cited By Count:</strong> {$metric->scopus_cited_by_count}</li>";
            $html .= "<li><strong>Coauthor Count:</strong> {$metric->scopus_coauthor_count}</li>";
            $html .= "</ul>";
        }
        else{
            $html .= "<strong>No Scopus Metrics Available</strong>";
        }
        return $html;
    }

    function getGsStats($metric){
        global $wgServer, $wgScriptPath, $wgTitle;
        $html = "";
        if($metric->gs_citation_count != 0){
            $array = $metric->getGsCitations();
            $html .= "<ul>";
            $html .= "<li><strong>H-Index (All Time):</strong> {$metric->gs_hindex}</li>";
            $html .= "<li><strong>H-Index (Last 5 years):</strong> {$metric->gs_hindex_5_years}</li>";
            $html .= "<li><strong>i10-Index (All Time):</strong> {$metric->gs_i10_index}</li>";
            $html .= "<li><strong>i10-Index (Last 5 Years):</strong> {$metric->gs_i10_index_5_years}</li>";
            $html .= "<li><strong>Citation Count (All Time):</strong> {$metric->gs_citation_count}</li>";
            $html .= "<li><strong>Citation Count (Last 5 Years):</strong> {$metric->getRecentCitationCount()}</li>";
            $html .= "</ul>";
            $bar = new Bar($array);
            $html .= $bar->show();
        }
        else{
            $html .= "<strong>No Google Scholar Metrics Available</strong>";
        }
        return $html;
    }
}
?>
