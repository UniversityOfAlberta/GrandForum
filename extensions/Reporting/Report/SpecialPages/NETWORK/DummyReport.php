<?php

class DummyReport extends AbstractReport{
    
    function DummyReport($reportType, $person, $project=null, $year=REPORTING_YEAR){
        global $config;
        if(is_numeric($reportType)){
            switch($reportType){
                case RP_RESEARCHER:
                    $reportType = "NIReport";
                    break;
                case RP_HQP:
                    $reportType = "HQPReport";
                    break;
                case RP_LEADER:
                    $reportType = "ProjectReport";
                    break;
                case RP_PROJECT_PROPOSAL:
                    $reportType = "ProjectProposal";
                    break;
                case RP_SAB_REVIEW:
                    $reportType = "SABReview";
                    break;
                case RP_SAB_REPORT:
                    $reportType = "SABReport";
                    break;
                case RP_EVAL_RESEARCHER:
                    $reportType = "RMCNIReview";
                    break;
                case RP_EVAL_PROJECT:
                    $reportType = "RMCProjectReview";
                    break;
            }
        }
    
        $this->readOnly = true;
        $projectName = null;
        if($project != null){
            $projectName = $project->getName();
        }
        $topProjectOnly = false;
        if($projectName != null && ($reportType == "NIReport" || 
                                    $reportType == "HQPReport" || 
                                    $reportType == "NIReportPDF" || 
                                    $reportType == "HQPReportPDF" || 
                                    $reportType == "SABReport" ||
                                    $reportType == "SABReportPDF")){
            $topProjectOnly = true;
        }
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$reportType.xml", $person->getId(), $projectName, $topProjectOnly, $year);
    }
    
}

?>
