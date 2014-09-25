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
        $allocated = $project->getAllocatedBudget(REPORTING_YEAR-1);
        
        $pniNames = $this->getNamesBudget($pniBudget);
        $cniNames = $this->getNamesBudget($cniBudget);
        
        $pniTotal = $this->getTotalBudget($pniBudget);
        $cniTotal = $this->getTotalBudget($cniBudget);
        
        $pniTotalAllocated = $this->getTotalAllocatedBudget($allocated, $pniNames);
        $cniTotalAllocated = $this->getTotalAllocatedBudget($allocated, $cniNames);
        
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
                                
        $projectTotal = new Budget(array(array(HEAD1, BLANK, MONEY, PERC, MONEY)),
                                   array(array("Project Total", "asdf", $sumAllocated, ($sum-$sumAllocated)/max(1, $sumAllocated), $sum)));
        
        $pniPerc = $this->getPercBudget($pniTotal, $sum);
        $cniPerc = $this->getPercBudget($cniTotal, $sum);
        
        $pniPercAllocated = $this->getPercBudget($pniTotalAllocated, $sumAllocated);
        $cniPercAllocated = $this->getPercBudget($cniTotalAllocated, $sumAllocated);
        
        $pniJoined = Budget::join_tables(array($pniNames, $pniPercAllocated, $pniTotalAllocated, $pniPerc, $pniTotal));
        $cniJoined = Budget::join_tables(array($cniNames, $cniPercAllocated, $cniTotalAllocated, $cniPerc, $cniTotal));
        
        $joined = Budget::union_tables(array($topHeader, $pniHeader, $pniJoined, $pniFooter, $cniHeader, $cniJoined, $cniFooter, $projHeader, $projectTotal));
        return $joined;
    }
    
    private function getNamesBudget($budget){
        return $budget->copy()
                      ->transpose()
                      ->where(V_PERS_NOT_NULL)
                      ->select(V_PERS_NOT_NULL);
    }

    private function getTotalBudget($budget){
        return $budget->copy()
                      ->transpose()
                      ->select(CUBE_TOTAL)
                      ->filter(HEAD1)
                      ->filter(CUBE_TOTAL);
    }
    
    private function getTotalAllocatedBudget($allocated, $names){
        $toBeJoined = array();
        foreach($names->xls as $rowN => $row){
            $name = $row[0]->value;
            $personBudget = $allocated->copy()
                                      ->rasterize()
                                      ->select(V_PERS_NOT_NULL, array($name))
                                      ->where(CUBE_COL_TOTAL);
            if($personBudget->nRows() > 0){
                $toBeJoined[] = $personBudget;
            }
            else{
                $toBeJoined[] = new Budget(array(array(MONEY)),
                                           array(array("")));
            }
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
        $item = $budget->render();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $budget = $this->getBudget();
        $item = $budget->renderForPDF();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
