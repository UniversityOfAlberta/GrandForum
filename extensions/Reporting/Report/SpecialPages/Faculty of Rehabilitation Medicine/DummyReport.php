<?php

class DummyReport extends AbstractReport{
    
    function __construct($reportType, $person, $project=null, $year=YEAR, $quick=false){
        global $config;
        $this->readOnly = true;
        $topProjectOnly = false;
        parent::__construct(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$reportType.xml", $person->getId(), null, $topProjectOnly, $year, $quick);
    }
    
}

?>
