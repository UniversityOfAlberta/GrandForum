<?php

class DummyReport extends AbstractReport{
    
    function __construct($reportType, $person, $project=null, $year=REPORTING_YEAR, $quick=false){
        global $config;
        $this->readOnly = true;
        $projectName = null;
        if($project != null){
            $projectName = $project->getName();
        }
        $topProjectOnly = false;
        parent::__construct(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$reportType.xml", $person->getId(), $projectName, $topProjectOnly, $year, $quick);
    }
    
}

?>
