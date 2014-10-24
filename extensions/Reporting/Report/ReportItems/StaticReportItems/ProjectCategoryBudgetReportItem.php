<?php

class ProjectCategoryBudgetReportItem extends StaticReportItem {

    private function getBudget(){
        $project = Project::newFromId($this->projectId);
        
        $budget = $project->getRequestedBudget(REPORTING_YEAR);
        $cniBudget = $project->getRequestedBudget(REPORTING_YEAR, CNI);
        $pniBudget = $project->getRequestedBudget(REPORTING_YEAR, PNI);
        
        $cniTotal = $this->getTotalBudget($cniBudget);
        $fcniTotal = $budget->copy()->rasterize()->filter(CUBE_TOTAL)->select(READ, array("Future CNIs"))->filter(BLANK)->limit(1, 16);
        $pniTotal = $this->getTotalBudget($pniBudget);
        
        $categories = $budget->copy()->filter(HEAD1, array("Name of network investigator submitting request:"))->filter(CUBE_TOTAL)->select(HEAD1);
        
        $toBeJoined = array($categories->copy(),
                            $pniTotal->copy(),
                            $cniTotal->copy(),
                            $fcniTotal->copy());
        
        $joined = Budget::join_tables($toBeJoined);
        $joined = $joined->cube();
        
        $joined->xls[0][1]->value = "PNIs";
        $joined->xls[0][2]->value = "CNIs";
        $joined->xls[0][3] = new HeadCell(HEAD, "", "Future CNIs", "", "", "");
        
        $joined->join($pniTotal->copy())
               ->join($cniTotal->copy());
        
        $joined->xls[0][4]->value = "Total";
        $joined->xls[0][5]->value = "PNI %";
        $joined->xls[0][6]->value = "CNI %";
        
        foreach($joined->xls as $rowN => $row){
            if(!isset($row[5]) || !isset($row[6])){
                $percPNI = ($row[1]->value / max(1, $row[4]->value));
                $percCNI = (($row[2]->value + $row[3]->value) / max(1, $row[4]->value));
                
                $joined->structure[$rowN][5] = PERC;
                $joined->structure[$rowN][6] = PERC;
                $joined->xls[$rowN][5] = new PercCell("", "", $percPNI, "", "", $joined);
                $joined->xls[$rowN][6] = new PercCell("", "", $percCNI, "", "", $joined);
            }
            else if(is_numeric($row[5]->value) || is_numeric($row[6]->value)){
                $percPNI = ($row[1]->value / max(1, $row[4]->value));
                $percCNI = (($row[2]->value + $row[3]->value) / max(1, $row[4]->value));
                
                $joined->structure[$rowN][5] = PERC;
                $joined->structure[$rowN][6] = PERC;
                $joined->xls[$rowN][5] = new PercCell("", "", $percPNI, "", "", $joined);
                $joined->xls[$rowN][6] = new PercCell("", "", $percCNI, "", "", $joined);
            }
        }
        foreach($joined->xls[1] as $cell){
            $cell->style = "background:#DDDDDD;";
        }
        foreach($joined->xls[6] as $cell){
            $cell->style = "background:#DDDDDD;";
        }
        foreach($joined->xls[10] as $cell){
            $cell->style = "background:#DDDDDD;";
        }
        foreach($joined->xls[11] as $cell){
            $cell->style = "background:#DDDDDD;";
        }
        foreach($joined->xls[12] as $cell){
            $cell->style = "background:#DDDDDD;";
        }
        foreach($joined->xls[16] as $cell){
            $cell->style = "font-weight: bold;";
        }
        return $joined;
    }

    private function getTotalBudget($budget){
        return $budget->copy()
                      ->rasterize()
                      ->filter(HEAD1, array("Name of network investigator submitting request:"))
                      ->filter(CUBE_TOTAL)
                      ->select(HEAD, array("TOTAL"));
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
