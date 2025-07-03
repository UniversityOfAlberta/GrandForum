<?php

class ProjectBDTab extends AbstractTab {

    var $project;
    var $rp;
    var $title;

    function __construct($project){
        parent::__construct("Business Development");
        $this->project = $project;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $contents = file_get_contents(getcwd()."/extensions/Reporting/Report/ReportXML/{$config->getValue('networkName')}/BusinessDevelopment.xml");
        $xml = simplexml_load_string($contents);
        $p1 = $xml->xpath("//If[@id='p1']/@if");
        $p2 = $xml->xpath("//If[@id='p2']/@if");
        $p3 = $xml->xpath("//If[@id='p3']/@if");
        $p4 = $xml->xpath("//If[@id='p4']/@if");
        
        $p1 = explode("(", $p1[0]);
        $p2 = explode("(", $p2[0]);
        $p3 = explode("(", $p3[0]);
        $p4 = explode("(", $p4[0]);
        
        $p1 = explode(",", $p1[1]);
        $p2 = explode(",", $p2[1]);
        $p3 = explode(",", $p3[1]);
        $p4 = explode(",", $p4[1]);
        
        $p1 = explode("  ", trim(str_replace("'", "", preg_replace('/\s{2,}/', '  ', $p1[0]))));
        $p2 = explode("  ", trim(str_replace("'", "", preg_replace('/\s{2,}/', '  ', $p2[0]))));
        $p3 = explode("  ", trim(str_replace("'", "", preg_replace('/\s{2,}/', '  ', $p3[0]))));
        $p4 = explode("  ", trim(str_replace("'", "", preg_replace('/\s{2,}/', '  ', $p4[0]))));
        
        $table_header = "";
        $cells = array();
        if(in_array($this->project->getName(), $p1)){
            $table_header = $xml->xpath("//Static[@id='p1_table']");
            $cells = $xml->xpath("//If[@id='p1']/ReportItem/@blobItem | //If[@id='p1']/For/@array");
            $table_header = $table_header[0];
        }
        else if(in_array($this->project->getName(), $p2)){
            $table_header = $xml->xpath("//Static[@id='p2_table']");
            $cells = $xml->xpath("//If[@id='p2']/ReportItem/@blobItem| //If[@id='p2']/For/@array");
            $table_header = $table_header[0];
        }
        else if(in_array($this->project->getName(), $p3)){
            $table_header = $xml->xpath("//Static[@id='p3_table']");
            $cells = $xml->xpath("//If[@id='p3']/ReportItem/@blobItem| //If[@id='p3']/For/@array");
            $table_header = $table_header[0];
        }
        else if(in_array($this->project->getName(), $p4)){
            $table_header = $xml->xpath("//Static[@id='p4_table']");
            $cells = $xml->xpath("//If[@id='p4']/ReportItem/@blobItem| //If[@id='p4']/For/@array");
            $table_header = $table_header[0];
        }
        if("{$table_header}" == ""){
            return;
        }
        $this->html .= "{$table_header}";
        $leaders = array();
        foreach($this->project->getLeaders() as $leader){
            $leaders[] = "<a href='{$leader->getUrl()}'>{$leader->getNameForForms()}</a>";
        }
        $this->html .= "<tr>";
        $this->html .= "<td>{$this->project->getName()}</td>";
        $this->html .= "<td>".implode(", ", $leaders)."</td>";
        foreach($cells as $cell){
            if(strstr($cell, "|")){
                // Array
                foreach(explode("|", $cell) as $progress){
                    $this->html .= "<td align='center' class='progress'>".nl2br($this->getBlobValue($progress))."</td>";
                }
            }
            else{
                $this->html .= "<td>".nl2br($this->getBlobValue($cell))."</td>";
            }
        }
        $this->html .= "</tr>";
        $this->html .= "</tbody></table>";
        $this->html .= "<script type='text/javascript'>
            $('.progress').each(function(){
                var val = $(this).text();
                if(val == 'In Progress'){
                    $(this).closest('td').css('background-color', '#3399ff');
                }
                else if(val == 'Completed'){
                    $(this).closest('td').css('background-color', '#55bb55');
                }
                else {
                    $(this).closest('td').css('background-color', '');
                }
            });
        </script>";
    }
    
    function getBlobValue($blobItem){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = 0;
        $projectId = $this->project->getId();
        
        $blb = new ReportBlob(BLOB_RAW, $year, $personId, $projectId);
        $addr = ReportBlob::create_address("RP_BD_REPORT", "BD", $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }

}    
    
?>
