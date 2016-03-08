<?php

class DummyReport extends AbstractReport{
    
    function DummyReport($reportType, $person, $project=null, $year=REPORTING_YEAR, $quick=false){
        global $config;
        if(is_numeric($reportType) || ReportXMLParser::findReport($reportType) != ""){
            $fileName = ReportXMLParser::findReport($reportType);
        }
        else{
            $fileName = dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$reportType.xml";
        }
  
        $this->readOnly = true;
        $projectName = null;
        if($project != null){
            $projectName = $project->getName();
        }
        $topProjectOnly = false;
        /*if($projectName != null){
            $topProjectOnly = true;
        }*/
        $this->AbstractReport($fileName, $person->getId(), $projectName, $topProjectOnly, $year, $quick);
    }
    
}

?>
