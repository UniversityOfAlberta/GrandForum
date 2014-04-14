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
            $this->showBudgetSummary();
            $this->showResearcherProductivity();
            $this->showContributionsByUniversity();
            $this->showHQPBreakdown();
            if(isset($_GET['downloadOverview'])){
                header("Content-type: application/vnd.ms-word");
                header("Content-Disposition: attachment;Filename={$this->project->getName()}_Overview.doc");
                echo "<html>";
                echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
                echo "<body>";
                echo $this->html;
                echo "</body>";
                echo "</html>";
                exit;
            }
        }
        return $this->html;
    }
    
    function showBudgetSummary(){
        $this->html .= "<h2>Budget Summary</h2>";
        for($y=$this->startYear; $y<=$this->endYear-1; $y++){
            $budget = $this->project->getRequestedBudget($y);
            if($budget->nCols() > 2 && $budget->nRows() > 2){
                $this->html .= "<h3>".($y+1)."</h3>";
                $this->html .= $budget->render();
            }
        }
    }
    
    function showResearcherProductivity(){
        $this->html .= "<h2>Researcher Productivity</h2>";
        for($y=$this->startYear; $y<=$this->endYear; $y++){
            $people = array();
            $tmpPeople = array_merge($this->project->getAllPeopleDuring(PNI, $y."-01-01", $y."-12-31"), 
                                     $this->project->getAllPeopleDuring(CNI, $y."-01-01", $y."-12-31"),
                                     $this->project->getAllPeopleDuring(AR, $y."-01-01", $y."-12-31"));
            foreach($tmpPeople as $person){
                $people[$person->getReversedName()] = $person;
            }
            ksort($people);
            $this->html .= "<h3>".$y."</h3>";
            $this->html .= "<table frame='box' rules='all' cellpadding='1'><tr><th>Researcher</th><th>#HQP</th><th>#Undergraduate</th><th>#Masters</th><th>#PhD</th><th>#PostDoc</th><th>#Technician</th><th>#Other HQP</th><th>#Publications</th><th>#Artifacts</th><th>#Contributions</th><th>Volume of Contributions</th></tr>";
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
                $hqps = $person->getHQPDuring($y."-01-01", $y."-12-31");
                $publications = $person->getPapersAuthored('Publication', $y."-01-01", $y."-12-31");
                $artifacts = $person->getPapersAuthored('Artifact', $y."-01-01", $y."-12-31");
                $contribs = $person->getContributionsDuring($y);
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
                    if($hqp->isMemberOfDuring($this->project, $y."-01-01", $y."-12-31")){
                        $university = $hqp->getUniversityDuring($y."-01-01", $y."-12-31");
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
            //$this->html .= "<tr><td><b>Total</b></td><td align='right'>$totalHQP</td><td align='right'>$totalUndergraduate</td><td align='right'>$totalMasters</td><td align='right'>$totalPhD</td><td align='right'>$totalPostDoc</td><td align='right'>$totalTech</td><td align='right'>$totalOther</td><td align='right'>$totalPubs</td><td align='right'>$totalArts</td><td align='right'>$totalContribs</td><td align='right'>$".number_format($totalVContribs, 2)."</td></tr>";
            $this->html .= "</table>";
        }
    }
    
    function showContributionsByUniversity(){
        $this->html .= "<h2>Contributions by University</h2>";
        for($y=$this->startYear; $y<=$this->endYear; $y++){
            $contribs = $this->project->getContributionsDuring($y);
            $unis = array();
            $this->html .= "<h3>".$y."</h3>";
            $this->html .= "<table frame='box' rules='all' cellpadding='1'><tr><th>University</th><th>Cash</th><th>In-Kind</th><th>Total</th></tr>";
            foreach($contribs as $contrib){
                foreach($contrib->getPeople() as $person){
                    if($person->isMemberOfDuring($this->project, $y."-01-01", $y."-12-31")){
                        $university = $person->getUniversityDuring($y."-01-01", $y."-12-31");
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
    }
    
    function showHQPBreakdown(){
        $this->html .= "<h2>HQP Breakdown by University</h2>";
        for($y=$this->startYear; $y<=$this->endYear; $y++){
            $unis = array();
            $hqps = $this->project->getAllPeopleDuring(HQP, $y."-01-01", $y."-12-31");
            foreach($hqps as $hqp){
                $university = $hqp->getUniversityDuring($y."-01-01", $y."-12-31");
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
            $this->html .= "<h3>".$y."</h3>";
            $this->html .= "<table frame='box' rules='all' cellpadding='1'><tr><th>University</th><th>Ugrad</th><th>Masters</th><th>PhD</th><th>PostDoc</th><th>Tech</th><th>Other</th><th>Total</th></tr>";
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

}    
    
?>
