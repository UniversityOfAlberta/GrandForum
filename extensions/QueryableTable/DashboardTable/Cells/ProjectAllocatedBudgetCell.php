<?php

    class ProjectAllocatedBudgetCell extends ProjectBudgetCell {
        
        var $obj;
        
        function ProjectAllocatedBudgetCell($cellType, $params, $cellValue, $rowN, $colN, $table){
            $start = "0000";
            $end = "2100";
            if(count($params) == 1){
                $params[2] = $params[0];
            }
            else{
                if(isset($params[0])){
                    // Start
                    $start = substr($params[0], 0, 4);
                }
                if(isset($params[1])){
                    // End
                    $end = substr($params[1], 0, 4);
                }
            }
            if(isset($params[2])){
                $person = Person::newFromId($params[2]);
                if($person != null && $person->getName() != null){
                    $this->obj = $person;
                    $project = $table->obj;
                    $budget = $project->getAllocatedBudget($start-1);
                    $value = 0;
                    if($budget != null){
                        $value = $budget->copy()->select(V_PERS_NOT_NULL, array($person->getName()))->rasterize()->where(CUBE_COL_TOTAL)->toString();
                        if($value == ""){
                            $value = 0;
                        }
                    }
                    $this->setValue($value);
                }
            }
            else{
                $person = $table->obj;
                $budget = $person->getAllocatedBudget($start-1);
                $value = 0;
                if($budget != null){
                    $value = $budget->copy()->rasterize()->where(CUBE_TOTAL)->select(CUBE_TOTAL)->toString();
                    if($value == ""){
                        $value = 0;
                    }
                }
                $this->setValue($value);
            }
        }
        
        function rasterize(){
            return array(PROJECT_ALLOCATED_BUDGET, $this);
        }
        
    }
    
?>
