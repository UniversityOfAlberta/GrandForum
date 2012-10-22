<?php

class PersonHoursCell extends Cell{
    
    function PersonHoursCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $start = "0000";
        $end = "2100";
        if(count($params) == 1){
            $params[2] = $params[0];
        }
        else{
            if(isset($params[0])){
                // Start
                $start = $params[0];
            }
            if(isset($params[1])){
                // End
                $end = $params[1];
            }
        }
        if(isset($params[2])){
            $project = Project::newFromName($params[2]);
            $person = $table->obj;
            $rep_addr = ReportBlob::create_address(RP_RESEARCHER, RES_MILESTONES, RES_MIL_CONTRIBUTIONS, 0);
            $blob = new ReportBlob(BLOB_ARRAY, substr($start, 0, 4), $person->getId(), $project->getId());
            $blob->load($rep_addr);
            $data = $blob->getData();
            $this->value = (isset($data[0]) && $data[0]["time"])? $data[0]["time"] : 0;
        }
        else{
            $person = $table->obj;
            $total = 0;
            $rep_addr = ReportBlob::create_address(RP_RESEARCHER, RES_MILESTONES, RES_MIL_CONTRIBUTIONS, 0);
            foreach($person->getProjectsDuring($start, $end) as $project){
                $blob = new ReportBlob(BLOB_ARRAY, substr($start, 0, 4), $person->getId(), $project->getId());
                $blob->load($rep_addr);
                $data = $blob->getData();
                $total += (isset($data[0]) && $data[0]["time"])? $data[0]["time"] : 0;
            }
            $this->value = $total;
        }
    }
    
    function rasterize(){
        return array(PERSON_HOURS, $this);
    }
    
    function toString(){
        return number_format(doubleval($this->value), 1);
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        $this->style = 'text-align:right;';
        return number_format(doubleval($this->value), 1);
    }
}

?>
