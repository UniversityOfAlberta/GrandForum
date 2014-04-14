<?php

class ProjectOverviewTab extends AbstractTab {

    var $project;
    var $visibility;
    var $startYear;
    var $endYear;

    function ProjectOverviewTab($project, $visibility){
        parent::AbstractTab("Overview");
        $this->project = $project;
        $this->startYear = substr($project->getCreated(), 0, 4);
        if($this->project->getDeleted() != "0000-00-00 00:00:00"){
            $this->endYear = substr($project->getDeleted(), 0, 4);
        }
        else{
            $this->endYear = date('Y');
        }
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        if($this->visibility['isLead']){
            for($y=$this->startYear; $y<=$this->endYear; $y++){
                $this->html .= "<h2>{$y}</h2>";
                $this->showExecutiveSummary($y);
                $this->showBudgetSummary($y);
                $this->showResearcherProductivity($y);
                $this->showContributionsByUniversity($y);
                $this->showHQPBreakdown($y);
            }
            if(isset($_GET['downloadOverview'])){
                if(!isset($_GET['preview'])){
                    header("Content-type: application/vnd.ms-word");
                    header("Content-Disposition: attachment;Filename={$this->project->getName()}_Overview.doc");
                }
                echo "<html>";
                echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
                echo "<body style='font-size:10px;'>";
                echo "<center><h1>{$this->project->getFullName()} Overview</h1></center>";
                echo $this->html;
                echo "</body>";
                echo "</html>";
                exit;
            }
        }
        return $this->html;
    }
    
    function showExecutiveSummary($year){
        $addr = ReportBlob::create_address(RP_LEADER, LDR_RESACTIVITY, LDR_RESACT_OVERALL, 0);
        $blob = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
        $res = $blob->load($addr);
        $data = nl2br($blob->getData());
        if($res){
            $this->html .= "<h3>Executive Summary</h3>";
            $this->html .= "$data";
        }
    }
    
    function showBudgetSummary($year){
        $budget = $this->project->getRequestedBudget($year);
        if($budget->nCols() > 2 && $budget->nRows() > 2){
            $this->html .= "<h3>Budget Summary</h3>";
            $this->html .= $budget->render();
        }
    }
    
    function showResearcherProductivity($year){
        $this->html .= "<h3>Researcher Productivity</h3>";
        $people = array();
        $tmpPeople = array_merge($this->project->getAllPeopleDuring(PNI, $year."-01-01", $year."-12-31"), 
                                 $this->project->getAllPeopleDuring(CNI, $year."-01-01", $year."-12-31"),
                                 $this->project->getAllPeopleDuring(AR, $year."-01-01", $year."-12-31"));
        foreach($tmpPeople as $person){
            $people[$person->getReversedName()] = $person;
        }
        ksort($people);
        $this->html .= "<table frame='box' rules='all' cellpadding='1' style='page-break-inside: avoid;'><tr><th>Researcher</th><th>#HQP</th><th>#Undergraduate</th><th>#Masters</th><th>#PhD</th><th>#PostDoc</th><th>#Technician</th><th>#Other HQP</th><th>#Publications</th><th>#Artifacts</th><th>#Contributions</th><th>Volume of Contributions</th></tr>";
        $totalHQP = 0;
        $totalUndergraduate = 0;
        $totalMasters = 0;
        $totalPhD = 0;
        $totalPostDoc = 0;
        $totalTech = 0;
        $totalOther = 0;
        $totalPubs = 0;
        $totalArts = 0;
        $totalContribs = 0;
        $totalVContribs = 0;
        foreach($people as $person){
            $this->html .= "<tr><td>{$person->getReversedName()}</td>";
            $hqps = $person->getHQPDuring($year."-01-01", $year."-12-31");
            $publications = $person->getPapersAuthored('Publication', $year."-01-01", $year."-12-31");
            $artifacts = $person->getPapersAuthored('Artifact', $year."-01-01", $year."-12-31");
            $contribs = $person->getContributionsDuring($year);
            $nHQP = 0;
            $nUndergraduate = 0;
            $nMasters = 0;
            $nPhD = 0;
            $nPostDoc = 0;
            $nTech = 0;
            $nOther = 0;
            $nPubs = 0;
            $nArts = 0;
            $nContribs = 0;
            $vContribs = 0;
            foreach($hqps as $hqp){
                if($hqp->isMemberOfDuring($this->project, $year."-01-01", $year."-12-31")){
                    $university = $hqp->getUniversityDuring($year."-01-01", $year."-12-31");
                    $nHQP++;
                    $totalHQP++;
                    switch($university['position']){
                        case "Undergraduate":
                            $nUndergraduate++;
                            $totalUndergraduate++;
                            break;
                        case "Masters Student":
                            $nMasters++;
                            $totalMasters++;
                            break;
                        case "PhD Student":
                            $nPhD++;
                            $totalPhD++;
                            break;
                        case "PostDoc":
                            $nPostDoc++;
                            $totalPostDoc++;
                            break;
                        case "Technician":
                            $nTech++;
                            $totalTech++;
                            break;
                        default:
                            $nOther++;
                            $totalOther++;
                            break;
                    }
                }
            }
            foreach($publications as $pub){
                if($pub->belongsToProject($this->project)){
                    $nPubs++;
                    $totalPubs++;
                }
            }
            foreach($artifacts as $art){
                if($art->belongsToProject($this->project)){
                    $nArts++;
                    $totalArts++;
                }
            }
            foreach($contribs as $contrib){
                if($contrib->belongsToProject($this->project)){
                    $nContribs++;
                    $totalContribs++;
                    $vContribs += $contrib->getTotal();
                    $totalVContribs += $contrib->getTotal();
                }
            }
            $this->html .= "<td align='right'>$nHQP</td><td align='right'>$nUndergraduate</td><td align='right'>$nMasters</td><td align='right'>$nPhD</td><td align='right'>$nPostDoc</td><td align='right'>$nTech</td><td align='right'>$nOther</td><td align='right'>$nPubs</td><td align='right'>$nArts</td><td align='right'>$nContribs</td><td align='right'>$".number_format($vContribs, 2)."</td></tr>";
        //$this->html .= "<tr><td><b>Total</b></td><td align='right'>$totalHQP</td><td align='right'>$totalUndergraduate</td><td align='right'>$totalMasters</td><td align='right'>$totalPhD</td><td align='right'>$totalPostDoc</td><td align='right'>$totalTech</td><td align='right'>$totalOther</td><td align='right'>$totalPubs</td><td align='right'>$totalArts</td><td align='right'>$totalContribs</td><td align='right'>$".number_format($totalVContribs, 2)."</td></tr>";
        }
        $this->html .= "</table>";
    }
    
    function showContributionsByUniversity($year){
        $this->html .= "<h3'>Contributions by University</h3>";
        $contribs = $this->project->getContributionsDuring($year);
        $unis = array();
        $this->html .= "<table frame='box' rules='all' cellpadding='1' style='page-break-inside: avoid;'><tr><th>University</th><th>Cash</th><th>In-Kind</th><th>Total</th></tr>";
        foreach($contribs as $contrib){
            foreach($contrib->getPeople() as $person){
                if($person->isMemberOfDuring($this->project, $year."-01-01", $year."-12-31")){
                    $university = $person->getUniversityDuring($year."-01-01", $year."-12-31");
                    $uni = $university['university'];
                    @$unis[$uni]['cash'] += $contrib->getCash();
                    @$unis[$uni]['kind'] += $contrib->getKind();
                    @$unis[$uni]['total'] += $contrib->getTotal();
                }
            }
        }
        ksort($unis);
        foreach($unis as $name => $row){
            $this->html .= "<tr><td>$name</td><td align='right'>$".number_format($row['cash'], 2)."</td><td align='right'>$".number_format($row['kind'], 2)."</td><td align='right'>$".number_format($row['total'], 2)."</td></tr>";
        }
        $this->html .= "</table>";
    }
    
    function showHQPBreakdown($year){
        $this->html .= "<h3>HQP Breakdown by University</h3>";
        $unis = array();
        $hqps = $this->project->getAllPeopleDuring(HQP, $year."-01-01", $year."-12-31");
        foreach($hqps as $hqp){
            $university = $hqp->getUniversityDuring($year."-01-01", $year."-12-31");
            $uni = $university['university'];
            $pos = $university['position'];
            if(!isset($unis[$uni])){
                $unis[$uni] = array('Ugrad' => 0,
                                    'Masters' => 0,
                                    'PhD' => 0,
                                    'PostDoc' => 0,
                                    'Tech' => 0,
                                    'Other' => 0,
                                    'Total' => 0);
            }
            switch($pos){
                case 'Undergraduate':
                    $unis[$uni]['Ugrad']++;
                    break;
                case 'Masters Student':
                    $unis[$uni]['Masters']++;
                    break;
                case 'PhD Student':
                    $unis[$uni]['PhD']++;
                    break;
                case 'PostDoc':
                    $unis[$uni]['PostDoc']++;
                    break;
                case 'Technician':
                    $unis[$uni]['Tech']++;
                    break;
                default:
                    $unis[$uni]['Other']++;
                    break;
            }
            @$unis[$uni]['Total']++;
        }
        $this->html .= "<table frame='box' rules='all' cellpadding='1' style='page-break-inside: avoid;'><tr><th>University</th><th>Ugrad</th><th>Masters</th><th>PhD</th><th>PostDoc</th><th>Tech</th><th>Other</th><th>Total</th></tr>";
        ksort($unis);
        foreach($unis as $uni => $row){
            $this->html .= "<tr>
                                <td>$uni</td>
                                <td align='right'>{$row['Ugrad']}</td>
                                <td align='right'>{$row['Masters']}</td>
                                <td align='right'>{$row['PhD']}</td>
                                <td align='right'>{$row['PostDoc']}</td>
                                <td align='right'>{$row['Tech']}</td>
                                <td align='right'>{$row['Other']}</td>
                                <td align='right'>{$row['Total']}</td>
                            </tr>";
        }
        $this->html .= "</table>";
    }
}    
    
?>
