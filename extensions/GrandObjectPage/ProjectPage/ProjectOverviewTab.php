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
        if($this->visibility['isLead'] && isExtensionEnabled("Reporting")){
            for($y=$this->startYear; $y<=$this->endYear; $y++){
                if($y == $this->startYear){
                    $this->html .= "<h2>{$y}</h2>";
                }
                else{
                    $this->html .= "<h2 style='page-break-before:always;'>$y</h2>";
                }
                $this->showExecutiveSummary($y);
                $this->showBudgetSummary($y-1, $y-1);
                $this->showResearcherProductivity($y, $y);
                $this->showContributionsByUniversity($y, $y);
                $this->showHQPBreakdown($y, $y);
            }
            if($this->project->isDeleted()){
                $createdYear = intval(substr($this->project->getCreated(),0,4));
                $deletedYear = intval(substr($this->project->getDeleted(),0,4));
                if($createdYear < $deletedYear){
                    $this->html .= "<h2 style='page-break-before:always;'>$createdYear-$deletedYear</h2>";
                    $this->showBudgetSummary($createdYear-1, $deletedYear-2);
                    $this->showResearcherProductivity($createdYear, $deletedYear);
                    $this->showContributionsByUniversity($createdYear, $deletedYear);
                    $this->showHQPBreakdown($createdYear, $deletedYear);
                }
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
    
    function showBudgetSummary($year, $end){
        $fullBudget = new Budget(array(array(HEAD, HEAD, HEAD)), array(array("Categories for April 1, ".($year+1).", to March 31, ".($end+2), NI."s")));
            
        $niTotals = array();
        for($y=$year;$y<=$end;$y++){
            $people = array();
            foreach($this->project->getAllPeopleDuring(NI, $y."-04-01", ($y+1)."-03-31") as $person){
                if(!isset($people[$person->getId()])){
                    $budget = $person->getRequestedBudget($y);
                    if($budget != null){
                        $b = $budget->copy()->rasterize()->select(V_PROJ, array($this->project->getName()))->limit(6, 16);
                        if($b->nCols() > 0 && $b->nRows() > 0){
                            $niTotals[] = $b;
                            $people[$person->getId()] = true;
                        }
                    }
                }
            }
        }
        if(count($niTotals) == 0){
            return;
        }
        @$niTotals = Budget::join_tables($niTotals);
        
        $cubedNI = new Budget();
        if($niTotals != null){
            $cubedNI = @$niTotals->cube();
        }
        
        $categoryBudget = new Budget(array(array(HEAD1),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD1),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD1),
                                           array(HEAD1),
                                           array(HEAD1),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD2)), 
                                     array(array("1) Salaries and stipends"),
                                           array("a) Graduate students"),
                                           array("b) Postdoctoral fellows"),
                                           array("c) Technical and professional assistants"),
                                           array("d) Undergraduate students"),
                                           array("2) Equipment"),
                                           array("a) Purchase or rental"),
                                           array("b) Maintenance costs"),
                                           array("c) Operating costs"),
                                           array("3) Materials and supplies"),
                                           array("4) Computing costs"),
                                           array("5) Travel expenses"),
                                           array("a) Field trips"),
                                           array("b) Conferences"),
                                           array("c) GRAND annual conference")));
                                        
        $categoryBudget = @$categoryBudget->join($cubedNI->select(CUBE_ROW_TOTAL)->filter(CUBE_TOTAL)->filter(HEAD, array("TOTAL")));
                                         
        $fullBudget = $fullBudget->union($categoryBudget);
        $this->html .= "<h3>Budget Summary (Requested)</h3>";
        $this->html .= $fullBudget->cube()->render();
    }
    
    function showResearcherProductivity($year, $end){
        $this->html .= "<h3>Researcher Productivity</h3>";
        $people = array();
        $tmpPeople = $this->project->getAllPeopleDuring(NI, $year."-01-01", $end."-12-31");
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
        if(count($people) > 0){
            foreach($people as $person){
                $this->html .= "<tr><td>{$person->getReversedName()}</td>";
                $hqps = $person->getHQPDuring($year."-01-01", $end."-12-31");
                $publications = $person->getPapersAuthored('Publication', $year."-01-01", $end."-12-31");
                $artifacts = $person->getPapersAuthored('Artifact', $year."-01-01", $end."-12-31");
                $contribs = array();
                for($y=$year;$y<=$end;$y++){
                    $contribs = array_merge($contribs, $person->getContributionsDuring($y));
                }
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
                    if($hqp->isMemberOfDuring($this->project, $year."-01-01", $end."-12-31")){
                        $university = $hqp->getUniversityDuring($year."-01-01", $end."-12-31");
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
            }
            $this->html .= "</table>";
        }
        else{
            $this->html .= "No Researchers";
        }
    }
    
    function showContributionsByUniversity($year, $end){
        $this->html .= "<h3>Contributions by University</h3>";
        $contribs = array();
        for($y=$year;$y<=$end;$y++){
            $contribs = array_merge($contribs, $this->project->getContributionsDuring($y));
        }
        $unis = array();
        foreach($contribs as $contrib){
            $tmpUnis = array();
            foreach($contrib->getPeople() as $person){
                if($person instanceof Person && $person->isMemberOfDuring($this->project, $year."-01-01", $end."-12-31")){
                    $university = $person->getUniversityDuring($year."-01-01", $end."-12-31");
                    $uni = $university['university'];
                    if(!isset($tmpUnis[$uni])){
                        @$unis[$uni]['cash'] += $contrib->getCash();
                        @$unis[$uni]['kind'] += $contrib->getKind();
                        @$unis[$uni]['total'] += $contrib->getTotal();
                        $tmpUnis[$uni] = true;
                    }
                }
            }
        }
        if(count($unis) > 0){
            $this->html .= "<table frame='box' rules='all' cellpadding='1' style='page-break-inside: avoid;'><tr><th>University</th><th>Cash</th><th>In-Kind</th><th>Total</th></tr>";
            ksort($unis);
            foreach($unis as $name => $row){
                $this->html .= "<tr><td>$name</td><td align='right'>$".number_format($row['cash'], 2)."</td><td align='right'>$".number_format($row['kind'], 2)."</td><td align='right'>$".number_format($row['total'], 2)."</td></tr>";
            }
            $this->html .= "</table>";
        }
        else{
            $this->html .= "No Contributions";
        }
    }
    
    function showHQPBreakdown($year, $end){
        $this->html .= "<h3>HQP Breakdown by University</h3>";
        $unis = array();
        $hqps = $this->project->getAllPeopleDuring(HQP, $year."-01-01", $end."-12-31");
        foreach($hqps as $hqp){
            $university = $hqp->getUniversityDuring($year."-01-01", $end."-12-31");
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
        if(count($unis) > 0){
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
        else{
            $this->html .= "No HQP Universities";
        }
    }
}    
    
?>
