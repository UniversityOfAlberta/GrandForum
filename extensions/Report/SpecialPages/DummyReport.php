<?php

class DummyReport extends AbstractReport{
    
    function DummyReport($reportType, $person, $project=null, $year=REPORTING_YEAR){
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
                case RP_CHAMP:
                    $reportType = "ChampionReport";
                    break;
                case RP_PROJECT_ISAC:
                    $reportType = "ProjectISACCommentsPDF";
                    break;
                case RP_PROJECT_CHAMP:
                    $reportType = "ProjectChampionsReportPDF";
                    break;
                case RP_REVIEW:
                    $reportType = "ReviewReport";
                    break;
                case RP_EVAL_RESEARCHER:
                    $reportType = "EvalResearcherReportOld";
                    break;
                case RP_EVAL_PROJECT:
                    $reportType = "EvalProjectReportOld";
                    break;
                case RP_SUPPLEMENTAL:
                    $reportType = "NIReport";
                    break;
                case RP_EVAL_PDF:
                    $reportType = "EvalNIPDFReport";
                    break;
                case RP_EVAL_CNI:
                    break;
                case RP_MTG:
                    $reportType = "MindTheGap";
                    break;
            }
        }
    
        $this->readOnly = true;
        $projectName = null;
        if($project != null){
            $projectName = $project->getName();
        }
        $topProjectOnly = false;
        if($projectName != null && ($reportType == "NIReport" || $reportType == "HQPReport" || $reportType == "NIReportPDF" || $reportType == "HQPReportPDF")){
            $topProjectOnly = true;
        }
        $this->AbstractReport(dirname(__FILE__)."/../ReportXML/$reportType.xml", $person->getId(), $projectName, $topProjectOnly, $year);
    }
    
}

?>
