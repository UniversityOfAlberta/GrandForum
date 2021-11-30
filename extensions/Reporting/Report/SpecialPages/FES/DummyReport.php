<?php

class DummyReport extends AbstractReport{
    
    function __construct($reportType, $person, $project=null, $year=REPORTING_YEAR){
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
            if($project instanceof Project){
                if($project->getName() == ""){
                    $projectName = $project->getId();
                }
                else{
                    $projectName = $project->getName();
                }
            }
            else if($project instanceof Theme){
                $projectName = $project->getAcronym();
            }
        }
        $topProjectOnly = false;
        /*if($projectName != null){
            $topProjectOnly = true;
        }*/
        $this->AbstractReport($fileName, $person->getId(), $projectName, $topProjectOnly, $year);
    }
    
}

?>
