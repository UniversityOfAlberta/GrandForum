<?php

class JungAPI extends API{

    var $personDisciplines = array();
    var $personUniversities = array();
    var $year = REPORTING_YEAR;
    var $startDate = REPORTING_END;
    var $endDate = REPORTING_END;
    var $type;
    var $nodeType;
    var $output = "json";

    function JungAPI(){
        $this->addGET("year", true, "", "2012");
        $this->addGET("type", false, "", "Physical");
        $this->addGET("nodeType", false, "", "Person");
        $this->addGET("output", false, "", "json"); 
    }

    function processParams($params){
        $_GET['year'] = mysql_real_escape_string($_GET['year']);
    }

    function doAction(){
        header("Content-type: application/json");
        $this->outputJSON();
        exit;
    }
    
    function outputJSON(){
        ini_set("memory_limit", "512M");
        $json = array();

        $this->year = $_GET['year'];
        $this->type = isset($_GET['type']) ? $_GET['type'] : "Physical";
        $this->nodeType = isset($_GET['nodeType']) ? $_GET['nodeType'] : "Person";
        $this->output = isset($_GET['output']) ? $_GET['output'] : "json";
        $this->startDate = $_GET['year'].REPORTING_CYCLE_END_MONTH;
        $this->endDate = $_GET['year'].REPORTING_CYCLE_END_MONTH_ACTUAL;
        
        $nodes = array();
        $edges = array();
        $metas = array();
        $projects = array();
            
        $pnis = Person::getAllPeopleDuring(PNI, $this->startDate, $this->endDate);
        $cnis = Person::getAllPeopleDuring(CNI, $this->startDate, $this->endDate);
        $hqps = Person::getAllPeopleDuring(HQP, $this->startDate, $this->endDate);
        $tmpNodes = array_merge($pnis, $cnis, $hqps);
        $nodes = $pnis;
        foreach($tmpNodes as $p){
            $found = false;
            foreach($nodes as $node){
                if($node->getId() == $p->getId()) $found = true;
            }
            if(!$found) $nodes[] = $p;
        }

        foreach($nodes as $key => $node){
            $projects = $node->getProjectsDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
            if(count($projects) == 0){
                unset($nodes[$key]);
            }
        }
        switch($this->type){
            case "Physical":
                $edges = array_merge($this->getContributionEdges($nodes),
                                     $this->getCoProduceEdges($nodes),
                                     $this->getCoSuperviseEdges($nodes));
                break;
            case "Explicit":
                $edges = array_merge($this->getWorksWithEdges($nodes),
                                     $this->getContributionEdges($nodes),
                                     $this->getCoProduceEdges($nodes),
                                     $this->getCoSuperviseEdges($nodes));
                break;
            case "Implicit":
                $edges = array_merge($this->getProjectEdges($nodes),
                                     $this->getUniversityEdges($nodes),
                                     $this->getDisciplineEdges($nodes));
                break;
            case "All":
                $edges = array_merge($this->getWorksWithEdges($nodes),
                                     $this->getCoProduceEdges($nodes),
                                     $this->getCoSuperviseEdges($nodes),
                                     $this->getProjectEdges($nodes),
                                     $this->getUniversityEdges($nodes),
                                     $this->getDisciplineEdges($nodes),
                                     $this->getContributionEdges($nodes));
                break;
        }
        
        $metas = $this->getMetas($nodes, $edges);
        $projects = $this->getProjects();
        
        $json['nodes'] = array();
        if($this->nodeType == "Person"){
            foreach($nodes as $node){
                $json['nodes'][] = array('type' => "Person", 
                                         'name' => $node->getName(),
                                         'meta' => $metas[$node->getName()]);
            }
        }
        else if($this->nodeType == "Project"){
            foreach($projects as $project){
                $json['nodes'][] = array('type' => "Project",
                                         'name' => $project['name'],
                                         'meta' => $project);
            }
        }
        
        $json['edges'] = $edges;
        if($this->output == "json"){
            echo json_encode($json);
        }
        else if($this->output == "csv_nodes"){
            echo "\"Nodes\",\"Id\",\"Discipline\",\"University\",\"Title\",\"Gender\"\n";
            foreach($json['nodes'] as $node){
                $meta = $node['meta'];
                $disc = $meta['Discipline'];
                $uni = ($meta['University'] != "") ? $meta['University'] : "Unknown";
                $title = ($meta['Title'] != "") ? $meta['Title'] : "Unknown";
                $gender = ($meta['Gender'] != "") ? $meta['Gender'] : "Unknown";
                echo "\"{$node['name']}\",\"{$node['name']}\",\"{$disc}\",\"{$uni}\",\"{$title}\",\"{$gender}\"\n";
            }
        }
        else if($this->output == "csv_edges"){
            echo "\"Source\",\"Target\",\"Type\"\n";
            foreach($json['edges'] as $edge){
                echo "{$edge['a']},{$edge['b']},{$edge['direction']}\n";
            }
        }
        exit;
    }
    
    function getProjects(){
        $projData = array();
        $projects = Project::getAllProjectsDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
        
        foreach($projects as $project){
            if($project->getCreated() > $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL){
                continue;
            }
            $people = $project->getAllPeopleDuring(null, $this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
            $products = $project->getPapers('all', "2010".REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
            
            $sumDisc = array();
            foreach($products as $product){
                $isCurrentYear = (strstr($product->getDate(), $this->year) !== false);
                if($isCurrentYear){
                    $pDiscs = array();
                    $authors = $product->getAuthors();
                    foreach($authors as $author){
                        if(!isset($this->personDisciplines[$author->getName()])){
                            $pDisc = $author->getDisciplineDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL, true);
                            $this->personDisciplines[$author->getName()] = $pDisc;
                        }
                        else{
                            $pDisc = $this->personDisciplines[$author->getName()];
                        }
                        $pDiscs[$pDisc] = true;
                    }
                    if(count($pDiscs) != 0){
                        $sumDisc[] = count($pDiscs);
                    }
                }
            }
            
            $discs = array();
            foreach($people as $person){
                if(!isset($this->personDisciplines[$person->getName()])){
                    $disc = $person->getDisciplineDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL, true);
                    $this->personDisciplines[$person->getName()] = $disc;
                }
                else{
                    $disc = $this->personDisciplines[$person->getName()];
                }
                $discs[$disc] = true;
            }
        
            $budgets = array();
            $totalAllocated = 0;
            $allocatedAmount = 0;
            $allocationDelta = 0;
            for($i=2010;$i<=$this->year;$i++){
                $allocated = $project->getAllocatedBudget($i-1);
                $lastAllocatedAmount = $allocatedAmount;
                $allocatedAmount = 0;
                if($allocated != null){
                    $value = $allocated->copy()->rasterize()->where(CUBE_TOTAL)->select(CUBE_TOTAL)->toString();
                    $allocatedAmount = (int)str_replace(',', '', str_replace('$', '', $value));
                    if($allocatedAmount == 0 || $lastAllocatedAmount == 0){
                        $allocationDelta = 0;
                    }
                    else{
                        $allocationDelta = ($allocatedAmount-$lastAllocatedAmount);
                    }
                    $totalAllocated += $allocatedAmount;
                }
            }
            
            $contTotal = 0;
            foreach($project->getContributions() as $contribution){
                if($contribution->getYear() == $this->year){
                    $contTotal += $contribution->getTotal();
                }
            }
            
            $tuple = array();
            
            $tuple['name'] = $project->getName();
            $tuple['nProductsUpToNow'] = (string)count($products);
            $tuple['nDisciplines'] = (string)count($discs);
            $tuple['totalAllocationUpToNow'] = ($totalAllocated == 0) ? "" : (string)$totalAllocated;
            $tuple['allocation'] = ($allocatedAmount == 0) ? "" : (string)$allocatedAmount;
            $tuple['allocationDelta'] = ($allocationDelta == 0) ? "" : (string)$allocationDelta;

            $tuple['contributionsThisYear'] = (string)$contTotal;
            
            if(count($sumDisc) > 0){
                $tuple['avgProductDisciplines'] = number_format((array_sum($sumDisc)/count($sumDisc)), 2, '.', '');
            }
            else{
                $tuple['avgProductDisciplines'] = "";
            }
            $projData[] = $tuple;
        }
        return $projData;
    }
    
    function getMetas($nodes, $edges){
        $metas = array();
        $connectedDisciplines = array();
        
        foreach($edges as $edge){
            if($edge['type'] == "Person"){
                if(!isset($personDisciplines[$edge['b']])){
                    $b = Person::newFromName($edge['b']);
                    $disc = $b->getDisciplineDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL, true);
                    $personDisciplines[$edge['b']] = $disc;
                }
                else{
                    $disc = $personDisciplines[$edge['b']];
                }
                $connectedDisciplines[$edge['a']][$disc] = $disc;
            }
        }
        $allUnis = Person::getAllUniversities();
        $allDisc = array("Computer Science", 
                         "Other Sciences & Engineering", 
                         "Media, Arts & Design",
                         "Other Social Sciences & Humanities",
                         "Information Science");
        $msa = json_decode(file_get_contents("http://grand.cs.ualberta.ca/~dwt/MSResearchCrawler/db.json"));
        $msaAuthors = array();
        $nCitsSum = 0;
        $nPubsSum = 0;
        $nCitsMax = 0;
        $nPubsMax = 0;
        foreach($msa->authors as $name => $author){
            $person = Person::newFromNameLike($name);
            $forum_id = $person->getId();
            $msaAuthors[$forum_id] = $author;
            foreach(@(array)($author->nPubs) as $year => $pub){
                $nPubs[$year] = $pub;
            }
            foreach(@(array)($author->nCits) as $year => $cit){
                $nCits[$year] = $cit;
            }
            if(isset($nPubs[$this->year])){
                $nPubsSum += $nPubs[$this->year];
                if($nPubs[$this->year] > $nPubsMax){
                    $nPubsMax = $nPubs[$this->year];
                }
            }
            if(isset($nCits[$this->year])){
                $nCitsSum += $nCits[$this->year];
                if($nCits[$this->year] > $nCitsMax){
                    $nCitsMax = $nCits[$this->year];
                }
            }
        }
        foreach($nodes as $person){
            $tuple = array();
            $projects = $person->getProjectsDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL, true);
            $projectsAlready = array();
            foreach($projects as $p){
                if($p->getCreated() <= $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL){
                    if(!isset($projectsAlready[$p->getId()])){
                        $projectsAlready[$p->getId()] = true;
                    }
                }
            }
            $currentProducts = array();
            $products = $person->getPapersAuthored('all', "2010".REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL, false);
            $projectsByProduct = array();
            $nProductsWith1University = array();
            $nProductsWith2Universities = array();
            $nProductsWith3Universities = array();
            $nProductsWith4OrMoreUniversities = array();
            
            $sumDisc = array();
            $sumCoAuthors = 0;
            foreach($products as $product){
                $pProjects = $product->getProjects();
                $universities = array();
                foreach($pProjects as $proj){
                    if($proj->getCreated() <= $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL){
                        $projectsByProduct[$proj->getName()] = true;
                    }
                }
                $discs = array();
                $isCurrentYear = (strstr($product->getDate(), $this->year) !== false);
                $authors = $product->getAuthors();
                foreach($authors as $author){
                    if(!isset($this->personUniversities[$author->getName()])){
                        $uni = $author->getUniversityDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL, true);
                        $this->personUniversities[$author->getName()] = $uni;
                    }
                    else{
                        $uni = $this->personUniversities[$author->getName()];
                    }
                    $universities[$uni['university']] = true;
                    if($isCurrentYear){
                        if(!isset($this->personDisciplines[$author->getName()])){
                            $disc = $author->getDisciplineDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL, true);
                            $this->personDisciplines[$author->getName()] = $disc;
                        }
                        else{
                            $disc = $this->personDisciplines[$author->getName()];
                        }
                        $discs[$disc] = true;
                    }
                }
                if($isCurrentYear){
                    $currentProducts[] = $product;
                    $sumDisc[] = count($discs);
                }
                if(count($universities) == 1){
                    $nProductsWith1University[$product->getId()] = true;
                }
                if(count($universities) == 2){
                    $nProductsWith2Universities[$product->getId()] = true;
                }
                if(count($universities) == 3){
                    $nProductsWith3Universities[$product->getId()] = true;
                }
                if(count($universities) >= 4){
                    $nProductsWith4OrMoreUniversities[$product->getId()] = true;
                }
                $sumCoAuthors += count($authors);
            }
            
            $tuple['nProjects'] = (string)count($projectsAlready);
            $tuple['nProjectsByProduct'] = (string)count($projectsByProduct);
            if(count($sumDisc) > 0){
                $tuple['avgProductDisciplines'] = number_format((array_sum($sumDisc)/count($sumDisc)), 2, '.', '');
            }
            else{
                $tuple['avgProductDisciplines'] = "";
            }
            if(count($products) > 0){
                $tuple['avgAuthorsPerProduct'] = number_format($sumCoAuthors/count($products), 2, '.', '');
            }
            else{
                $tuple['avgAuthorsPerProduct'] = "";
            }
            $tuple['nProductsWith1University'] = (string)count($nProductsWith1University);
            $tuple['nProductsWith2Universities'] = (string)count($nProductsWith2Universities);
            $tuple['nProductsWith3Universities'] = (string)count($nProductsWith3Universities);
            $tuple['nProductsWith4OrMoreUniversities'] = (string)count($nProductsWith4OrMoreUniversities);
            
            if($person->isRoleDuring(HQP, $this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL) &&
               !$person->isRoleDuring(PNI, $this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL) &&
               !$person->isRoleDuring(CNI, $this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL)){
                $sups = $person->getSupervisorsDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
                $totalSups = $person->getSupervisors(true);
                $tuple['nCurrentHQP'] = "";
                $tuple['nTotalHQP'] = "";
                $tuple['nCurrentSupervisors'] = (string)count($sups);
                $tuple['nTotalSupervisors'] = (string)count($totalSups);
                $tuple['nCurrentWorksWith'] = "";
                $tuple['totalAllocationUpToNow'] = "";
                $tuple['allocation'] = "";
                $tuple['allocationDelta'] = "";
                $tuple['nextAllocation'] = "";
                $tuple['nextAllocationDelta'] = "";
            }
            else{
                $worksWith = $person->getRelationsDuring(WORKS_WITH, $this->startDate, $this->endDate);
                $hqps = $person->getHQPDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
                $totalHqps = $person->getHQP(true);
                $budgets = array();
                $totalAllocated = 0;
                $allocatedAmount = 0;
                $allocationDelta = 0;
                $nextAllocationAmount = 0;
                for($i=2010;$i<=$this->year;$i++){
                    $allocated = $person->getAllocatedBudget($i-1);
                    $lastAllocatedAmount = $allocatedAmount;
                    $allocatedAmount = 0;
                    if($allocated != null){
                        $value = $allocated->copy()->rasterize()->where(COL_TOTAL)->select(ROW_TOTAL)->toString();
                        $allocatedAmount = (int)str_replace(',', '', str_replace('$', '', $value));
                        if($lastAllocatedAmount == 0 || $allocatedAmount == 0){
                            $allocationDelta = 0;
                        }
                        else{
                            $allocationDelta = ($allocatedAmount-$lastAllocatedAmount)/max(1, $lastAllocatedAmount);
                        }
                        $totalAllocated += $allocatedAmount;
                    }
                }
                $allocated = $person->getAllocatedBudget($this->year);
                if($allocated != null){
                    $value = $allocated->copy()->rasterize()->where(COL_TOTAL)->select(ROW_TOTAL)->toString();
                    $nextAllocationAmount = (int)str_replace(',', '', str_replace('$', '', $value));
                }
                if($nextAllocationAmount == 0 || $allocatedAmount == 0){
                    $nextAllocationDelta = 0;
                }
                else{
                    $nextAllocationDelta = ($nextAllocationAmount-$allocatedAmount)/max(1, $nextAllocationAmount);
                }
                $tuple['nCurrentHQP'] = (string)count($hqps);
                $tuple['nTotalHQP'] = (string)count($totalHqps);
                $tuple['nCurrentSupervisors'] = "";
                $tuple['nTotalSupervisors'] = "";
                $tuple['nCurrentWorksWith'] = (string)count($worksWith);
                
                $tuple['totalAllocationUpToNow'] = ($totalAllocated == 0) ? "" : (string)$totalAllocated;
                $tuple['allocation'] = ($allocatedAmount == 0) ? "" : (string)$allocatedAmount;
                $tuple['allocationDelta'] = ($allocationDelta == 0) ? "" : (string)$allocationDelta;
                $tuple['nextAllocation'] = ($nextAllocationAmount == 0) ? "" : (string)$nextAllocationAmount;
                $tuple['nextAllocationDelta'] = ($nextAllocationDelta == 0) ? "" : (string)$nextAllocationDelta;
            }
            
            if(!isset($this->personDisciplines[$person->getName()])){
                $disc = $person->getDisciplineDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL, true);
                $this->personDisciplines[$person->getName()] = $disc;
            }
            else{
                $disc = $this->personDisciplines[$person->getName()];
            }
            
            if(!isset($this->personUniversities[$person->getName()])){
                $uni = $person->getUniversityDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL, true);
                $this->personUniversities[$person->getName()] = $uni;
            }
            
            $contTotal = 0;
            foreach($person->getContributions() as $contribution){
                if($contribution->getYear() == $this->year){
                    $contTotal += $contribution->getTotal();
                }
            }
            
            $tuple['contributionsThisYear'] = (string)$contTotal;
            $tuple['nProductsUpToNow'] = (string)count($products);
            $tuple['nCurrentProducts'] = (string)count($currentProducts);
            $tuple['nConnectedDisciplines'] = (string)@count($connectedDisciplines[$person->getName()]);
            
            $tuple['Discipline'] = $disc;
            $tuple['University'] = (string)$this->personUniversities[$person->getName()]['university'];
            $tuple['Title'] = (string)$this->personUniversities[$person->getName()]['position'];
            $tuple['Gender'] = (string)$person->getGender();
            $tuple['Nationality'] = (string)$person->getNationality();
            $tuple['yearRegistered'] = (string)substr($person->getRegistration(), 0, 4);
            
            // Extra
            $tuple['Projects'] = array();
            $tuple['WorksWith'] = array();
            $tuple['Produces'] = array();
            // Extra (Accross Universities)
            $tuple['ProjectsDiffUni'] = array();
            $tuple['WorksWithDiffUni'] = array();
            $tuple['ProducesDiffUni'] = array();
            // Extra (Accross Disciplines)
            $tuple['ProjectsDiffDisc'] = array();
            $tuple['WorksWithDiffDisc'] = array();
            $tuple['ProducesDiffDisc'] = array();
            foreach($projects as $project){
                if($project->getCreated() <= $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL){
                    $value = (string)$project->getName();
                    $tuple['Projects'][] = $value;
                    $tuple['ProjectsDiffUni'][] = $value;
                    $tuple['ProjectsDiffDisc'][] = $value;
                }
            }
            $value = (string)$person->getName();
            $tuple['WorksWith'][] = $value;
            $tuple['WorksWithDiffUni'][] = $value;
            $tuple['WorksWithDiffDisc'][] = $value;
            foreach($person->getRelationsDuring(WORKS_WITH, $this->startDate, $this->endDate) as $rel){
                $value = (string)$rel->getUser2()->getName();
                $tuple['WorksWith'][] = $value;
                $tuple['WorksWithDiffUni'][] = $value;
                $tuple['WorksWithDiffDisc'][] = $value;
            }
            foreach($products as $product){
                $value = (string)$product->getId();
                $tuple['Produces'][] = $value;
                $tuple['ProducesDiffUni'][] = $value;
                $tuple['ProducesDiffDisc'][] = $value;
            }
            $tuple['DiffUni'][] = $tuple['University'];
            $tuple['DiffUni'][] = "!".$tuple['University'];
            
            $tuple['DiffDisc'][] = $tuple['Discipline'];
            $tuple['DiffDisc'][] = "!".$tuple['Discipline'];
            
            $tuple['ProjectsDiffUni'][] = $tuple['University'];
            $tuple['WorksWithDiffUni'][] = $tuple['University'];
            $tuple['ProducesDiffUni'][] = $tuple['University'];
            $tuple['ProjectsDiffUni'][] = "!".$tuple['University'];
            $tuple['WorksWithDiffUni'][] = "!".$tuple['University'];
            $tuple['ProducesDiffUni'][] = "!".$tuple['University'];
            
            $tuple['ProjectsDiffDisc'][] = $tuple['Discipline'];
            $tuple['WorksWithDiffDisc'][] = $tuple['Discipline'];
            $tuple['ProducesDiffDisc'][] = $tuple['Discipline'];
            $tuple['ProjectsDiffDisc'][] = "!".$tuple['Discipline'];
            $tuple['WorksWithDiffDisc'][] = "!".$tuple['Discipline'];
            $tuple['ProducesDiffDisc'][] = "!".$tuple['Discipline'];
            
            $tuple['ScopusPubs'] = "";
            $tuple['ScopusCits'] = "";
            $tuple['ScopusPubsDelta'] = "";
            $tuple['ScopusCitsDelta'] = "";
            
            if(isset($msaAuthors[$person->getId()])){
                $nPubs = array();
                $nCits = array();
                foreach(@(array)($msaAuthors[$person->getId()]->nPubs) as $year => $pub){
                    $nPubs[$year] = $pub;
                }
                foreach(@(array)($msaAuthors[$person->getId()]->nCits) as $year => $cit){
                    $nCits[$year] = $cit;
                }
                if(isset($nPubs[$this->year]) && isset($nCits[$this->year])){
                    $tuple['ScopusPubs'] = @(string)$nPubs[$this->year];
                    $tuple['ScopusCits'] = @(string)$nCits[$this->year];

                    if(@$nPubs[$this->year] != 0 && @$nPubs[$this->year-1] != 0){
                        $tuple['ScopusPubsDelta'] = @(string)($nPubs[$this->year]-$nPubs[$this->year-1]);
                    }
                    if(@$nCits[$this->year] != 0 && @$nCits[$this->year-1] != 0){
                        $tuple['ScopusCitsDelta'] = @(string)($nCits[$this->year]-$nCits[$this->year-1]);
                    }
                }
            }
            $metas[$person->getName()] = $tuple;
        }
        return $metas;
    }
    
    function getCoProduceEdges($nodes){
        $edges = array();
        $ids = array();
        foreach($nodes as $node){
            $ids[$node->getId()] = true;
        }
        foreach($nodes as $person){
            $products = $person->getPapersAuthored('all', $this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL, true);
            foreach($products as $product){
                $authors = $product->getAuthors();
                foreach($authors as $auth){
                    if(isset($ids[$auth->getId()]) && $person->getId() < $auth->getId()){
                        $edges[] = array('a' => $person->getName(), 
                                         'b' => $auth->getName(),
                                         'type' => "Person",
                                         'edgeType' => 'CoProduces',
                                         'direction' => "Undirected");
                    }
                }
            }
        }
        return $edges;
    }
    
    function getCoSuperviseEdges($nodes){
        $edges = array();
        $ids = array();
        foreach($nodes as $node){
            $ids[$node->getId()] = true;
        }
        foreach($nodes as $person){
            $hqps = $person->getHQPDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
            foreach($hqps as $hqp){
                $sups = $hqp->getSupervisorsDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
                foreach($sups as $sup){
                    if(isset($ids[$sup->getId()]) && $person->getId() < $sup->getId()){
                        $edges[] = array('a' => $person->getName(), 
                                         'b' => $sup->getName(),
                                         'type' => "Person",
                                         'edgeType' => "CoSupervises",
                                         'direction' => "Undirected");
                    }
                }
            }
        }
        return $edges;
    }
    
    function getWorksWithEdges($nodes){
        $edges = array();
        $ids = array();
        foreach($nodes as $node){
            $ids[$node->getId()] = true;
        }
        foreach($nodes as $person){
            $relations = $person->getRelationsDuring(WORKS_WITH, $this->startDate, $this->endDate);
            $alreadyDone = array();
            foreach($relations as $relation){
                if(isset($ids[$relation->getUser2()->getId()]) && 
                   !isset($alreadyDone[$relation->getUser2()->getId()]) &&
                   $person->getId() < $relation->getUser2()->getId()){
                    $edges[] = array('a' => $relation->getUser1()->getName(), 
                                     'b' => $relation->getUser2()->getName(),
                                     'type' => "Person",
                                     'edgeType' => "WorksWith",
                                     'direction' => "Undirected");
                    $alreadyDone[$relation->getUser2()->getId()] = true;
                }
            }
        }
        return $edges;
    }
    
    function getProjectEdges($nodes){
        $edges = array();
        $projs = array();
        foreach($nodes as $node){
            $projects = $node->getProjectsDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
            foreach($projects as $project){
                $projs[$project->getName()][] = $node;
            }
        }
        foreach($nodes as $node){
            $projects = $node->getProjectsDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
            foreach($projects as $project){
                if($project->getCreated() <= $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL){
                    foreach($projs[$project->getName()] as $node2){
                        if($node->getId() < $node2->getId()){
                            $edges[] = array('a' => $node->getName(), 
                                             'b' => $node2->getName(),
                                             'type' => "Person",
                                             'edgeType' => "SameProject",
                                             'direction' => "Undirected");
                        }
                    }
                }
            }
        }
        return $edges;
    }
    
    function getUniversityEdges($nodes){
        $edges = array();
        $unis = array();
        foreach($nodes as $node){
            if(!isset($this->personUniversities[$node->getName()])){
                $this->personUniversities[$node->getName()] = $node->getUniversityDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
            }
            $uni = $this->personUniversities[$node->getName()];
            $unis[$uni['university']][] = $node;
        }
        foreach($nodes as $node){
            $uni = $this->personUniversities[$node->getName()];
            foreach($unis[$uni['university']] as $node2){
                if($node->getId() < $node2->getId()){
                    $edges[] = array('a' => $node->getName(), 
                                     'b' => $node2->getName(),
                                     'type' => "Person",
                                     'edgeType' => "SameUniversity",
                                     'direction' => "Undirected");
                }
            }
        }
        return $edges;
    }
    
    function getContributionEdges($nodes){
        $edges = array();
        $ids = array();
        foreach($nodes as $node){
            $ids[$node->getId()] = true;
        }
        foreach($nodes as $node){
            $contribs = $node->getContributions();
            foreach($contribs as $contrib){
                if($contrib->getYear() == $this->year){
                    $people = $contrib->getPeople();
                    foreach($people as $person){
                        if($person instanceof Person && 
                           isset($ids[$person->getId()]) &&
                           $node->getId() < $person->getId()){
                            $edges[] = array('a' => $node->getName(),
                                             'b' => $person->getName(),
                                             'type' => "Person",
                                             'edgeType' => "CoFunded",
                                             'direction' => "Undirected");
                        }
                    }
                }
            }
        }
        return $edges;
    }
    
    function getDisciplineEdges($nodes){
        $depts = array();
        foreach($nodes as $node){
            $disc = $node->getDisciplineDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
            $depts[$disc][] = $node;
        }
        $edges = array();
        foreach($nodes as $node){
            $disc = $node->getDisciplineDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH_ACTUAL);
            foreach($depts[$disc] as $node2){
                if($node->getId() < $node2->getId()){
                    $edges[] = array('a' => $node->getName(), 
                                     'b' => $node2->getName(),
                                     'type' => "Person",
                                     'edgeType' => "SameDiscipline",
                                     'direction' => "Undirected");
                }
            }
        }
        return $edges;
    }
    
    function isLoginRequired(){
        return false;
    }
}

?>
