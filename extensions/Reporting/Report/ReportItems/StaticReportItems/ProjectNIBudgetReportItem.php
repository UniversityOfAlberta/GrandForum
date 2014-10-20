<?php

class ProjectNIBudgetReportItem extends StaticReportItem {

    private function getBudget(){
        $project = Project::newFromId($this->projectId);
        
        $topHeader = new Budget(array(array(HEAD, HEAD, HEAD)),
                                array(array("", (REPORTING_YEAR)." - ".(REPORTING_YEAR+1)." allocation", (REPORTING_YEAR+1)." - ".(REPORTING_YEAR+2)." request")));
        
        $topHeader->xls[0][1]->span = 2;
        $topHeader->xls[0][2]->span = 2;
        
        $pniHeader = new Budget(array(array(HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW)),
                                array(array("PNI Name", "%", "$", "%", "$")));
        $cniHeader = new Budget(array(array(HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW)),
                                array(array("CNI Name", "%", "$", "%", "$")));
        $projHeader = new Budget(array(array(HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW)),
                                 array(array("", "", "", "increase", "")));
        
        $cniBudget = $project->getRequestedBudget(REPORTING_YEAR, CNI);
        $pniBudget = $project->getRequestedBudget(REPORTING_YEAR, PNI);
        
        $pniNames = $this->getNamesBudget($pniBudget, PNI);
        $cniNames = $this->getNamesBudget($cniBudget, CNI);
        
        $pniTotal = $this->getTotalBudget($pniBudget, $pniNames);
        $cniTotal = $this->getTotalBudget($cniBudget, $cniNames);
        
        $pniTotalAllocated = $this->getTotalAllocatedBudget($pniNames);
        $cniTotalAllocated = $this->getTotalAllocatedBudget($cniNames);
        
        $pniSum = $pniTotal->copy()->sum();
        $cniSum = $cniTotal->copy()->sum();
        
        $pniSumAllocated = $pniTotalAllocated->copy()->sum();
        $cniSumAllocated = $cniTotalAllocated->copy()->sum();
        
        $sum = $pniSum->xls[0][0]->value + $cniSum->xls[0][0]->value;
        $sumAllocated = $pniSumAllocated->xls[0][0]->value + $cniSumAllocated->xls[0][0]->value;
        
        $pniFooter = new Budget(array(array(HEAD1, PERC, MONEY, PERC, MONEY)),
                                array(array("PNI Subtotal", $pniSumAllocated->xls[0][0]->value/max(1, $sumAllocated), $pniSumAllocated->xls[0][0]->value, $pniSum->xls[0][0]->value/max(1, $sum), $pniSum->xls[0][0]->value)));
        $cniFooter = new Budget(array(array(HEAD1, PERC, MONEY, PERC, MONEY)),
                                array(array("CNI Subtotal", $cniSumAllocated->xls[0][0]->value/max(1, $sumAllocated), $cniSumAllocated->xls[0][0]->value, $cniSum->xls[0][0]->value/max(1, $sum), $cniSum->xls[0][0]->value)));
        
        $sumAllocated = $project->getAllocatedAmount(REPORTING_YEAR);
        $projectTotal = new Budget(array(array(HEAD1, BLANK, MONEY, PERC, MONEY)),
                                   array(array("Project Total", "", $sumAllocated, ($sum-$sumAllocated)/max(1, $sumAllocated), $sum)));
        
        $pniPerc = $this->getPercBudget($pniTotal, $sum);
        $cniPerc = $this->getPercBudget($cniTotal, $sum);
        
        $pniPercAllocated = $this->getPercBudget($pniTotalAllocated, $sumAllocated);
        $cniPercAllocated = $this->getPercBudget($cniTotalAllocated, $sumAllocated);
        
        $pniJoined = Budget::join_tables(array($pniNames, $pniPercAllocated, $pniTotalAllocated, $pniPerc, $pniTotal));
        $cniJoined = Budget::join_tables(array($cniNames, $cniPercAllocated, $cniTotalAllocated, $cniPerc, $cniTotal));
        
        $pniJoined->where(PERC, array(".+"));
        $cniJoined->where(PERC, array(".+"));
        
        $joined = Budget::union_tables(array($topHeader, $pniHeader, $pniJoined, $pniFooter, $cniHeader, $cniJoined, $cniFooter, $projHeader, $projectTotal));
        foreach($joined->xls[count($joined->xls)-1] as $cell){
            $cell->style .= "font-weight: bold;";
        }
        return $joined;
    }
    
    private function getNamesBudget($budget, $role=CNI){
        $project = Project::newFromId($this->projectId);
        $names = $budget->copy()
                        ->transpose()
                        ->where(V_PERS_NOT_NULL)
                        ->select(V_PERS_NOT_NULL);
        $allPeople = $project->getAllPeopleDuring($role, REPORTING_CYCLE_START, REPORTING_CYCLE_END);
        foreach($allPeople as $person){
            $found = false;
            foreach($names->xls as $rowN => $row){
                $name = $row[0]->value;
                if($person->getNameForForms() == $name){
                    $found = true;
                }
            }
            if(!$found){
                $names = $names->union(new Budget(array(array(V_PERS_NOT_NULL)),
                                                  array(array($person->getName()))));
            }
        }
        return $names;
    }

    private function getTotalBudget($budget, $names){
        $project = Project::newFromId($this->projectId);
        $total = $budget->copy()
                        ->transpose()
                        ->select(CUBE_TOTAL)
                        ->filter(HEAD1)
                        ->filter(CUBE_TOTAL);
        foreach($names->xls as $rowN => $row){
            $name = $row[0]->value;
            $total = $total->union(new Budget(array(array(MONEY)),
                                              array(array(""))));
        }
        return $total;
    }
    
    private function getTotalAllocatedBudget($names){
        $toBeJoined = array();
        $project = Project::newFromId($this->projectId);
        foreach($names->xls as $rowN => $row){
            $name = $row[0]->value;
            $person = Person::newFromNameLike($name);
            $allocated = $person->getAllocatedAmount(REPORTING_YEAR, $project);
            $toBeJoined[] = new Budget(array(array(MONEY)),
                                       array(array($allocated)));
        }
        return Budget::union_tables($toBeJoined);
    }
    
    private function getPercBudget($budget, $sum){
        $copy = $budget->copy();
        foreach($copy->xls as $rowN => $row){
            foreach($row as $colN => $cell){
                $copy->structure[$rowN][$colN] = PERC;
                $copy->xls[$rowN][$colN] = new PercCell("", "", $cell->value/max(1, $sum), "", "", $copy);
            }
        }
        return $copy;
    }

    function render(){
        global $wgOut;
        $budget = $this->getBudget();
        $item = "<small>Because of changes in roles and projects, the sum of the sub totals may not add up to the grand totals.  The grand totals should be treated as actual amounts, while the sub totals should be treated as approximations.</small><br />";
        $item .= $budget->render();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $budget = $this->getBudget();
        $item = "<small>Because of changes in roles and projects, the sum of the sub totals may not add up to the grand totals.  The grand totals should be treated as actual amounts, while the sub totals should be treated as approximations.</small><br />";
        $item .= $budget->renderForPDF();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
