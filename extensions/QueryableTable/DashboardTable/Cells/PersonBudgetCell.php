<?php

    class PersonBudgetCell extends Cell {
        
        var $obj;
        
        function PersonBudgetCell($cellType, $params, $cellValue, $rowN, $colN, $table){
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
                $project = Project::newFromName($params[2]);
                if($project != null && $project->getName() != null){
                    $this->obj = $project;
                    $person = $table->obj;
                    $budget = $person->getRequestedBudget($start);
                    $value = 0;
                    if($budget != null){
                        $value = $budget->copy()->select(V_PROJ, array($project->getName()))->rasterize()->where(COL_TOTAL)->toString();
                        if($value == ""){
                            $value = 0;
                        }
                    }
                    $this->setValue($value);
                }
            }
            else{
                $person = $table->obj;
                $budget = $person->getRequestedBudget($start);
                $value = 0;
                if($budget != null){
                    $value = $budget->copy()->rasterize()->where(HEAD1, array("TOTALS.*"))->select(ROW_TOTAL)->toString();
                    if($value == ""){
                        $value = 0;
                    }
                }
                $this->setValue($value);
            }
        }
        
        function toString(){
            return "$".$this->value;
        }
        
        function rasterize(){
            return array(PERSON_BUDGET, $this);
        }
        
        function render(){
            global $wgServer, $wgScriptPath;
            $this->style = 'text-align:right;';
            $table = "<table width='100%'>";
            $table .= "<tr><td style='text-align:right;'>{$this->value}</td></tr>";
            $table .= "</table>";
            return "$table";
        }
        
    }
    
?>
