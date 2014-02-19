<?php

class ProjectPeopleWithBudgetsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $proj = Project::newFromId($this->projectId);
        $proj_id = $this->projectId;
        if($proj != null){
            $members = array_merge($proj->getAllPeopleDuring(PNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                   $proj->getAllPeopleDuring(CNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL));
            $alreadySeen = array();
            foreach($members as $m){
                if(isset($alreadySeen[$m->getId()])){
                    continue;
                }
                $budget = $m->getRequestedBudget(REPORTING_YEAR);
                if($budget != null){
                    $budgetProject = $budget->copy()->select(V_PROJ, array($proj->getName()));
                    if($budgetProject->nCols()*$budgetProject->nRows() > 0){
                        $alreadySeen[$m->getId()] = true;
                        $tuple = self::createTuple();
                        $tuple['person_id'] = $m->getId();
                        $data[] = $tuple;
                    }
                }
            }
        }
        return $data;
    }
}

?>
