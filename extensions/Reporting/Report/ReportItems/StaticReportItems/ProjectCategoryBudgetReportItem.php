<?php

class ProjectCategoryBudgetReportItem extends StaticReportItem {

    private function getBudget(){
        $project = Project::newFromId($this->projectId);
        
        $budget = $project->getRequestedBudget(REPORTING_YEAR);
        $cniBudget = $project->getRequestedBudget(REPORTING_YEAR, CNI);
        $pniBudget = $project->getRequestedBudget(REPORTING_YEAR, PNI);
        
        $cniTotal = $this->getTotalBudget($cniBudget);
        $pniTotal = $this->getTotalBudget($pniBudget);
        
        $categories = $budget->copy()->filter(HEAD1, array("Name of network investigator submitting request:"))->filter(CUBE_TOTAL)->select(HEAD1);
        
        $toBeJoined = array($categories->copy(),
                            $pniTotal->copy(),
                            $cniTotal->copy());
        
        $joined = Budget::join_tables($toBeJoined);
        $joined = $joined->cube();
        
        $joined->xls[0][1]->value = "PNIs";
        $joined->xls[0][2]->value = "CNIs";
        
        $joined->join($pniTotal->copy())
               ->join($cniTotal->copy());
        
        $joined->xls[0][3]->value = "Total";
        $joined->xls[0][4]->value = "PNI %";
        $joined->xls[0][5]->value = "CNI %";
        
        foreach($joined->xls as $rowN => $row){
            if(!isset($row[4]) || !isset($row[5])){
                $percPNI = ($row[1]->value / max(1, $row[3]->value));
                $percCNI = ($row[2]->value / max(1, $row[3]->value));
                
                $joined->structure[$rowN][4] = PERC;
                $joined->structure[$rowN][5] = PERC;
                $joined->xls[$rowN][4] = new PercCell("", "", $percPNI, "", "", $joined);
                $joined->xls[$rowN][5] = new PercCell("", "", $percCNI, "", "", $joined);
            }
            else if(is_numeric($row[4]->value) || is_numeric($row[5]->value)){
                $percPNI = ($row[1]->value / max(1, $row[3]->value));
                $percCNI = ($row[2]->value / max(1, $row[3]->value));
                
                $joined->structure[$rowN][4] = PERC;
                $joined->structure[$rowN][5] = PERC;
                $joined->xls[$rowN][4] = new PercCell("", "", $percPNI, "", "", $joined);
                $joined->xls[$rowN][5] = new PercCell("", "", $percCNI, "", "", $joined);
            }
        }
        $joined->xls[1][0]->style = "background:#DDDDDD;";
        $joined->xls[1][1]->style = "background:#DDDDDD;";
        $joined->xls[1][2]->style = "background:#DDDDDD;";
        $joined->xls[1][3]->style = "background:#DDDDDD;";
        $joined->xls[1][4]->style = "background:#DDDDDD;";
        $joined->xls[1][5]->style = "background:#DDDDDD;";
        
        $joined->xls[6][0]->style = "background:#DDDDDD;";
        $joined->xls[6][1]->style = "background:#DDDDDD;";
        $joined->xls[6][2]->style = "background:#DDDDDD;";
        $joined->xls[6][3]->style = "background:#DDDDDD;";
        $joined->xls[6][4]->style = "background:#DDDDDD;";
        $joined->xls[6][5]->style = "background:#DDDDDD;";
        
        $joined->xls[10][0]->style = "background:#DDDDDD;";
        
        $joined->xls[11][0]->style = "background:#DDDDDD;";

        $joined->xls[12][0]->style = "background:#DDDDDD;";
        $joined->xls[12][1]->style = "background:#DDDDDD;";
        $joined->xls[12][2]->style = "background:#DDDDDD;";
        $joined->xls[12][3]->style = "background:#DDDDDD;";
        $joined->xls[12][4]->style = "background:#DDDDDD;";
        $joined->xls[12][5]->style = "background:#DDDDDD;";
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
