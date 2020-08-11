<?php

class MilestoneReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $proj = Project::newFromHistoricId($this->projectId);
        if($proj != null){
            $proj_id = $proj->getId();
            $milestones = $proj->getMilestonesDuring();
            foreach($milestones as $ms){
                $tuple = self::createTuple();
                $tuple['milestone_id'] = $ms->getMilestoneId();
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
