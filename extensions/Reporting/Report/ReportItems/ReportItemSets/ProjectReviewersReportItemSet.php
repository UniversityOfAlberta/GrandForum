<?php

class ProjectReviewersReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $project = Project::newFromHistoricId($this->projectId);
        if($project == null){
            return $data;
        }
        $type = $this->getAttr('subType', "Project");
        $subs = $project->getEvaluators($this->getReport()->year, $type);
        if(is_array($subs)){
            foreach($subs as $sub){
                $tuple = self::createTuple();
                $tuple['person_id'] = $sub->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
