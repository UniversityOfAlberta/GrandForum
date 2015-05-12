<?php

class ProjectCategoryBudgetReportItem extends StaticReportItem {

    private function getBudget(){
        $project = Project::newFromId($this->projectId);
        
        $budget = $project->getRequestedBudget(REPORTING_YEAR);
        $niBudget = $project->getRequestedBudget(REPORTING_YEAR, NI);
        
        $niTotal = $this->getTotalBudget($niBudget);
        
        $categories = $budget->copy()->filter(HEAD1, array("Name of network investigator submitting request:"))->filter(CUBE_TOTAL)->select(HEAD1);
        
        $toBeJoined = array($categories->copy(),
                            $niTotal->copy());
        
        $joined = Budget::join_tables($toBeJoined);
        $joined = $joined->cube();
        
        $joined->xls[0][1]->value = "NIs";
        
        $joined->join($niTotal->copy());
        
        $joined->xls[0][4]->value = "Total";
        $joined->xls[0][5]->value = "NI %";
        
        foreach($joined->xls as $rowN => $row){
            if(!isset($row[5]) || !isset($row[6])){
                $percNI = ($row[1]->value / max(1, $row[4]->value));
                
                $joined->structure[$rowN][5] = PERC;
                $joined->structure[$rowN][6] = PERC;
                $joined->xls[$rowN][5] = new PercCell("", "", $percNI, "", "", $joined);
            }
            else if(is_numeric($row[5]->value) || is_numeric($row[6]->value)){
                $percNI = ($row[1]->value / max(1, $row[4]->value));
                
                $joined->structure[$rowN][5] = PERC;
                $joined->structure[$rowN][6] = PERC;
                $joined->xls[$rowN][5] = new PercCell("", "", $percNI, "", "", $joined);
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
